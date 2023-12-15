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

        $user_class_list = [];
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

            $user_class = new \ReflectionClass($classname);
            if (!$user_class->isSubclassOf(User::class)) {
                continue;
            }

            $user_class_list[] = $user_class;
        }

        shuffle($user_class_list);
        foreach ($user_class_list as $user_class) {
            $user = $user_class->newInstance($this->map);
            $player = new \App\Player($this, $user);
            $this->map->addPlayer($player);
        }

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
            $this->dispatchUpdateGame(function (Entity $entity) {
                $entity->action();
            });

            ++$tick;
            if ($tick === 10800) {
                $this->gameOver();
            }
        });
    }

    private function gameOver(): void
    {
        $data = [
            'type' => 'game_over',
        ];

        if ($this->update_listener) {
            call_user_func($this->update_listener, $data);
        }

        $this->stop();
    }

    public function stop(): void
    {
        if (!$this->is_initialized || !$this->timer) {
            return;
        }

        \React\EventLoop\Loop::cancelTimer($this->timer);
    }
}
