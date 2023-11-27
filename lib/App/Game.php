<?php

declare(strict_types=1);

namespace App;

class Game
{
    public readonly \App\Map $map;

    private ?\Closure $update_listener = null;

    private bool $is_initialized = false;

    private ?\React\EventLoop\TimerInterface $timer = null;

    public function __construct(
        int $screen_width,
        int $screen_height,
    ) {
        $this->map = new \App\Map($screen_width, $screen_height);
    }

    public function init(): void
    {
        if ($this->is_initialized) {
            return;
        }

        $dir_it = new \DirectoryIterator(__DIR__ . '/../Users');
        foreach ($dir_it as $fileinfo) {
            if (
                $fileinfo->isDot()
                || $fileinfo->getType() != 'file'
                || $fileinfo->getExtension() != 'php'
            ) {
                continue;
            }

            $filename = $fileinfo->getFilename();
            $classname = 'Users\\' . preg_replace('/\.php$/', '', $filename);

            $class = new \ReflectionClass($classname);
            if (!$class->isSubclassOf(User::class)) {
                continue;
            }

            $user = $class->newInstance($this->map);

            $player = new \App\Player($this, $user);
            $this->map->addPlayer($player);
        }

        // $bot_user = new class () extends User {
        //     public function getName(): string
        //     {
        //         return 'bot';
        //     }

        //     public function action(PlayerInfo $player_info): UserAction
        //     {
        //         return UserAction::STOP;
        //     }
        // };

        // $time_magic = new TimeMagic($this);
        // $time_magic->setPosition(300, 100);
        // $this->map->addEntity($time_magic);

        // $player = new \App\Player($this, $bot_user);
        // $player->setPosition(300, 100);
        // $this->map->addEntity($player);

        // for ($i = 1; $i <= 32; $i++) {
        //     $user = new \Users\SampleUser();
        //     $player = new \App\Player($this, $user);
        //     $this->map->addPlayer($player);
        // }

        $this->dispatchUpdateGame();
        $this->is_initialized = true;
    }

    public function setUpdateListener(\Closure $update_listener): void
    {
        $this->update_listener = $update_listener;
    }

    private function dispatchUpdateGame(?callable $entity_callback = null): void
    {
        $entity_list = $this->map->getEntityList();
        $entity_data_list = [];
        foreach ($entity_list as $entity) {
            if ($entity_callback) {
                call_user_func($entity_callback, $entity);
            }
            $entity_data_list[] = $entity->getData();
        }

        $data = [
            'type' => 'update_game',
            'data' => [
                'entity_list' => $entity_data_list,
            ],
        ];

        if ($this->update_listener) {
            call_user_func($this->update_listener, $data);
        }
    }

    public function run(): void
    {
        if (!$this->is_initialized) {
            throw new \Exception('Game is not initialized.');
        }

        $tick = 0;
        $interval = 0.0167;

        $this->timer = \React\EventLoop\Loop::addPeriodicTimer($interval, function () use (&$tick) {
            // if ($tick % 50 === 0) {
            //     $time_magic = new Magic($this);
            //     $x = mt_rand($time_magic->width, $this->map->width - $time_magic->width);
            //     $y = mt_rand($time_magic->height, $this->map->height - $time_magic->height);
            //     $time_magic->setPosition($x, $y);
            //     $this->map->addEntity($time_magic);
            // }

            $this->dispatchUpdateGame(function (Entity $entity) {
                $entity->action();
            });

            ++$tick;
            if ($tick === 18000) {
                $this->stop();
            }
        });
    }

    public function stop(): void
    {
        if (!$this->is_initialized || !$this->timer) {
            return;
        }

        \React\EventLoop\Loop::cancelTimer($this->timer);
    }
}
