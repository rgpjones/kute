<?php

declare(strict_types=1);

namespace Kachuru\RedBlue;

class Player
{
    private array $selectorPools = [];

    public function select(): Selection
    {
        return mt_rand(0, 1) === 0
            ? Selection::BLUE
            : Selection::RED;
    }
}