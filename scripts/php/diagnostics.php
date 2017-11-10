<?php

    use Common\Connection\MaxiApi;
    use Common\Connection\MemcachedConnect;
    use Config\SenderConfig;
    use Sender4you\Diagnostics\Crontab;
    use Sender4you\Diagnostics\Exim;
    use Sender4you\Diagnostics\Memcached;
    use Sender4you\Diagnostics\Memory;
    use Sender4you\Diagnostics\Postgres;
    use Sender4you\Diagnostics\Bunny;
    use Sender4you\Diagnostics\Running;

    require_once __DIR__.'/vendor/autoload.php';

    // sleep random amount of seconds. We need this to start this process not simultaneously on all VPSs
    // TODO: uncomment
    //sleep(rand(10, 250));

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

    // postgres
    $postgres = new Postgres();
    $errors += $postgres->check()->fix()->check()->getErrors();

    // bunny
    $bunny = new Bunny();
    $errors += $bunny->check()->fix()->check()->getErrors();

    // running - check if all continuous processes is running - distributor, senders, server and so one
    $running = new Running();
    $errors += $running->check()->fix()->check()->getErrors();

    // memory - check free memory and inodes
    $memory = new Memory();
    $errors += $memory->check()->fix()->check()->getErrors();

    var_dump($errors);

    // TODO: uncomment start
    //if (!empty($errors)) { // if some errors found - set in memcached flag that this VPS is broken
    //    $settings = SenderConfig::getInstance();
    //    $memcached = MemcachedConnect::getInstance();
    //    $key = $settings->memcached_diagnostic_param;
    //    // set this flag for one hour, after this time diagnostics can start and check if problems still exist
    //    $memcached->set($key, 1, 60*60, false);
    //}
    // TODO: uncomment end

    // TODO: delete
    return;

    // get connection
    $api_connection = MaxiApi::getInstance();

    // make request
    // TODO: complete API function
    $response = $api_connection->makeRequest('diagnosticReport',
        array(
            'errors' => json_encode($errors, JSON_HEX_QUOT)
        ));

    var_dump($response);
