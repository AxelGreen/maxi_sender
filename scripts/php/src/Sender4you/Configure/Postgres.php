<?php

    namespace Sender4you\Configure;

    use Common\Connection\PgConnection;

    class Postgres
    {

        public function run()
        {

            $connection = PgConnection::getInstance();

            $this->createTables($connection);

        }

        private function createTables(PgConnection $connection)
        {

            // create logs table
            $query
                = '
                CREATE TABLE IF NOT EXISTS exim_logs (
                    date TIMESTAMP WITH TIME ZONE NOT NULL,
                    exim_id VARCHAR(16) NOT NULL PRIMARY KEY,
                    action SMALLINT NOT NULL,
                    message_id VARCHAR NULL,
                    host VARCHAR NULL,
                    error VARCHAR NULL,
                    defer VARCHAR NULL
                )
            ';

            // execute query
            $connection->query($query);

        }

    }