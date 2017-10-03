<?php

    require_once __DIR__.'/vendor/autoload.php';

    use Common\Connection\RabbitConnection;
    use Config\SenderConfig;
    use PhpAmqpLib\Message\AMQPMessage;

    // sender config
    $sender_settings = SenderConfig::getInstance();

    $rabbit_connection = RabbitConnection::getInstance($sender_settings->local_rabbit['connection_name'])->getConnection();
    $rabbit_channel = $rabbit_connection->channel();

    $rabbit_channel->queue_declare($sender_settings->local_rabbit['queue_name'], false, true, false, false);

    $new_pool = array(
        't' => 44,
        'e' => 'axelgreenkp@gmail.com',
        'i' => 134720871,
        'd' => array(
            'n' => 'Axel'
        ),
        'h' => 'albatross-pay.ru'
    );
    $message = new AMQPMessage(json_encode($new_pool, JSON_HEX_QUOT), array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

    $rabbit_channel->basic_publish($message, '', $sender_settings->local_rabbit['queue_name']);

    echo('Message send'.PHP_EOL);

    $rabbit_channel->close();
    $rabbit_connection->close();