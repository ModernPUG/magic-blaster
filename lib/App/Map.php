<?php

declare(strict_types=1);

namespace App;

class Map
{
    public readonly \App\Proxy\Map $proxy_map;

    /**
     * @var array<
     *     class-string<\App\Entity>,
     *     \SplObjectStorage<\App\Entity,int>
     * >
     * */
    private array $entity_list_by_class = [];

    public function __construct(
        public readonly int $width,
        public readonly int $height,
    ) {
        $this->proxy_map = new \App\Proxy\Map(
            width: $this->width,
            height: $this->height,
            player_min_x: (int)(Player::WIDTH / 2),
            player_max_x: $this->width - (int)(Player::WIDTH / 2),
            player_min_y: (int)(Player::HEIGHT / 2),
            player_max_y: $this->height - (int)(Player::HEIGHT / 2),
        );
    }

    public function canPlaceEntity(BaseEntity $entity): bool
    {
        $target_rectangle = $entity->getRectangle();

        $min_map_x = $target_rectangle->half_width;
        $max_map_x = $this->width - $target_rectangle->half_width;
        $min_map_y = $target_rectangle->half_height;
        $max_map_y = $this->height - $target_rectangle->half_height;

        if (
            $target_rectangle->getX() < $min_map_x
            || $target_rectangle->getX() > $max_map_x
            || $target_rectangle->getY() < $min_map_y
            || $target_rectangle->getY() > $max_map_y
        ) {
            return false;
        }

        foreach ($this->entity_list_by_class as $entity_list) {
            foreach ($entity_list as $other_entity) {
                if ($other_entity->getId() === $entity->getId()) {
                    continue;
                }

                if (!$other_entity->is_obstacle) {
                    continue;
                }

                $is_colliding = Rectangle::areColliding(
                    $target_rectangle,
                    $other_entity->getRectangle()
                );

                if ($is_colliding) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 배치 가능한 위치 목록
     *
     * @param \App\Entity $entity
     * @return array<array{x:int,y:int}>
     */
    public function findAvailablePositionList(Entity $entity): array
    {
        $entity = clone $entity;

        $min_map_x = $entity->half_width;
        $max_map_x = $this->width - $entity->half_width;
        $min_map_y = $entity->half_height;
        $max_map_y = $this->height - $entity->half_height;

        $position_list = [];
        for ($map_y = $min_map_y; $map_y <= $max_map_y; $map_y += $entity->height) {
            for ($map_x = $min_map_x; $map_x <= $max_map_x; $map_x += $entity->width) {
                $entity->setPosition($map_x, $map_y);

                $can_place = $this->canPlaceEntity($entity);

                if ($can_place) {
                    $position_list[] = [
                        'x' => $map_x,
                        'y' => $map_y,
                    ];
                }
            }
        }

        return $position_list;
    }

    public function addPlayer(Player $player): void
    {
        $position_list = $this->findAvailablePositionList($player);
        if (!$position_list) {
            throw new \Exception('추가할 공간이 부족합니다.');
        }

        shuffle($position_list);
        $player->setPosition($position_list[0]['x'], $position_list[0]['y']);

        // TODO: 테스트 코드
        // if ($player->getId() === 1) {
        //     $player->setPosition(48, 368);
        // } elseif ($player->getId() === 2) {
        //     $player->setPosition(48, 336);
        // }

        $this->addEntity($player);
    }

    public function addEntity(Entity $entity): void
    {
        if ($entity->is_obstacle) {
            $can_place = $this->canPlaceEntity($entity);
            if (!$can_place) {
                throw new \Exception('해당 위치에는 추가할 수 없습니다.');
            }
        }

        ($this->entity_list_by_class[$entity::class] ??= new \SplObjectStorage())
            ->attach($entity);
    }

    public function removeEntity(Entity $entity): void
    {
        $entity_list = $this->entity_list_by_class[$entity::class] ?? null;
        if ($entity_list) {
            $entity_list->detach($entity);
        }
    }

    /**
     * @return \App\Entity[]
     */
    public function getEntityList(): array
    {
        $class_name_list = [
            \App\Magic::class,
            \App\Player::class,
            \App\Explosion::class,
        ];

        $result_entity_list = [];
        foreach ($class_name_list as $class_name) {
            $entity_list = $this->entity_list_by_class[$class_name] ?? null;
            if (!$entity_list) {
                continue;
            }

            $result_entity_list = array_merge(
                $result_entity_list,
                iterator_to_array($entity_list),
            );
        }

        return $result_entity_list;
    }

    /**
     * @param class-string<Entity> $class_name
     * @return array<Entity>
     */
    private function getEntityListByClass(string $class_name): array
    {
        /** @var Entity[] */
        $entity_list = $this->entity_list_by_class[$class_name] ?? [];
        return iterator_to_array($entity_list);
    }

    /**
     * @return Player[]
     */
    public function getPlayerList(): array
    {
        /** @var Player[] */
        $entity_list = $this->getEntityListByClass(\App\Player::class);
        return $entity_list;
    }

    /**
     * @return Magic[]
     */
    public function getMagicList(): array
    {
        /** @var Magic[] */
        $entity_list = $this->getEntityListByClass(\App\Magic::class);
        return $entity_list;
    }
}
