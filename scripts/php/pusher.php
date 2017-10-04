<?php

    require_once __DIR__.'/vendor/autoload.php';

    use Common\Connection\BunnyConnection;
    use Config\SenderConfig;
    use PhpAmqpLib\Connection\AMQPStreamConnection;
    use PhpAmqpLib\Message\AMQPMessage;


