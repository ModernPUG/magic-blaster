<?php

declare(strict_types=1);

namespace App;

enum PlayerDirection: string
{
    case LEFT = 'left';
    case RIGHT = 'right';
    case UP = 'up';
    case DOWN = 'down';
}
