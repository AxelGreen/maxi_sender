<?php

    namespace Common\Connection;

    use Bunny\Client;
    use Config\ConnectionConfig;
    use Exception;

    class BunnyConnection extends Connection
    {

        private static $instances = array();

        private function __construct(string $db_config_name)
        {

            $db_config = ConnectionConfig::getInstance();

            $conn_params = $db_config->$db_config_name;

            $this->connection = new Client($conn_params);

        }

        private function __clone()
        {
        }

        public function __wakeup()
        {

            throw new Exception('Cannot unserialize singleton');
        }

        /**
         * @param string $db_config_name
         *
         * @return self
         */
        public static function getInstance(string $db_config_name)
        {

            if ( !isset(self::$instances[$db_config_name])) {
                self::$instances[$db_config_name] = new static($db_config_name);
            }

            return self::$instances[$db_config_name];
        }

        public function getConnection()
        {

            return $this->connection;
        }

    }