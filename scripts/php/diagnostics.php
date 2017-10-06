<?php

    require_once __DIR__.'/vendor/autoload.php';

    $cache = new Memcached();
    $cache->addServer('127.0.0.1', 11211);

    //$cache->set('test', 23423);
    $data = $cache->get('test');
    var_dump($data);