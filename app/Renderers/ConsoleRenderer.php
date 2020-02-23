<?php

namespace App\Renderers;

use App\Game;

class ConsoleRenderer implements Renderer
{
    public function __construct()
    {

    }

    public function render(array $state)
    {
        $frame = [];
        $collisionPositions = collect($state['collisions'])->pluck('position');
        for ($i = 0; $i < Game::BOARD_LENGTH; $i++) {
            $character = '.';
            foreach ($state['items'] as $item) {
                if ($item->position >= $i && $item->position < $i + 1) {
                    $character = $item->wave->getSymbol();
                }
            }

            if ($collisionPositions->search($i) !== false) {
                $character = 'x';
            }
            $frame[] = $character;
        }
        $message = implode("", $frame) . count($state['items']) . "\n";
        echo "\x1B[1A\x1B[2K$message";
    }
}
