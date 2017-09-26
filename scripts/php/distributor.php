<?php

    require_once __DIR__.'/vendor/autoload.php';

    use Common\Connection\RabbitConnection;
    use PhpAmqpLib\Message\AMQPMessage;

    $rabbit_connection = RabbitConnection::getInstance('rabbit_local_send')->getConnection();
    $rabbit_channel = $rabbit_connection->channel();

    $rabbit_channel->queue_declare('local_senders', false, true, false, false);

    $new_pool = array(
        't' => 40,
        'e' => 'axelgreenkp@gmail.com',
        'i' => 134720871,
        'd' => array(
            'n' => 'Axel'
        ),
        'h' => 'host.com'
    );
    $message = new AMQPMessage(json_encode($new_pool, JSON_HEX_QUOT), array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

    $rabbit_channel->basic_publish($message, '', 'local_senders');

    echo('Message send'.PHP_EOL);

    $rabbit_channel->close();
    $rabbit_connection->close();
