<?php

    require_once __DIR__.'/vendor/autoload.php';

    use Bunny\Client;
    use Common\Connection\RabbitConnection;
    use Config\SenderConfig;

    $connection = [
        'host'     => '88.99.195.32',
        'vhost'    => '/',
        'user'     => 'poolManager',
        'password' => 'Z2fKlNw2ossS1y5O'
    ];

    // sender config
    $sender_settings = SenderConfig::getInstance();

    $bunny = new Client($connection);
    $bunny->connect();

    $channel = $bunny->channel();
    $channel->queueDeclare('pool.311.1', false, true, false, false);
    $channel->queueDeclare('pool.311.4', false, true, false, false);

    $message = $channel->get('pool.311.1');
    if ($message) {
        var_dump($message);
        $channel->ack($message);
    }

    $message = $channel->get('pool.311.4');
    var_dump($message);
    $channel->ack($message);

    $channel->close();
    $bunny->disconnect();