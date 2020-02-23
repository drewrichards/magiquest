<?php

namespace App;

class GameItem
{
    CONST DIRECTION_FORWARD = 1;

    const DIRECTION_BACKWARD = -1;

    const AVERAGE_ITEM_LIFE_SECONDS = 3;

    public $id;

    public $position;

    public $direction;

    public $wave;

    public $lostCollision = false;

    public $color = '';

    public $colorRGB = [];

    private $velocity;

    public function __construct(Wave $wave)
    {
        $this->id = uniqid();
        $this->direction = $wave->wandId === WAVE::ANNS_WAND 
        ? self::DIRECTION_BACKWARD
        : self::DIRECTION_FORWARD;
        $this->position = $this->direction === self::DIRECTION_FORWARD ? 0 : GAME::BOARD_LENGTH;
        $this->wave = $wave;
        $this->velocity = $this->calculateVelocity();
        $this->initColor();
    }

    /**
     * Gets the wand wave's colors
     * 
     * @return string
     */
    public function initColor()
    {
        $colors = ['#540D6E', '#EE4266', '#FFD23F', '#3BCEAC', '#0EAD69'];
        $this->color = $colors[array_rand($colors)];
        $this->colorRGB = [
            hexdec(substr($this->color, 1, 2)),
            hexdec(substr($this->color, 3, 2)),
            hexdec(substr($this->color, 5, 2))
        ];
    }

    /**
     * Updates the item's position based on the time
     * of the last cycle.
     * 
     * @param float $seconds
     */
    public function processCycle()
    {
        $this->position = $this->getNextPosition();
    }

    /**
     * Determines if the item is still on the board
     * 
     * @return boolean
     */
    public function isInBounds()
    {
        return $this->position >= 0
            && $this->position < Game::BOARD_LENGTH;
    }

    /**
     * Gets the amount of space the item moves per cycle.
     * 
     * @return float
     */
    public function calculateVelocity()
    {
        $averageVelocity = (Game::BOARD_LENGTH * Game::LOOP_LENGTH_SECONDS) / self::AVERAGE_ITEM_LIFE_SECONDS;
        return $averageVelocity;
    }

    /**
     * Determines if this item will lose a collision 
     * with the other item on the next cycle.
     * 
     * @return boolean
     */
    public function isCollisionLoser(GameItem $item)
    {
        if (!$this->willCollide($item)) {
            return false;
        }
        
        $midpoint = Game::BOARD_LENGTH / 2;
        return ($this->direction === self::DIRECTION_FORWARD && $this->position <= $midpoint)
            || ($this->direction === self::DIRECTION_BACKWARD && $this->position > $midpoint);
    }

    /**
     * Checks if this item will collide with the other 
     * item on the next cycle.
     * 
     * @return boolean
     */
    public function willCollide(GameItem $item)
    {
        if ($this->direction === $item->direction) {
            return false;
        }

        $thisNextPosition = $this->getNextPosition();
        $otherNextPostion = $item->getNextPosition();

        if ($this->direction === self::DIRECTION_FORWARD) {
            if ($this->position < $item->position && $thisNextPosition >= $otherNextPostion) {
                return true;
            }
        } elseif ($this->position > $item->position && $thisNextPosition <= $otherNextPostion) {
            return true;
        }

        return false;
    }

    /**
     * Gets the position the item will be in after the next cycle.
     * 
     * @return float
     */
    private function getNextPosition()
    {
        return $this->position + ($this->velocity * $this->direction);
    }
}
