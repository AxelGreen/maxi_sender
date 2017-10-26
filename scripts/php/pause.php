<?php

    use Common\Connection\MemcachedConnect;
    use Config\SenderConfig;
    use Sender4you\Log\Error;

    require_once __DIR__.'/vendor/autoload.php';

    try {

        // unique identifier for this process.
        $sender_hash = sha1(microtime(true).rand());

        // sender config
        $settings = SenderConfig::getInstance();

        // memcached
        $memcached = MemcachedConnect::getInstance();

    } catch (Exception $ex) {

        // write error log
        Error::push($ex, 'pause');

    }