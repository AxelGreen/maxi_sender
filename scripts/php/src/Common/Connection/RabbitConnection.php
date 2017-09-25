<?php

    namespace Common\Connection;

    use Config\ConnectionConfig;
    use Exception;
    use PhpAmqpLib\Connection\AMQPStreamConnection;

    class RabbitConnection extends Connection
    {

        private static $instances = array();

        private function __construct(string $db_config_name)
        {

            $db_config = ConnectionConfig::getInstance();

            $conn_params = $db_config->$db_config_name;

            $this->connection = new AMQPStreamConnection($conn_params['host'], $conn_params['port'], $conn_params['user'], $conn_params['password']);

        }

        private function __clone()
        {
        }

        public function __wakeup()
        {

            throw new Exception("Cannot unserialize singleton");
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