<?php

declare(strict_types=1);

namespace App;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Server implements MessageComponentInterface
{
    private const SCREEN_WIDTH = 500;
    private const SCREEN_HEIGHT = 400;

    /** @var \SplObjectStorage<ConnectionInterface,Game> */
    protected \SplObjectStorage $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    private function stopGame(ConnectionInterface $client): void
    {
        $result = $this->clients->contains($client);
        if (!$result) {
            return;
        }

        /** @var \App\Game|null */
        $game = $this->clients->offsetGet($client);
        if ($game) {
            $game->stop();
            $this->clients->offsetSet($client);
        }
    }

    private function newGame(ConnectionInterface $client): void
    {
        $this->stopGame($client);

        $game = new \App\Game(self::SCREEN_WIDTH, self::SCREEN_HEIGHT);
        $game->setUpdateListener(function ($data) use ($client) {
            $client->send(json_encode($data));
        });

        $this->clients->offsetSet($client, $game);

        $client->send(json_encode([
            'type' => 'init_game',
            'data' => [
                'screen_width' => self::SCREEN_WIDTH,
                'screen_height' => self::SCREEN_HEIGHT,
            ],
        ]));

        $game->init();
        $game->run();
    }

    public function onOpen(ConnectionInterface $client): void
    {
        echo "onOpen({$client->resourceId})\n";

        $this->clients->attach($client);
    }

    public function onMessage(ConnectionInterface $client, $message): void
    {
        echo "onMessage({$client->resourceId}) : {$message}\n";

        if ($message === 'new_game') {
            $this->newGame($client);
            return;
        }
    }

    public function onClose(ConnectionInterface $client): void
    {
        echo "onClose({$client->resourceId})\n";

        $this->stopGame($client);
        $this->clients->detach($client);
    }

    public function onError(ConnectionInterface $client, \Exception $e): void
    {
        echo "onError({$client->resourceId}) : {$e->getMessage()}\n";

        $this->stopGame($client);
        $client->close();
    }
}
