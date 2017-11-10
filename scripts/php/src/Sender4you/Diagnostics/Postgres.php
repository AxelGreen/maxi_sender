<?php

    namespace Sender4you\Diagnostics;

    use Common\Connection\PgConnection;
    use Exception;

    class Postgres extends Tester
    {

        /**
         * Error codes
         *      40 - Postgres process not active
         *      41 - can't connect or another write/read problems
         *      42 - exim_logs table not exist
         */

        protected function runTests()
        {

            parent::runTests();

            $status = $this->checkService('postgresql');
            if ($status === false) {
                $this->errors[] = 40;

                return;
            }

            $this->checkConnection();
            if ( !empty($this->errors)) { // don't need to do another checks if can't connect
                return;
            }

            $this->tableExist();

        }

        protected function fixError(int $error)
        {

            parent::fixError($error);

            switch ($error) {
                case 40:
                case 41: {
                    $this->restartService('postgresql');
                }
                    break;
                case 42: {
                    $this->createTable();
                }
                    break;
            }
        }

        private function checkConnection()
        {

            $error = 41;

            $postgres = null;
            try {
                $postgres = PgConnection::getInstance();
            } catch (Exception $ex) {
                $this->errors[] = $error;

                return;
            }

            $hash = sha1(time());
            $query
                = '
                SELECT $1::varchar AS hash
            ';

            try {
                $result = $postgres->query($query,
                    array(
                        'hash' => $hash
                    ));
            } catch (Exception $ex) {
                $this->errors[] = $error;

                return;
            }

            if ($result[0]['hash'] !== $hash) {
                $this->errors[] = $error;
            }

        }

        private function tableExist()
        {

            $error = 42;

            $postgres = null;
            try {
                $postgres = PgConnection::getInstance();
            } catch (Exception $ex) {
                $this->errors[] = $error;

                return;
            }

            $query
                = '
                SELECT to_regclass(\'public.exim_logs\') AS exists
            ';

            try {
                $result = $postgres->query($query);
            } catch (Exception $ex) {
                $this->errors[] = $error;

                return;
            }

            if ($result[0]['exists'] === null) {
                $this->errors[] = $error;
            }
        }

        private function createTable()
        {

            $postgres_config = new \Sender4you\Configure\Postgres();
            $postgres_config->run();

        }

    }