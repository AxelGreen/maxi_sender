<?php

    namespace Sender4you\Diagnostics;

    use Bunny\Exception\ClientException;
    use Common\Connection\BunnyConnection;
    use Common\Connection\MemcachedConnect;
    use Config\SenderConfig;

    class Running extends Tester
    {

        /**
         * Error codes
         *      61 - mutator not running
         *      62 - sends not running
         *      63 - distributor not running
         *      64 - server not running
         */

        protected function runTests()
        {

            parent::runTests();

            $this->checkMutator();
            $this->checkSends();
            $this->checkDistributor();
            $this->checkServer();

        }

        protected function fixError(int $error)
        {

            parent::fixError($error);

            switch ($error) {
                case 61: {
                    $this->startMutator();
                }
                    break;
                case 62: {
                    $this->startSends();
                }
                    break;
                case 63: {
                    $this->startDistributor();
                }
                    break;
                case 64: {
                    $this->startServer();
                }
                    break;
            }
        }

        private function checkMutator()
        {

            $error = 61;

            $command = 'pgrep --full --count "^tail.*/var/log/exim4/mainlog$"';
            $result = shell_exec($command) * 1;

            if ($result <= 0) {
                $this->errors[] = $error;
            }

        }

        private function checkSends()
        {

            $error = 62;

            $command = 'pgrep --full --count "^php7\.0.*/etc/sender4you/send\.php$"';
            $result = shell_exec($command) * 1;

            if ($result <= 0) {
                $this->errors[] = $error;
            }

        }

        private function checkDistributor()
        {

            $error = 63;

            $command = 'pgrep --full --count "^php7\.0.*/etc/sender4you/distributor\.php$"';
            $result = shell_exec($command) * 1;

            if ($result > 0) {
                return;
            }

            $settings = SenderConfig::getInstance();
            $memcached = MemcachedConnect::getInstance();
            $active = $memcached->get($settings->memcached_pool_param) * 1;

            if ($active === 1) {
                $this->errors[] = $error;
            }

        }

        private function checkServer()
        {

            $error = 64;

            $command = 'pgrep --full --count "^php7\.0 --server 0\.0\.0\.0:80 --docroot /etc/sender4you/public/ /etc/sender4you/server\.php$"';
            $result = shell_exec($command) * 1;

            if ($result <= 0) {
                $this->errors[] = $error;
            }

        }

        private function startMutator()
        {

            $command = '/etc/sender4you/bash/run_mutator.sh >/dev/null 2>>/var/log/sender4you/run_mutator.log';
            shell_exec($command);

        }

        private function startSends()
        {

            $command = '/etc/sender4you/bash/run_send.sh >/dev/null 2>>/var/log/sender4you/run_send.log';
            shell_exec($command);

        }

        private function startDistributor()
        {

            $command = '/etc/sender4you/bash/run_distributor.sh >/dev/null 2>>/var/log/sender4you/run_distributor.log';
            shell_exec($command);

        }

        private function startServer()
        {

            $command = '/etc/sender4you/bash/run_server.sh >/dev/null 2>>/var/log/sender4you/run_server.log';
            shell_exec($command);

        }

    }