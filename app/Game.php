<?php

namespace App;

use App\Renderers\Renderer;

class Game
{
    const LOOP_LENGTH_SECONDS = 0.0625;

    const INPUT_BUFFER_SECONDS = 0.5;

    const BOARD_LENGTH = 32;

    const COLLISION_LENGTH_SECONDS = 2;

    private $irProcessor;

    private $waveBuffer = [];

    private $state = [
        'newWaves' => [],
        'items' => [],
        'collisions' => [],
    ];

    private $renderers = [];

    public function __construct(array $renderers)
    {
        $this->irProcessor = new IrProcessor();
        $this->renderers = $renderers;
    }

    public function loop()
    {
        while (true) {
            $startTime = microtime(true);

            $this->processInput();
            $this->updateState();
            $this->render();

            $this->wait(microtime(true) - $startTime);
        }
    }

    /**
     * Reads ir pulses and adds wand waves to the game state.
     */
    private function processInput()
    {
        $waves = $this->irProcessor->processPulses();
        if (count($waves) > 0) {
            $this->waveBuffer = array_merge($this->waveBuffer, $waves);
        }

        if ($this->shouldProcessWaveBuffer()) {
            $this->state['newWaves'] = Wave::findSignificantWaves($this->waveBuffer);
            $this->waveBuffer = [];
        }
    }

    /**
     * Updates the games state
     */
    private function updateState()
    {
        $newItems = [];
        $totalItems = count($this->state['items']);

        // Remove expired collision
        $this->state['collisions'] = array_filter($this->state['collisions'], function ($collision) {
            return (microtime(true) - $collision['time']) < self::COLLISION_LENGTH_SECONDS;
        });
        
        // Check for new collisions
        for ($i = 0; $i < $totalItems; $i++) {
            for ($j = $i + 1; $j < $totalItems; $j++) {
                if ($this->state['items'][$i]->willCollide($this->state['items'][$j])) {
                    $loser = $this->state['items'][$i]->isCollisionLoser($this->state['items'][$j]) ? $i : $j;
                    $this->state['items'][$loser]->lostCollision = true;
                    $this->state['collisions'][] = [
                        'position' => round($this->state['items'][$loser]->position),
                        'time' => microtime(true),
                    ];
                }
            }
        }

        // Update the position of existing items
        foreach ($this->state['items'] as $item) {
            $item->processCycle();
            if ($item->isInBounds() && !$item->lostCollision) {
                $newItems[] = $item;
            }
        }

        // Add the new items
        foreach ($this->state['newWaves'] as $wave) {
            $newItems[] = new GameItem($wave);
        }

        $this->state['newWaves'] = [];
        $this->state['items'] = $newItems;
    }

    /**
     * Renders the game state
     */
    private function render()
    {
        foreach ($this->renderers as $renderer) {
            $renderer->render($this->state);
        }
    }

    /**
     * Determines if the is ready to be processed
     * 
     * @return boolean
     */
    private function shouldProcessWaveBuffer()
    {
        return !empty($this->waveBuffer[0])
            && (microtime(true) - $this->waveBuffer[0]->createdMicrotime) > self::INPUT_BUFFER_SECONDS;
    }

    /**
     * Sleep until the next cycle starts
     */
    private function wait($loopTime)
    {
        $sleepTime = self::LOOP_LENGTH_SECONDS > $loopTime 
            ? self::LOOP_LENGTH_SECONDS - $loopTime
            : 0;
        //echo "sleeping $sleepTime\n";
        usleep($sleepTime * 1000000);
    }
}
