<?php

    use Sender4you\Diagnostics\Crontab;
    use Sender4you\Diagnostics\Exim;
    use Sender4you\Diagnostics\Memcached;
    use Sender4you\Diagnostics\Postgres;

    require_once __DIR__.'/vendor/autoload.php';

    // get errors for each systems
    $errors = array();

    // crontab
    $crontab = new Crontab();
    $errors += $crontab->check()->fix()->check()->getErrors();

    // exim
    $exim = new Exim();
    $exim->setEximConfig(new \Sender4you\Configure\Exim());
    $errors += $exim->check()->fix()->check()->getErrors();

    // memcached
    $memcached = new Memcached();
    $errors += $memcached->check()->fix()->check()->getErrors();

    // memcached
    $postgres = new Postgres();
    $errors += $postgres->check()->fix()->check()->getErrors();

    var_dump($errors);
