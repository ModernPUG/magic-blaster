<?php

declare(strict_types=1);

namespace App;

class Rectangle
{
    /**
     * 두 사각형의 충돌 여부
     *
     * @param \App\Rectangle $rectangle1
     * @param \App\Rectangle $rectangle2
     * @return bool
     */
    public static function areColliding(self $rectangle1, self $rectangle2): bool
    {
        $coordinates1 = $rectangle1->getCoordinates();
        $coordinates2 = $rectangle2->getCoordinates();

        return (
            $coordinates1['x1'] < $coordinates2['x2'] &&
            $coordinates1['x2'] > $coordinates2['x1'] &&
            $coordinates1['y1'] < $coordinates2['y2'] &&
            $coordinates1['y2'] > $coordinates2['y1']
        );
    }

    /**
     * 기준 사각형으로부터 대상 사각형이 얼마나 떨어져 있는지 계산합니다.
     * horizontal이 음수이면 대상 사각형은 기준 사각형의 왼쪽에 있고,
     * vertical이 음수이면 대상 사각형은 기준 사각형의 위쪽에 있습니다.
     *
     * @param \App\Rectangle $source_rectangle 기준 사각형
     * @param \App\Rectangle $target_rectangle 대상 사각형
     * @return array{vertical:int,horizontal:int}
     */
    public static function calculateDistance(
        self $source_rectangle,
        self $target_rectangle,
    ): array {
        $horizontal_distance = $target_rectangle->x - $source_rectangle->x;
        $vertical_distance = $target_rectangle->y - $source_rectangle->y;

        return [
            'horizontal' => $horizontal_distance,
            'vertical' => $vertical_distance,
        ];
    }

    /**
     * 두 사각형 사이의 맨해튼 거리를 계산합니다.
     *
     * @param \App\Rectangle $rectangle1
     * @param \App\Rectangle $rectangle2
     * @return int
     */
    public static function calculateManhattanDistance(
        self $rectangle1,
        self $rectangle2,
    ): int {
        return (int)(abs($rectangle1->x - $rectangle2->x) + abs($rectangle1->y - $rectangle2->y));
    }

    /**
     * 기준 사각형으로부터 가까운 순서대로 사각형 목록을 정렬합니다.
     *
     * @param \App\Rectangle $source_rectangle
     * @param \App\Rectangle[] $rectangle_list
     * @return \App\Rectangle[]
     */
    public static function sortListByDistance(
        self $source_rectangle,
        array $rectangle_list,
    ): array {
        usort($rectangle_list, function ($rectangle_a, $rectangle_b) use ($source_rectangle) {
            $distance_a = self::calculateManhattanDistance($source_rectangle, $rectangle_a);
            $distance_b = self::calculateManhattanDistance($source_rectangle, $rectangle_b);

            return $distance_a - $distance_b;
        });

        return $rectangle_list;
    }

    /**
     * 기준 사각형으로부터 가장 가까운 사각형을 찾습니다.
     *
     * @param \App\Rectangle $source_rectangle
     * @param \App\Rectangle[] $rectangle_list
     * @return \App\Rectangle|null
     */
    public static function findNearest(
        self $source_rectangle,
        array $rectangle_list,
    ): ?self {
        $sorted_rectangle_list = self::sortListByDistance($source_rectangle, $rectangle_list);
        return $sorted_rectangle_list[0] ?? null;
    }

    /**
     * 기준 사각형과 충돌여부에 따른 사각형 목록을 필터링합니다.
     *
     * @param \App\Rectangle $source_rectangle
     * @param \App\Rectangle[] $rectangle_list
     * @param bool $include_collision 충돌을 포함할지 여부
     * @return \App\Rectangle[]
     */
    public static function filterByCollision(
        self $source_rectangle,
        array $rectangle_list,
        bool $include_collision,
    ): array {
        $filtered_list = [];
        foreach ($rectangle_list as $rectangle) {
            if (self::areColliding($source_rectangle, $rectangle) === $include_collision) {
                $filtered_list[] = $rectangle;
            }
        }
        return $filtered_list;
    }

    public readonly int $half_width;
    public readonly int $half_height;

    private int $x;
    private int $y;
    private int $x1;
    private int $y1;
    private int $x2;
    private int $y2;

    public function __construct(
        public readonly int $width,
        public readonly int $height,
        int $x,
        int $y,
    ) {
        $this->half_width = (int)($this->width / 2);
        $this->half_height = (int)($this->height / 2);
        $this->setPosition($x, $y);
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function setX(int $x): void
    {
        $this->x = $x;
        $half_width = (int)($this->width / 2);
        $this->x1 = $x - $half_width;
        $this->x2 = $x + $half_width;
    }

    public function setY(int $y): void
    {
        $this->y = $y;
        $half_height = (int)($this->height / 2);
        $this->y1 = $y - $half_height;
        $this->y2 = $y + $half_height;
    }

    public function setPosition(int $x, int $y): void
    {
        $this->setX($x);
        $this->setY($y);
    }

    /**
     * @return array{x1:int,y1:int,x2:int,y2:int}
     */
    public function getCoordinates(): array
    {
        return [
            'x1' => $this->x1,
            'y1' => $this->y1,
            'x2' => $this->x2,
            'y2' => $this->y2,
        ];
    }
}
