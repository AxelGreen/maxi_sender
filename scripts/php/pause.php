<?php

    use Common\Connection\PgConnection;
    use Sender4you\Distributor\BigBuffer;
    use Sender4you\Log\Error;

    require_once __DIR__.'/vendor/autoload.php';

    try {


        $exim_id = $argv[1];
        if (preg_match('%.{6}-.{6}-.{2}$%', $exim_id) === false) { // wrong exim id
            return;
        }

        // retrieve this message from DB
        $pg_conn = PgConnection::getInstance();
        $query
            = '
            SELECT host FROM public.exim_logs
              WHERE exim_id = $1
        ';
        $log = $pg_conn->query($query,
            array(
                'exim_id' => $exim_id
            ));
        $sending_host = $log[0]['host'];

        $bounce = $argv[2];
        if (empty($bounce)) { // no message supplied
            return;
        }

        $email = explode(' ', $bounce, 2);
        $email = $email[0];

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) { // email not found
            return;
        }
        $email_domain = explode('@', $email);
        $email_domain = $email_domain[1];

        $bigs_buffer = BigBuffer::getInstance();
        $bounce_limits = $bigs_buffer->getBounceLimits();

        if (empty($bounce_limits)) { // no limits for pause
            return;
        }

        // detect Big
        $big = null;
        foreach ($bounce_limits as $big_id => $big_data) {

            if ($big_id <= 0) { // no pauses for not determined Bigs or tests
                continue;
            }

            if (preg_match($big_data['pattern'], $email_domain)) {
                $big = $big_id;
            }

        }

        if ($big === null) { // Big not detected
            return;
        }

        // get previous bounces for this sending_host and Big



    } catch (Exception $ex) {

        // write error log
        Error::push($ex, 'pause');

    }