<?php

declare(strict_types=1);

ini_set('error_reporting', E_ALL & ~E_DEPRECATED);

require __DIR__ . '/../vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

pcntl_signal(SIGTERM, function () {
    exit;
});

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new \App\Server()
        )
    ),
    8181,
);

$server->run();
