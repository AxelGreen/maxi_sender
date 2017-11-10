<?php

    namespace Sender4you\Diagnostics;

    use Common\Connection\MemcachedConnect;
    use Config\SenderConfig;

    class Memory extends Tester
    {

        /**
         * Error codes
         *      71 - free memory is low (< 5%)
         *      72 - free inodes is low (< 5%)
         */

        protected function runTests()
        {

            parent::runTests();

            $this->checkMemory();
            $this->checkInodes();

        }

        protected function fixError(int $error)
        {

            parent::fixError($error);

            switch ($error) {
                case 71: {

                }
                    break;
            }
        }

        private function checkMemory()
        {

            $error = 71;

            $command = 'df --human-readable | sed --silent --regexp-extended "s/.*\s([0-9]+)%\s\/$/\1/p"';
            $result = shell_exec($command) * 1;

            if ($result >= 95) {
                $this->errors[] = $error;
            }

        }

        private function checkInodes()
        {

            $error = 72;

            $command = 'df --inodes --human-readable | sed --silent --regexp-extended "s/.*\s([0-9]+)%\s\/$/\1/p"';
            $result = shell_exec($command) * 1;

            if ($result >= 95) {
                $this->errors[] = $error;
            }

        }

    }