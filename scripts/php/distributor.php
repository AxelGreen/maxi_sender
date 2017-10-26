<?php

    require_once __DIR__.'/vendor/autoload.php';

    use Bunny\Exception\ClientException;
    use Common\Connection\BunnyConnection;
    use Common\Connection\MemcachedConnect;
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

        // time when distributor will exit
        $reload_time = strtotime($settings->distributor_lifetime);

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

        // memcached
        $memcached = MemcachedConnect::getInstance();

        // initialize buffers
        $hosts_buffer = HostBuffer::getInstance();
        $bigs_buffer = BigBuffer::getInstance();

        // initialize variables
        $hosts = array(); // array of hosts connected to this VPS
        $big_speeds = array(); // array of bigs - key is big id, value is speed limit
        $big_delays = array(); // array of timestamps, where key is big id and value is timestamp when next letter for this big must be send.
        $processing_key = ''; // key of current processing. Combines Big.id and host index in it.
        $processing_key_parts = array(); // hold parts of processing key after exploding
        $big_id = null; // id of currently processed Big
        $host_index = null; // index of currently processed host
        $time_left = null; // time to wait until next send
        $declared_queues = array(); // array of queues declared for Bigs with their names. Key is Big.id, value - queue name
        $current_time = null; // holds microtime(true) result for this iteration

        // continuously wait for new messages
        while (true) {

            // check if server active in pool - check variable in memcache
            $active_in_pool = $memcached->get($settings->memcached_pool_param);
            $active_in_pool *= 1;
            if ($active_in_pool === 0) {
                break;
            }

            $current_time = microtime(true);
            if ($reload_time < $current_time) {
                break;
            }

            // update hosts
            $hosts = $hosts_buffer->getHosts();
            if (empty($hosts)) {
                throw new Exception(_('Empty hosts'));
            }

            // update Big speeds
            $big_speeds = $bigs_buffer->getBigs();
            if (empty($big_speeds)) {
                throw new Exception(_('Empty big speeds'));
            }

            // update big_delays
            foreach ($hosts as $key => $host) {
                foreach ($big_speeds as $big_id => $speed_limit) {
                    $next_key = $key.'.'.$big_id;
                    if (isset($big_delays[$next_key])) {
                        continue;
                    }
                    $big_delays[$next_key] = 0;
                }
            }
            asort($big_delays);

            // get next processing key
            $processing_key = array_keys($big_delays)[0];

            // explode key to parts to retrieve host index and big id
            $processing_key_parts = explode('.', $processing_key);

            // get host index of first element of $big_delays - host which use to send this letter
            $host_index = $processing_key_parts[0] * 1;

            // get Big.id of first element of $big_delays - letter for this Big must be send earlier then all another
            $big_id = $processing_key_parts[1] * 1;

            // calculate how much time left to try to get next letter for this Big
            $time_left = $big_delays[$processing_key] - $current_time;
            if ($time_left < 0) { // time already past? we need to send immediately
                $time_left = 0;
            }

            // TODO: check memcache, maybe this Big is blocked

            // sleep until we need to get next next letter
            usleep($time_left * 1000000);

            // add sleep time to current_time
            $current_time += $time_left;

            // check if queue for this Big declared already
            if (empty($declared_queues[$big_id])) { // need to declare queue to be sure that it exist, if already exist - won't be created
                $declared_queues[$big_id] = $settings->remote_bunny['queue_prefix'].$owner_user_id.'.'.$big_id;
                try {
                    $remote_bunny_channel->queueDeclare($declared_queues[$big_id], false, true, false, false);
                } catch (ClientException $ex) {
                    var_dump(1);
                    Error::push($ex, 'distributor');
                }
            }
            // get next letter for this Big
            try {
                $message = $remote_bunny_channel->get($declared_queues[$big_id]);
            } catch (ClientException $ex) {
                Error::push($ex, 'distributor');
            }

            if (empty($message)) { // message is empty, delay send for this big for distributor_delay
                $big_delays[$processing_key] = $current_time + $settings->distributor_delay;
                continue;
            }

            // decode message data
            $data = json_decode($message->content, true);
            // add host to data
            $data['h'] = $hosts[$host_index];
            // get task_id from message routingKey
            $task_id = explode('.', $message->routingKey);
            $task_id = array_pop($task_id) * 1;
            $data['t'] = $task_id;

            // encode data to send to local bunny
            $data = json_encode($data, JSON_HEX_QUOT);

            // push to local bunny
            try {
                $local_bunny_channel->publish($data,
                    array(
                        'delivery-mode' => 2
                    ),
                    '',
                    $settings->local_bunny['queue_name']);
            } catch (Exception $ex) {
                Error::push($ex, 'distributor');
            }

            // acknowledge remote message
            try {
                $remote_bunny_channel->ack($message);
            } catch (ClientException $ex) {
                Error::push($ex, 'distributor');
            }

            // update big_delays
            $big_delays[$processing_key] = $current_time + (60 / $big_speeds[$big_id]);

        }

        // close connection and channels
        $local_bunny_channel->close();
        $local_bunny_connection->disconnect();
        $remote_bunny_channel->close();
        $remote_bunny_connection->disconnect();

        // write log which shows that process stops
        Info::push(_('Distributor stop').' ('.$sender_hash.')', 'distributor');

    } catch (Exception $ex) {

        // write error log
        Error::push($ex, 'distributor');

    }