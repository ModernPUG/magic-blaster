<?php

declare(strict_types=1);

namespace App;

class Player extends Entity
{
    public const WIDTH = 32;
    public const HEIGHT = 32;

    private const FRAME_DELAY = 10;
    private const MAGIC_DELAY = 60;
    private const MOVE_INERTIA = 16;

    private const FRAME_LIST_BY_DIRECTION = [
        PlayerDirection::DOWN->value => [0, 1, 2],
        PlayerDirection::LEFT->value => [3, 4, 5],
        PlayerDirection::RIGHT->value => [6, 7, 8],
        PlayerDirection::UP->value => [9, 10, 11],
    ];

    private static function generateSpriteNumber(): int
    {
        static $number_list;

        if (!isset($number_list)) {
            $number_list = range(1, 32);
            shuffle($number_list);
        }

        $current_number = current($number_list);

        next($number_list);

        if (key($number_list) === null) {
            reset($number_list);
        }

        return $current_number;
    }

    private ?PlayerDirection $direction = null;
    private ?UserAction $user_move_action = null;
    private int $frame_delay_count = -1;
    private int $magic_delay_count = 0;
    private int $move_inertia_count = 0;
    private int $damage = 0;

    public function __construct(
        \App\Game $game,
        private readonly User $user,
    ) {
        $sprite_num = self::generateSpriteNumber();
        $sprite_path = sprintf('/players/player%d.png', $sprite_num);

        parent::__construct(
            game: $game,
            width: self::WIDTH,
            height: self::HEIGHT,
            sprite_path: $sprite_path,
            sprite_tile_width: 32,
            sprite_tile_height: 32,
            sprite_tiles_cols: 3,
            sprite_tiles_rows: 4,
            sprite_z_index: 2,
            is_obstacle: true,
        );

        $this->setDirection(PlayerDirection::DOWN);
    }

    private function resetFrameDelayCount(): void
    {
        $this->frame_delay_count = 0;
    }

    private function setDirection(PlayerDirection $direction): void
    {
        if ($this->direction === $direction) {
            return;
        }

        $this->direction = $direction;
        $this->setUseFrameList(self::FRAME_LIST_BY_DIRECTION[$direction->value]);
        $this->resetFrameDelayCount();
    }

    private function moveBy(int $x, int $y): void
    {
        $prev_x = $this->getX();
        $prev_y = $this->getY();

        $this->setPosition($prev_x + $x, $prev_y + $y);

        $result = $this->map->canPlaceEntity($this);
        if (!$result) {
            // 이동이 막히면 이동 관성을 없앤다.
            $this->move_inertia_count = 0;
            $this->setPosition($prev_x, $prev_y);
        }
    }

    public function moveDown(): void
    {
        $this->setDirection(PlayerDirection::DOWN);
        $this->moveBy(0, 1);
    }

    public function moveLeft(): void
    {
        $this->setDirection(PlayerDirection::LEFT);
        $this->moveBy(-1, 0);
    }

    public function moveRight(): void
    {
        $this->setDirection(PlayerDirection::RIGHT);
        $this->moveBy(1, 0);
    }

    public function moveUp(): void
    {
        $this->setDirection(PlayerDirection::UP);
        $this->moveBy(0, -1);
    }

    public function doMagic(): void
    {
        if ($this->magic_delay_count > 0) {
            return;
        }

        $this->magic_delay_count = self::MAGIC_DELAY;

        $magic = new Magic($this->game);
        $magic->setPosition($this->getX(), $this->getY());
        $this->map->addEntity($magic);
    }

    private function getProxyPlayer(): \App\Proxy\Player
    {
        return new  \App\Proxy\Player(
            id: $this->id,
            width: $this->width,
            height: $this->height,
            x: $this->getX(),
            y: $this->getY(),
            damage: $this->damage,
            can_use_magic: $this->magic_delay_count === 0,
        );
    }

    private function callUserAction(): UserAction
    {
        $player_info = $this->getProxyPlayer();

        $player_list = $this->map->getPlayerList();
        $other_player_info_list = [];
        foreach ($player_list as $player) {
            if ($player === $this) {
                continue;
            }

            $other_player_info_list[] = $player->getProxyPlayer();
        }

        $magic_list = $this->map->getMagicList();
        $magic_info_list = [];
        foreach ($magic_list as $magic) {
            $magic_info_list[] = $magic->getProxyMagic();
        }

        try {
            $user_action = $this->user->action(
                $this->map->proxy_map,
                $player_info,
                $other_player_info_list,
                $magic_info_list,
            );
        } catch (\Throwable $e) {
            $user_action = null;

            $message = $e->getMessage();
            $trace = $e->getTraceAsString();
            error_log("유저 액션 오류: {$message}\n{$trace}");
        }

        return $user_action ?? UserAction::STOP;
    }

    public function action(): void
    {
        if ($this->frame_delay_count >= self::FRAME_DELAY) {
            $this->resetFrameDelayCount();
            $this->nextFrame();
        } else {
            ++$this->frame_delay_count;
        }

        if ($this->magic_delay_count > 0) {
            --$this->magic_delay_count;
        }

        // 이동 관성이 있는 상태라면
        if ($this->move_inertia_count > 0) {
            --$this->move_inertia_count;
            $user_action = $this->user_move_action;
        } else {
            $user_action = $this->callUserAction();
            if ($user_action->isMove()) {
                $this->user_move_action = $user_action;
                $this->move_inertia_count = self::MOVE_INERTIA;
            }
        }

        $method = match ($user_action) {
            UserAction::DOWN => $this->moveDown(...),
            UserAction::LEFT => $this->moveLeft(...),
            UserAction::RIGHT => $this->moveRight(...),
            UserAction::UP => $this->moveUp(...),
            UserAction::MAGIC => $this->doMagic(...),
            default => null,
        };

        if ($method) {
            call_user_func($method);
        }
    }

    public function incrementDamage(): void
    {
        ++$this->damage;
    }

    public function getData(): array
    {
        $data = [
            ...parent::getData(),
            'username' => $this->user->getName(),
            'damage' => $this->damage,
        ];
        return $data;
    }
}
