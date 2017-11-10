<?php

    namespace Common\Connection;

    use Config\ConnectionConfig;
    use Exception;

    class PgConnection extends Connection
    {

        private static $instance = null;

        private function __construct()
        {

            $db_config = ConnectionConfig::getInstance();
            $connection_string = $this->createConnectionString($db_config);
            $this->connection = pg_connect($connection_string);
            if ($this->connection === false) {
                throw new Exception('Can\'t connect to postgres');
            }

        }

        private function createConnectionString(ConnectionConfig $config)
        {

            $params = $config->postgresql;

            $connection_string_parts = array();
            foreach ($params as $keyword => $value) {
                $connection_string_parts[] = $keyword.'='.$value;
            }
            $connection_string = implode(' ', $connection_string_parts);

            return $connection_string;

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

        public function query(string $query, array $params = array())
        {

            if (empty($query)) {
                return array();
            }

            $rows = pg_query_params($this->connection, $query, $params);

            if ($rows === false) {
                throw new Exception(pg_last_error($this->connection));
            }

            $results = array();
            while ($row = pg_fetch_assoc($rows)) {
                $results[] = $row;
            }

            return $results;

        }

    }