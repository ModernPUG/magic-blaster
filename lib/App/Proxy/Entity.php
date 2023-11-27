<?php

declare(strict_types=1);

namespace App\Proxy;

abstract class Entity implements \App\BaseEntity
{
    public readonly int $half_width;
    public readonly int $half_height;

    public function __construct(
        public readonly int $id,
        public readonly int $width,
        public readonly int $height,
        public readonly int $x,
        public readonly int $y,
    ) {
        $this->half_width = (int)($this->width / 2);
        $this->half_height = (int)($this->height / 2);
    }

    final public function getId(): int
    {
        return $this->id;
    }

    final public function getRectangle(): \App\Rectangle
    {
        return new \App\Rectangle(
            $this->width,
            $this->height,
            $this->x,
            $this->y,
        );
    }
}
