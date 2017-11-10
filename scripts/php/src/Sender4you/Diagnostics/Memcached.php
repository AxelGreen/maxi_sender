<?php

    namespace Sender4you\Diagnostics;

    use Common\Connection\MemcachedConnect;

    class Memcached extends Tester
    {

        /**
         * Error codes
         *      30 - memcached process not active
         *      31 - can't connect or another write/read problems
         */

        protected function runTests()
        {

            parent::runTests();

            $status = $this->checkService('memcached');
            if ($status === false) {
                $this->errors[] = 30;

                return;
            }

            $this->checkConnection();

        }

        protected function fixError(int $error)
        {

            parent::fixError($error);

            switch ($error) {
                case 30:
                case 31: {
                    $this->restartService('memcached');
                }
                    break;
            }
        }

        private function checkConnection()
        {

            $error = 31;

            $memcached = MemcachedConnect::getInstance();
            $key = 'diagnostics_test';
            $hash = sha1(time());
            $memcached->set($key, $hash, 100, false);

            $saved_hash = $memcached->get($key);

            if ($saved_hash !== $hash) {
                $this->errors[] = $error;

                return;
            }

            $memcached->delete($key);

        }

    }