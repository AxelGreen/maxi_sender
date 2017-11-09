<?php

    namespace Sender4you\Diagnostics;

    class Memcached extends Tester
    {

        /**
         * Error codes
         *      30 - memcached process not active
         */

        protected function runTests()
        {

            parent::runTests();

            $status = $this->checkService('memcached');
            if ($status === false) {
                $this->errors[] = 30;
            }

        }

        protected function fixError(int $error)
        {

            parent::fixError($error);

            switch ($error) {
                case 30: {
                    $this->restartService('memcached');
                }
                    break;
            }
        }

    }