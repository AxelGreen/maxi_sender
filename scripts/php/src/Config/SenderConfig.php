<?php

    namespace Config;

    use Common\Singleton;

    class SenderConfig extends Singleton
    {

        /**
         * @var array - params for local Rabbit connection
         */
        public $local_rabbit
            = array(
                'connection_name' => 'rabbit_local',
                'queue_name'      => 'local_senders'
            );

        /**
         * @var string - existing time for one sender. After this amount of time + random amount of minutes (from 1 to 60) + process one more message - this sender will stop listening and exit. Used
         *      to recreate consumers sometimes.
         */
        public $time_to_live = '+6 hours';

        /**
         * @var array - log files location
         */
        public $logs
            = array(
                'info'  => '/var/log/sender4you/sender_info.log',
                'error' => '/var/log/sender4you/sender_error.log'
            );

    }