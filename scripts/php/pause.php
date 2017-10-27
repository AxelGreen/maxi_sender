<?php

    use Common\Connection\MemcachedConnect;
    use Sender4you\Distributor\BigBuffer;
    use Sender4you\Log\Error;
    use Sender4you\Pause\Helper;

    require_once __DIR__.'/vendor/autoload.php';

    try {

        // helper which holds all functions to process
        $helper = new Helper();

        // validate exim_id and retrieve sending_host for this message from DB
        $sending_host = $helper->retrieveHost($argv[1]);
        if (empty($sending_host)) {
            return false;
        }

        // validate bounce and get email_domain
        $email_domain = $helper->parseBounce($argv[2]);
        if (empty($email_domain)) {
            return false;
        }

        // retrieve bounce_limits from Memcache or central server (if there is no local copy)
        $bigs_buffer = BigBuffer::getInstance();
        $bounce_limits = $bigs_buffer->getBounceLimits();
        if (empty($bounce_limits)) { // no data about limits, so can't process
            return false;
        }

        // detect Big
        $big_id = $helper->detectBigId($email_domain, $bounce_limits);
        if (empty($big_id)) { // Big not detected
            return;
        }

        // retrieve previous bounces from Memcache
        $previous_bounces = $helper->getPreviousBounces($sending_host, $big_id);
        if ( !empty($previous_bounces)) {
            $previous_bounces = $helper->filterPreviousBounces($previous_bounces);
        }

        // add new bounce
        $previous_bounces[] = time();
        // save previous_bounces to memcache
        $helper->savePreviousBounces($sending_host, $big_id, $previous_bounces);

        // check bounces limit
        if (count($previous_bounces) < $bounce_limits[$big_id]['bounce_limit']) { // not enough bounces for pause
            return true;
        }

        // save to memcache time when we can restore sending messages for this sender_host and big
        $cache_key = $sending_host.'|||'.$big_id;
        $memcached = MemcachedConnect::getInstance();
        $restore_at = microtime(true) + ($bounce_limits[$big_id]['pause'] * 3600);
        // save 1 to memcache with expiration time (when we can restore sending). So if this key is not set in memcache - we can continue to send this
        $memcached->set($cache_key, $restore_at, $bounce_limits[$big_id]['pause'] * 3600, false);

    } catch (Exception $ex) {

        // write error log
        Error::push($ex, 'pause');

    }