<?php

declare(strict_types=1);

namespace App;

/**
 * 게임에 참가하는 유저는 이 추상 클래스를 상속받아야 합니다.
 * 이 클래스가 제공하는 protected 메서드를 활용하세요.
 */
abstract class User
{
    /**
     * 기준 엔터티으로부터 대상 엔터티이 얼마나 떨어져 있는지 계산합니다.
     * horizontal이 음수이면 대상 엔터티는 기준 엔터티의 왼쪽에 있고,
     * vertical이 음수이면 대상 엔터티는 기준 엔터티의 위쪽에 있습니다.
     *
     * @param \App\Proxy\Entity $source_entity 기준 엔터티
     * @param \App\Proxy\Entity $target_entity 대상 엔터티
     * @return array{vertical:int,horizontal:int}
     */
    final protected static function calculateEntityDistance(
        \App\Proxy\Entity $source_entity,
        \App\Proxy\Entity $target_entity,
    ): array {
        return \App\Rectangle::calculateDistance(
            $source_entity->getRectangle(),
            $target_entity->getRectangle(),
        );
    }

    /**
     * 두 엔터티 사이의 맨해튼 거리를 계산합니다.
     *
     * @param \App\Proxy\Entity $entity1
     * @param \App\Proxy\Entity $entity2
     * @return int
     */
    final protected static function calculateEntityManhattanDistance(
        \App\Proxy\Entity $entity1,
        \App\Proxy\Entity $entity2,
    ): int {
        return (int)(abs($entity1->x - $entity2->x) + abs($entity1->y - $entity2->y));
    }

    /**
     * 기준 엔터티로부터 가까운 순서대로 엔터티 목록을 정렬합니다.
     *
     * @param \App\Proxy\Entity $source_entity 기준 엔터티
     * @param \App\Proxy\Entity[] $entity_list 정렬할 엔터티 목록
     * @return \App\Proxy\Entity[]
     */
    final protected static function sortEntityListByDistance(
        \App\Proxy\Entity $source_entity,
        array $entity_list,
    ): array {
        usort($entity_list, function ($entity_a, $entity_b) use ($source_entity) {
            $distance_a = self::calculateEntityManhattanDistance($source_entity, $entity_a);
            $distance_b = self::calculateEntityManhattanDistance($source_entity, $entity_b);

            return $distance_a - $distance_b;
        });

        return $entity_list;
    }

    /**
     * 기준 엔터티로부터 가장 가까운 엔터티를 찾습니다.
     *
     * @param \App\Proxy\Entity $source_entity 기준 엔터티
     * @param \App\Proxy\Entity[] $entity_list 찾을 엔터티 목록
     * @return \App\Proxy\Entity|null
     */
    final protected static function findNearestEntity(
        \App\Proxy\Entity $source_entity,
        array $entity_list,
    ): ?\App\Proxy\Entity {
        $sorted_entity_list = self::sortEntityListByDistance($source_entity, $entity_list);
        return $sorted_entity_list[0] ?? null;
    }

    /**
     * 생성자
     *
     * @param \App\Map $map
     */
    final public function __construct(
        private readonly Map $map,
    ) {
    }

    /**
     * 플레이어가 왼쪽으로 이동할 수 있는지 여부를 반환합니다.
     *
     * @param \App\Proxy\Player $player
     * @return bool
     */
    final protected function canPlayerMoveLeft(\App\Proxy\Player $player): bool
    {
        $player = $player->withLeft();
        return $this->map->canPlaceEntity($player);
    }

    /**
     * 플레이어가 오른쪽으로 이동할 수 있는지 여부를 반환합니다.
     *
     * @param \App\Proxy\Player $player
     * @return bool
     */
    final protected function canPlayerMoveRight(\App\Proxy\Player $player): bool
    {
        $player = $player->withRight();
        return $this->map->canPlaceEntity($player);
    }

    /**
     * 플레이어가 위쪽으로 이동할 수 있는지 여부를 반환합니다.
     *
     * @param \App\Proxy\Player $player
     * @return bool
     */
    final protected function canPlayerMoveUp(\App\Proxy\Player $player): bool
    {
        $player = $player->withUp();
        return $this->map->canPlaceEntity($player);
    }

    /**
     * 플레이어가 아래쪽으로 이동할 수 있는지 여부를 반환합니다.
     *
     * @param \App\Proxy\Player $player
     * @return bool
     */
    final protected function canPlayerMoveDown(\App\Proxy\Player $player): bool
    {
        $player = $player->withDown();
        return $this->map->canPlaceEntity($player);
    }

    /**
     * 유저의 이름을 반환합니다.
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * 플레이어의 행동을 결정합니다.
     *
     * @param \App\Proxy\Map $map 지도
     * @param \App\Proxy\Player $own_player 플레이어 본인
     * @param \App\Proxy\Player[] $other_player_list 다른 플레이어 목록
     * @param \App\Proxy\Magic[] $magic_list 마법 목록
     * @return \App\UserAction
     */
    abstract public function action(
        \App\Proxy\Map $map,
        \App\Proxy\Player $own_player,
        array $other_player_list,
        array $magic_list,
    ): UserAction;
}
