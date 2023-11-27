<?php

declare(strict_types=1);

namespace App;

class Magic extends Entity
{
    private const WIDTH = 32;
    private const HEIGHT = 32;

    private const SPRITE_PATH = '/magic.png';
    private const SPRITE_TILE_WIDTH = 192;
    private const SPRITE_TILE_HEIGHT = 192;
    private const SPRITE_TILES_COLS = 5;
    private const SPRITE_TILES_ROWS = 4;
    private const SPRITE_Z_INDEX = 1;

    private const FRAME_COUNT = self::SPRITE_TILES_COLS * self::SPRITE_TILES_ROWS;
    private const FRAME_DELAY = 3;
    private const LAST_ANIME_ACTION_COUNT = self::FRAME_COUNT * self::FRAME_DELAY;

    private const EXPLOSION_LIMIT = 4;
    private const EXPLOSION_DELAY = 5;

    private int $action_count = 0;
    private int $frame_delay_count = -1;
    private int $explosion_count = 0;
    private int $explosion_delay_count = 0;
    private bool $stop_action = false;
    private bool $explosion_activated = false;

    public function __construct(
        \App\Game $game,
    ) {
        parent::__construct(
            game: $game,
            width: self::WIDTH,
            height: self::HEIGHT,
            sprite_path: self::SPRITE_PATH,
            sprite_tile_width: self::SPRITE_TILE_WIDTH,
            sprite_tile_height: self::SPRITE_TILE_HEIGHT,
            sprite_tiles_cols: self::SPRITE_TILES_COLS,
            sprite_tiles_rows: self::SPRITE_TILES_ROWS,
            sprite_z_index: self::SPRITE_Z_INDEX,
            is_obstacle: false,
        );
    }

    public function action(): void
    {
        if ($this->stop_action) {
            return;
        }

        if (
            $this->explosion_activated
        ) {
            if ($this->explosion_delay_count > 0) {
                --$this->explosion_delay_count;
                return;
            } else {
                $this->explosion_delay_count = self::EXPLOSION_DELAY;
            }

            ++$this->explosion_count;

            if ($this->explosion_count === 1) {
                // 중앙
                $pos_list = [
                    [$this->getX(), $this->getY()],
                ];
            } else {
                $expand_explosion_count = $this->explosion_count - 1;
                // 상하좌우 확장
                $pos_list = [
                    [$this->getX() - ($this->width * $expand_explosion_count), $this->getY()],
                    [$this->getX() + ($this->width * $expand_explosion_count), $this->getY()],
                    [$this->getX(), $this->getY() - ($this->height * $expand_explosion_count)],
                    [$this->getX(), $this->getY() + ($this->height * $expand_explosion_count)],
                ];
            }

            foreach ($pos_list as [$x, $y]) {
                $explosion = new Explosion($this->game);
                $explosion->setPosition($x, $y);
                $this->map->addEntity($explosion);
            }

            if ($this->explosion_count >= self::EXPLOSION_LIMIT) {
                $this->map->removeEntity($this);
                $this->stop_action = true;
            }
        }

        ++$this->action_count;

        if ($this->action_count <= self::LAST_ANIME_ACTION_COUNT) {
            if ($this->frame_delay_count >= self::FRAME_DELAY) {
                $this->frame_delay_count = 0;
                $this->nextFrame();
            } else {
                ++$this->frame_delay_count;
            }
        } else {
            $this->explosion_activated = true;
        }
    }

    public function getProxyMagic(): \App\Proxy\Magic
    {
        $damage_horizon_size = $this->width * self::EXPLOSION_LIMIT;
        $damage_horizon_x = (int)($damage_horizon_size / 2);
        $damage_vertical_size = $this->height * self::EXPLOSION_LIMIT;
        $damage_vertical_y = (int)($damage_vertical_size / 2);

        $left_damage_rectangle = new Rectangle(
            width: $damage_horizon_size,
            height: $this->height,
            x: $this->getX() - $damage_horizon_x,
            y: $this->getY(),
        );

        $right_damage_rectangle = new Rectangle(
            width: $damage_horizon_size,
            height: $this->height,
            x: $this->getX() + $damage_horizon_x,
            y: $this->getY(),
        );

        $top_damage_rectangle = new Rectangle(
            width: $this->width,
            height: $damage_vertical_size,
            x: $this->getX(),
            y: $this->getY() - $damage_vertical_y,
        );

        $bottom_damage_rectangle = new Rectangle(
            width: $this->width,
            height: $damage_vertical_size,
            x: $this->getX(),
            y: $this->getY() + $damage_vertical_y,
        );

        return new \App\Proxy\Magic(
            id: $this->id,
            width: $this->width,
            height: $this->height,
            x: $this->getX(),
            y: $this->getY(),
            explosion_count: $this->explosion_count,
            left_damage_rectangle: $left_damage_rectangle,
            right_damage_rectangle: $right_damage_rectangle,
            top_damage_rectangle: $top_damage_rectangle,
            bottom_damage_rectangle: $bottom_damage_rectangle,
        );
    }
}
