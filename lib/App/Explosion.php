<?php

declare(strict_types=1);

namespace App;

class Explosion extends Entity
{
    private const WIDTH = 32;
    private const HEIGHT = 32;

    private const SPRITE_PATH = '/explosion.png';
    private const SPRITE_TILE_WIDTH = 192;
    private const SPRITE_TILE_HEIGHT = 192;
    private const SPRITE_TILES_COLS = 5;
    private const SPRITE_TILES_ROWS = 2;
    private const SPRITE_Z_INDEX = 3;

    private const FRAME_COUNT = self::SPRITE_TILES_COLS * self::SPRITE_TILES_ROWS;
    private const FRAME_DELAY = 2;
    private const ACTION_LIMIT = self::FRAME_COUNT * self::FRAME_DELAY * 2;

    private int $action_count = 0;
    private int $frame_delay_count = -1;
    private bool $stop_action = false;

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

        ++$this->action_count;

        if ($this->action_count === 1) {
            $player_list = $this->map->getPlayerList();
            foreach ($player_list as $player) {
                if (Entity::areColliding($this, $player)) {
                    $player->incrementDamage();
                }
            }
        }

        if ($this->action_count === self::ACTION_LIMIT) {
            $this->stop_action = true;
            $this->map->removeEntity($this);
            return;
        }

        if ($this->frame_delay_count >= self::FRAME_DELAY) {
            $this->frame_delay_count = 0;
            $this->nextFrame();
        } else {
            ++$this->frame_delay_count;
        }
    }
}
