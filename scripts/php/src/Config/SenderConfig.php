<?php

    namespace Config;

    use Common\Singleton;

    class SenderConfig extends Singleton
    {

        /**
         * @var array - params for local Bunny connection
         */
        public $local_bunny
            = array(
                'connection_name' => 'bunny_local',
                'queue_name'      => 'local_senders'
            );

        /**
         * @var array - params for local Bunny connection
         */
        public $remote_bunny
            = array(
                'connection_name' => 'bunny_remote'
            );

        /**
         * @var int - existing time for one sender in seconds. After this amount of seconds + random amount of minutes (from 1 to 60) - this sender will stop listening and exit. Used
         *      to recreate consumers sometimes.
         */
        public $time_to_live = 60 * 60 * 6;

        /**
         * @var array - log files location
         */
        public $logs
            = array(
                'info'  => '/var/log/sender4you/sender_info.log',
                'error' => '/var/log/sender4you/sender_error.log'
            );

    }