<?php

    require_once __DIR__.'/vendor/autoload.php';

    use Common\Connection\RabbitConnection;
    use Config\SenderConfig;
    use PhpAmqpLib\Message\AMQPMessage;

    // sender config
    $sender_settings = SenderConfig::getInstance();

    $rabbit_connection = RabbitConnection::getInstance($sender_settings->local_rabbit['connection_name'])->getConnection();




    $rabbit_connection->close();

