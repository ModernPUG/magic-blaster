<?php

declare(strict_types=1);

namespace App;

abstract class Entity implements BaseEntity
{
    private static function generateId(): int
    {
        static $entity_id = 0;
        return ++$entity_id;
    }

    private static function getClassName(): string
    {
        static $class_name = [];

        if (!isset($class_name[static::class])) {
            $class_name[static::class] = join('', array_slice(explode('\\', static::class), -1));
        }

        return $class_name[static::class];
    }

    /**
     * 두 엔터티의 충돌 여부
     *
     * @param \App\Entity $entity1
     * @param \App\Entity $entity2
     * @return bool
     */
    public static function areColliding(self $entity1, self $entity2): bool
    {
        $result = Rectangle::areColliding($entity1->rectangle, $entity2->rectangle);
        return $result;
    }

    public readonly int $half_width;
    public readonly int $half_height;

    protected readonly int $id;
    protected readonly Rectangle $rectangle;
    protected readonly Map $map;

    /** @var array<array<int>> */
    private readonly array $sprite_position_by_frame;

    /** @var array<int> */
    private array $use_frame_list;

    private int $current_frame_index = 0;
    private int $x = 0;
    private int $y = 0;

    public function __construct(
        protected readonly \App\Game $game,
        public readonly int $width,
        public readonly int $height,
        protected readonly string $sprite_path,
        protected readonly int $sprite_tile_width,
        protected readonly int $sprite_tile_height,
        protected readonly int $sprite_tiles_cols,
        protected readonly int $sprite_tiles_rows,
        public readonly int $sprite_z_index,
        public readonly bool $is_obstacle,
    ) {
        $this->map = $game->map;

        $this->id = self::generateId();
        $this->half_width = (int)($this->width / 2);
        $this->half_height = (int)($this->height / 2);

        $this->rectangle = new Rectangle(
            $this->width,
            $this->height,
            $this->x,
            $this->y,
        );

        $sprite_position_by_frame = [];
        for ($y = 0; $y < $this->sprite_tiles_rows; $y++) {
            for ($x = 0; $x < $this->sprite_tiles_cols; $x++) {
                $tile_x = -($x * $this->sprite_tile_width);
                $tile_y = -($y * $this->sprite_tile_height);
                $sprite_position_by_frame[] = [$tile_x, $tile_y];
            }
        }
        $this->sprite_position_by_frame = $sprite_position_by_frame;
        $use_frame_list = range(0, count($sprite_position_by_frame) - 1);
        $this->setUseFrameList($use_frame_list);
    }

    final public function getId(): int
    {
        return $this->id;
    }

    final public function getRectangle(): Rectangle
    {
        return clone $this->rectangle;
    }

    /**
     * @param int[] $use_frame_list
     * @return void
     */
    public function setUseFrameList(array $use_frame_list): void
    {
        $this->use_frame_list = $use_frame_list;
        $this->current_frame_index = 0;
    }

    public function nextFrame(): void
    {
        $this->current_frame_index = ($this->current_frame_index + 1) % count($this->use_frame_list);
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function setPosition(int $x, int $y): void
    {
        $this->x = $x;
        $this->y = $y;
        $this->rectangle->setPosition($x, $y);
    }

    /**
     * @return array<string,mixed>
     */
    public function getData(): array
    {
        $current_frame = $this->use_frame_list[$this->current_frame_index];
        [
            $sprite_tile_x,
            $sprite_tile_y,
        ] = $this->sprite_position_by_frame[$current_frame];

        return [
            'type' => self::getClassName(),
            'id' => $this->id,
            'width' => $this->width,
            'height' => $this->height,
            'sprite_path' => $this->sprite_path,
            'sprite_tile_width' => $this->sprite_tile_width,
            'sprite_tile_height' => $this->sprite_tile_height,
            'sprite_tile_x' => $sprite_tile_x,
            'sprite_tile_y' => $sprite_tile_y,
            'sprite_z_index' => $this->sprite_z_index,
            'x' => $this->x,
            'y' => $this->y,
        ];
    }

    abstract public function action(): void;
}
