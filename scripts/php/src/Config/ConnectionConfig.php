<?php

    namespace Config;

    use Common\Singleton;

    class ConnectionConfig extends Singleton
    {

        public
            $postgresql
            = array(
            'host'     => 'localhost',
            'dbname'   => 'postgres',
            'user'     => 'postgres',
            'password' => ''
        );

        public $bunny_local
            = array(
                'host'      => 'localhost',
                'vhost'     => '/',
                'user'      => 'guest',
                'password'  => 'guest',
                // TODO: change to normal value when handle problem with connection recreating
                'heartbeat' => 30000
            );

        public $bunny_remote
            = array(
                'host'     => '88.99.195.32',
                'vhost'    => '/',
                'user'     => 'distributor.311',
                'password' => 'Z2fKlNw2ossS1y5O'
            );

        public $sender4you_api
            = array(
                'host'           => 'http://api.sender4you.com/',
                'version_prefix' => 'maxi/',
                'endpoints'      => array(
                    'template'        => 'template',
                    'redirectDomains' => 'redirectDomains',
                    'ips'             => 'ips',
                    'hosts'           => 'hosts',
                    'bigs'            => 'bigs',
                    'vps'             => 'vps'
                )
            );

        public $memcached
            = array(
                'host' => '127.0.0.1',
                'post' => 11211
            );

    }