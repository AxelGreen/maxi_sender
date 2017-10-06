<?php

    namespace Common\Connection;

    use Config\ConnectionConfig;
    use Memcached;

    class MemcachedConnect extends Connection
    {

        private static $instance = null;

        private function __construct()
        {

            $config = ConnectionConfig::getInstance();
            $this->connection = new Memcached();
            $this->connection->addServer($config->memcached['host'], $config->memcached['post']);
        }

        /**
         * @return null|self
         */
        public static function getInstance()
        {

            if ( !isset(self::$instance)) {
                self::$instance = new static();
            }

            return self::$instance;
        }

        public function getConnection()
        {

            return $this->connection;

        }

        public function get(string $key)
        {

            if (empty($key)) {
                return null;
            }

            $results = $this->connection->get($key);

            if ($results === false) {
                return null;
            }

            return $results;

        }

    }