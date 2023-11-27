<?php

declare(strict_types=1);

namespace App\Proxy;

class Magic extends Entity
{
    /**
     * @param int $id 마법 ID
     * @param int $width 마법의 너비
     * @param int $height 마법의 높이
     * @param int $x 마법의 x 좌표
     * @param int $y 마법의 y 좌표
     * @param int $explosion_count 폭발한 횟수
     * @param \App\Rectangle $left_damage_rectangle 왼쪽에 있는 플레이어에게 줄 피해 범위
     * @param \App\Rectangle $right_damage_rectangle 오른쪽에 있는 플레이어에게 줄 피해 범위
     * @param \App\Rectangle $top_damage_rectangle 위쪽에 있는 플레이어에게 줄 피해 범위
     * @param \App\Rectangle $bottom_damage_rectangle 아래쪽에 있는 플레이어에게 줄 피해 범위
     */
    public function __construct(
        int $id,
        int $width,
        int $height,
        int $x,
        int $y,
        public readonly int $explosion_count,
        public readonly \App\Rectangle $left_damage_rectangle,
        public readonly \App\Rectangle $right_damage_rectangle,
        public readonly \App\Rectangle $top_damage_rectangle,
        public readonly \App\Rectangle $bottom_damage_rectangle,
    ) {
        parent::__construct($id, $width, $height, $x, $y);
    }

    /**
     * 모든 피해 범위를 반환합니다.
     *
     * @return array<string,\App\Rectangle>
     */
    public function getAllDamageRectangleList(): array
    {
        return [
            'center' => $this->getRectangle(),
            'left' => $this->left_damage_rectangle,
            'right' => $this->right_damage_rectangle,
            'top' => $this->top_damage_rectangle,
            'bottom' => $this->bottom_damage_rectangle,
        ];
    }
}
