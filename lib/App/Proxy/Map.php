<?php

declare(strict_types=1);

namespace App\Proxy;

/**
 * 지도 정보
 */
class Map
{
    /** @var int 지도의 중앙 x 좌표 */
    public readonly int $center_x;

    /** @var int 지도의 중앙 y 좌표 */
    public readonly int $center_y;

    /**
     * @param int $width 지도의 너비
     * @param int $height 지도의 높이
     * @param int $player_min_x 플레이어가 지도의 왼쪽 끝에서 시작할 수 있는 x 좌표의 최솟값
     * @param int $player_max_x 플레이어가 지도의 오른쪽 끝에서 시작할 수 있는 x 좌표의 최댓값
     * @param int $player_min_y 플레이어가 지도의 위쪽 끝에서 시작할 수 있는 y 좌표의 최솟값
     * @param int $player_max_y 플레이어가 지도의 아래쪽 끝에서 시작할 수 있는 y 좌표의 최댓값
     */
    public function __construct(
        public readonly int $width,
        public readonly int $height,
        public readonly int $player_min_x,
        public readonly int $player_max_x,
        public readonly int $player_min_y,
        public readonly int $player_max_y,
    ) {
        $this->center_x = (int)($width / 2);
        $this->center_y = (int)($height / 2);
    }

    /**
     * 엔터티가 지도의 중앙에서 얼마나 떨어져 있는지 계산합니다.
     * horizontal이 음수이면 왼쪽에 있고, vertical이 음수이면 위쪽에 있습니다.
     *
     * @param \App\Proxy\Entity $entity
     * @return array{horizontal:int,vertical:int}
     */
    public function calculateEntityDistanceFromCenter(Entity $entity): array
    {
        $horizontal_distance = $entity->x - $this->center_x;
        $vertical_distance = $entity->y - $this->center_y;

        return [
            'horizontal' => $horizontal_distance,
            'vertical' => $vertical_distance,
        ];
    }
}
