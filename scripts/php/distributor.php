<?php

    require_once __DIR__.'/vendor/autoload.php';

    use Common\Connection\BunnyConnection;
    use Config\SenderConfig;
    use Sender4you\Distributor\BigBuffer;
    use Sender4you\Distributor\HostBuffer;
    use Sender4you\Log\Error;
    use Sender4you\Log\Info;

    try {

        // unique identifier for this process.
        $sender_hash = sha1(microtime(true).rand());

        // write log which shows that process starts
        Info::push(_('Distributor start').' ('.$sender_hash.')', 'distributor');


        // sender config
        $sender_settings = SenderConfig::getInstance();

        // connect
        // local
        $local_bunny_connection = BunnyConnection::getInstance($sender_settings->local_bunny['connection_name'])->getConnection();
        $local_bunny_connection->connect();
        $local_bunny_channel = $local_bunny_connection->channel();
        // declare
        $local_bunny_channel->queueDeclare($sender_settings->local_bunny['queue_name'], false, true, false, false);

        // remote
        $remote_bunny_connection = BunnyConnection::getInstance($sender_settings->remote_bunny['connection_name'])->getConnection();
        $remote_bunny_connection->connect();
        $remote_bunny_channel = $remote_bunny_connection->channel();

        // array of timestamps, where key is big id and value is timestamp when next letter for this big must be send
        $big_delays = array();

        // array of hosts connected to this VPS
        $hosts_buffer = HostBuffer::getInstance();
        $hosts = $hosts_buffer->getHosts();
        var_dump($hosts);
        if (empty($hosts)) {
            throw new Exception(_('Empty hosts'));
        }

        // array of bigs - key is big id, value is speed limit
        $bigs_buffer = BigBuffer::getInstance();
        $big_speeds = $bigs_buffer->getBigs();
        var_dump($big_speeds);
        if (empty($big_speeds)) {
            throw new Exception(_('Empty big speeds'));
        }

        while (true) {


            break;

        }

        // write log which shows that process stops
        Info::push(_('Distributor stop').' ('.$sender_hash.')', 'distributor');

    } catch (Exception $ex) {

        var_dump($ex->getMessage());

        // write error log
        Error::push($ex, 'distributor');

    }



    return;



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