<?php

    namespace Sender4you\Diagnostics;

    use Bunny\Exception\ClientException;
    use Common\Connection\BunnyConnection;
    use Config\SenderConfig;

    class Bunny extends Tester
    {

        /**
         * Error codes
         *      50 - Rabbit server not active
         *      51 - can't connect to local rabbit
         *      52 - can't connect to remote rabbit
         */

        protected function runTests()
        {

            parent::runTests();

            $status = $this->checkService('rabbitmq-server');
            if ($status === false) {
                $this->errors[] = 50;

                return;
            }

            $this->checkLocalConnection();
            $this->checkRemoteConnection();

        }

        protected function fixError(int $error)
        {

            parent::fixError($error);

            switch ($error) {
                case 50:
                case 51: {
                    $this->fixInstallation();
                    $this->restartService('rabbitmq-server');
                }
                    break;
            }
        }

        private function checkLocalConnection()
        {

            $error = 51;

            $settings = SenderConfig::getInstance();
            $bunny = BunnyConnection::getInstance($settings->local_bunny['connection_name'])->getConnection();
            try {
                $bunny->connect();
            } catch (ClientException $ex) {
                $this->errors[] = $error;
            }

            $bunny->disconnect();

        }

        private function checkRemoteConnection()
        {

            $error = 52;

            $settings = SenderConfig::getInstance();
            $bunny = BunnyConnection::getInstance($settings->remote_bunny['connection_name'])->getConnection();
            try {
                $bunny->connect();
            } catch (ClientException $ex) {
                $this->errors[] = $error;
            }

            $bunny->disconnect();

        }

        private function fixInstallation()
        {

            $command = 'apt-get install -y -f';
            shell_exec($command);

        }

    }