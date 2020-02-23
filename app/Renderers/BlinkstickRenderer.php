<?php

namespace App\Renderers;

use App\Game;

class BlinkstickRenderer implements Renderer
{
    private $itemPositions = [];

    public function __construct()
    {
        $this->resetLeds();
    }

    public function render($state)
    {
        //$this->renderWithPulses($state);
        //$this->renderWithSetLed($state);
        $this->renderWithSetAll($state);
    }

    private function renderWithPulses($state)
    {
        foreach ($state['items'] as $item) {
            $position = (int) floor($item->position);
            if (array_key_exists($item->id, $this->itemPositions) && $this->itemPositions[$item->id] === $position) {
                continue;
            }

            $this->pulseLed($position, 200, $item->color);
            $this->itemPositions[$item->id] = $position;
        }
    }

    private function renderWithSetLed($state)
    {
        foreach ($state['items'] as $item) {
            $position = (int) floor($item->position);
            if (array_key_exists($item->id, $this->itemPositions) && $this->itemPositions[$item->id] === $position) {
                continue;
            }

            $this->setLed($position, $item->color);
            if (array_key_exists($item->id, $this->itemPositions)) {
                $this->setLed($this->itemPositions[$item->id], 'black');
            }

            $this->itemPositions[$item->id] = $position;
        }
    }

    private function renderWithSetAll($state)
    {
        $data = [];
        for ($i =0; $i < Game::BOARD_LENGTH; $i++) {
            $color = [0, 0, 0];
            foreach ($state['items'] as $item) {
                if ((int) floor($item->position) === $i) {
                    $color = $item->colorRGB;
                }
            }
            $data = array_merge($data, $color);
        }

        $this->setAllLeds($data);
    }

    /**
     * Uses a nodejs script to interact with the blinkstick
     * 
     * @param int $position
     * @param int $duration
     * @param string $color
     * @return void
     */
    private function pulseLed(int $position, int $duration, string $color)
    {
        //$command = resource_path('js/pulseLed.js') . " --index=$position --duration=$duration --color=$color";
        $command = "blinkstick --pulse --index=$position --duration=$duration '$color'";
        $logFile = storage_path('logs/blink.txt');
        exec($command . " > $logFile &");
        echo $command . "\n";
    }

    private function setLed(int $position, string $color)
    {
        $command = resource_path('js/setLed.js') . " --index=$position --color=$color";
        $logFile = storage_path('logs/blink.txt');
        echo $command . "\n";
        exec($command . " > $logFile &");
    }

    private function setAllLeds(array $data)
    {
        $command = resource_path("js/setAllLeds.js --data='" . json_encode($data) . "'");
        $logFile = storage_path('logs/blink.txt');
        echo $command . "\n";
        exec($command . " > $logFile &");
    }

    /**
     * Resets the leds back to black
     */
    private function resetLeds()
    {
        $data = array_fill(0, Game::BOARD_LENGTH * 3, 0);
        $this->setAllLeds($data);
    }
}
