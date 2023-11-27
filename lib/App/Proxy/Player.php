<?php

declare(strict_types=1);

namespace App\Proxy;

/**
 * 플레이어 정보
 */
class Player extends Entity
{
    /**
     * @param int $id 플레이어의 ID
     * @param int $width 플레이어의 너비
     * @param int $height 플레이어의 높이
     * @param int $x 플레이어의 x 좌표
     * @param int $y 플레이어의 y 좌표
     * @param int $damage 플레이어가 받은 피해
     * @param bool $can_use_magic 플레이어가 마법을 사용할 수 있는지 여부
     */
    public function __construct(
        int $id,
        int $width,
        int $height,
        int $x,
        int $y,
        public readonly int $damage,
        public readonly bool $can_use_magic,
    ) {
        parent::__construct($id, $width, $height, $x, $y);
    }

    public function withPos(int $x, int $y): self
    {
        return new self(
            $this->id,
            $this->width,
            $this->height,
            $x,
            $y,
            $this->damage,
            $this->can_use_magic,
        );
    }

    public function withX(int $x): self
    {
        return $this->withPos($x, $this->y);
    }

    public function withY(int $y): self
    {
        return $this->withPos($this->x, $y);
    }

    public function withLeft(): self
    {
        return $this->withX($this->x - 1);
    }

    public function withRight(): self
    {
        return $this->withX($this->x + 1);
    }

    public function withUp(): self
    {
        return $this->withY($this->y - 1);
    }

    public function withDown(): self
    {
        return $this->withY($this->y + 1);
    }
}
