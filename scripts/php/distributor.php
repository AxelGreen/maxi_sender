<?php

    require_once __DIR__.'/vendor/autoload.php';

    use Common\Connection\BunnyConnection;
    use Config\SenderConfig;

    //$connection = [
    //    'host'     => '88.99.195.32',
    //    'vhost'    => '/',
    //    'user'     => 'poolManager',
    //    'password' => 'Z2fKlNw2ossS1y5O'
    //];
    //
    //// sender config
    //$sender_settings = SenderConfig::getInstance();
    //
    //$bunny = new Client($connection);
    //$bunny->connect();
    //
    //$channel = $bunny->channel();
    //$channel->queueDeclare('pool.311.1', false, true, false, false);
    //$channel->queueDeclare('pool.311.4', false, true, false, false);
    //
    //$message = $channel->get('pool.311.1');
    //if ($message) {
    //    var_dump($message);
    //    $channel->ack($message);
    //}
    //
    //$message = $channel->get('pool.311.4');
    //var_dump($message);
    //$channel->ack($message);
    //
    //$channel->close();
    //$bunny->disconnect();

    // sender config
    $sender_settings = SenderConfig::getInstance();

    // connect
    $local_bunny_connection = BunnyConnection::getInstance($sender_settings->local_bunny['connection_name'])->getConnection();
    $local_bunny_connection->connect();
    $local_bunny_channel = $local_bunny_connection->channel();

    $local_bunny_channel->queueDeclare($sender_settings->local_bunny['queue_name'], false, true, false, false);

    $new_pool = array(
        't' => 44,
        'e' => 'axelgreenkp@gmail.com',
        'i' => 134720871,
        'd' => array(
            'n' => 'Axel'
        ),
        'h' => 'albatross-pay.ru'
    );
    $local_bunny_channel->publish(json_encode($new_pool, JSON_HEX_QUOT),
        array(
            'delivery-mode' => 2
        ),
        '',
        $sender_settings->local_bunny['queue_name']);

    echo('Message send'.PHP_EOL);

    $local_bunny_channel->close();
    $local_bunny_connection->disconnect();