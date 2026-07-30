<?php

declare(strict_types=1);

namespace Kachuru\RedBlue;

class Game
{
    private array $player = [];

    public function setup(): void
    {
        for ($playernum = 1; $playernum <= 4; $playernum++) {
            $this->player[$playernum] = $this->setupPlayer();
        }
    }

    public function play(): array
    {
        $roundResults = [];

        $selections = [];
        for ($round = 1; $round <= 10; $round++) {
            foreach ($this->player as $playernum => $player) {
                $selections[$playernum] = $player->select();
            }

            $roundResults[$round] = $selections;
        }

        return $roundResults;
    }

    private function setupPlayer(): Player
    {
        return new Player();
    }
}