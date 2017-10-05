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
        $settings = SenderConfig::getInstance();

        // TODO: set it while installation process
        $owner_user_id = 311;

        // connect
        // local
        $local_bunny_connection = BunnyConnection::getInstance($settings->local_bunny['connection_name'])->getConnection();
        $local_bunny_connection->connect();
        $local_bunny_channel = $local_bunny_connection->channel();
        // declare
        $local_bunny_channel->queueDeclare($settings->local_bunny['queue_name'], false, true, false, false);

        // remote
        $remote_bunny_connection = BunnyConnection::getInstance($settings->remote_bunny['connection_name'])->getConnection();
        $remote_bunny_connection->connect();
        $remote_bunny_channel = $remote_bunny_connection->channel();

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

        // array of timestamps, where key is big id and value is timestamp when next letter for this big must be send. Initialized from big speeds
        $big_delays = $big_speeds;

        // initialize variables
        $big_id = null;
        $time_left = null;
        $declared_queues = array();

        while (true) {

            // get key (Big.id) of first element of $big_delays - letter for this Big must be send earlier then all another
            $big_id = array_keys($big_delays)[0];

            // calculate how much time left to try to get next letter for this Big
            $time_left = $big_delays[$big_id] - microtime(true);
            if ($time_left < 0) { // time already past? we need to send immediately
                $time_left = 0;
            }

            // TODO: check memcache, maybe this Big is blocked

            // sleep until we need to get next next letter
            usleep($time_left * 1000000);

            // check if queue for this Big declared already
            if (empty($declared_queues[$big_id])) { // need to declare queue to be sure that it exist, if already exist - won't be created
                $declared_queues[$big_id] = $settings->remote_bunny['queue_prefix'].$owner_user_id.'.'.$big_id;
                $remote_bunny_channel->queueDeclare($declared_queues[$big_id], false, true, false, false);
                var_dump('declare queue: '.$big_id);
            }
            // get next letter for this Big
            $message = $remote_bunny_channel->get($declared_queues[$big_id]);
            var_dump($message);


            break;

        }

        // close connection and channels
        $local_bunny_channel->close();
        $local_bunny_connection->disconnect();
        $remote_bunny_channel->close();
        $remote_bunny_connection->disconnect();

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
        $settings->local_bunny['queue_name']);

    echo('Message send'.PHP_EOL);









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