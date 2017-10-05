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
                'host'     => 'localhost',
                'vhost'    => '/',
                'user'     => 'guest',
                'password' => 'guest'
            );

        public
            $bunny_remote
            = array(
            'host'     => '88.99.195.32',
            'vhost'    => '/',
            'user'     => 'poolManager',
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
                    'bigs'            => 'bigs'
                )
            );

    }