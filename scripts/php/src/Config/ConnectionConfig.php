<?php

    namespace Config;

    use Common\Singleton;

    class ConnectionConfig extends Singleton
    {

        public /** @noinspection SpellCheckingInspection */
            $postgresql
            = array(
            'host'     => 'localhost',
            'dbname'   => 'postgres',
            'user'     => 'postgres',
            'password' => 'AiLwYlHG4rQingrXOyn6Mcionn5SHEts'
        );

        public $rabbit_local_send
            = array(
                'host'     => 'localhost',
                'port'     => 5672,
                'user'     => 'guest',
                'password' => 'guest'
            );

        // TODO: change remote connection params
        public $rabbit_remote
            = array(
                'host'     => 'localhost',
                'port'     => 5672,
                'user'     => 'guest',
                'password' => 'guest'
            );

        public $sender4you_api
            = array(
                'host'           => 'http://api.sender4you.com/',
                'version_prefix' => 'maxi/',
                'endpoints'      => array(
                    'template'        => 'template',
                    'redirectDomains' => 'redirectDomains',
                    'ips'             => 'ips'
                )
            );

    }