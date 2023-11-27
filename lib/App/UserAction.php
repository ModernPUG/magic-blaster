<?php

declare(strict_types=1);

namespace App;

enum UserAction: string
{
    case STOP = 'stop';
    case LEFT = 'left';
    case RIGHT = 'right';
    case UP = 'up';
    case DOWN = 'down';
    case MAGIC = 'magic';

    public function isMove(): bool
    {
        return in_array($this, [
            self::LEFT,
            self::RIGHT,
            self::UP,
            self::DOWN
        ], true);
    }
}
