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

        public function set(string $key, $value, int $expiration = null, $encode = true)
        {

            if (empty($key)) {
                return false;
            }

            if ($encode === true) {
                $value = json_encode($value, JSON_HEX_QUOT);
            }

            return $this->connection->set($key, $value, $expiration);

        }

        public function delete(string $key)
        {

            if (empty($key)) {
                return false;
            }

            return $this->connection->delete($key);

        }

    }