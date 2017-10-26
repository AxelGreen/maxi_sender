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
                'connection_name' => 'bunny_remote',
                'queue_prefix'    => 'pool.'
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
                'sender_info'       => '/var/log/sender4you/sender.info.log',
                'sender_error'      => '/var/log/sender4you/sender.error.log',
                'distributor_info'  => '/var/log/sender4you/distributor.info.log',
                'distributor_error' => '/var/log/sender4you/distributor.error.log',
                'pause_info'        => '/var/log/sender4you/pause.info.log',
                'pause_error'       => '/var/log/sender4you/pause.error.log',
            );

        // default amount of seconds to wait for Big which don't get any new messages
        public $distributor_delay = 30;

        public $distributor_lifetime = '+6 hours';

        public $memcached_pool_param = 'active_in_pool';

    }