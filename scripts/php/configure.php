<?php

    use Sender4you\Configure\Exim;
    use Sender4you\Configure\Postgres;

    require_once __DIR__.'/vendor/autoload.php';

    try {

        // exim
        $exim = new Exim();
        // create /etc/exim4/domains.virtual file, create /etc/exim4/dkim/*.pem files
        $exim->run();
        // restart exim
        $exim->restart();

        // postgresql
        $postgres = new Postgres();
        // create table
        $postgres->run();

    } catch (Exception $exception) {

        // generate log entry
        $error = array(
            'date'    => date('Y-m-d H:i:s'),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'message' => '"'.$exception->getMessage().'"'
        );
        $error_message = implode(' ', $error).PHP_EOL;

        echo _('Installation error').': '.$error_message;

    }
