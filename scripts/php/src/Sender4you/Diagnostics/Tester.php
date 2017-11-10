<?php

    namespace Sender4you\Diagnostics;

    class Tester
    {

        protected $errors = array();

        public function check()
        {

            // reset errors
            $this->errors = array();

            // run all tests
            $this->runTests();

            return $this;

        }

        protected function runTests()
        {

        }

        public function fix()
        {

            // no errors to fix
            if (empty($this->errors)) {
                return $this;
            }

            // go throw each error
            foreach ($this->errors as $key => $error) {
                $this->fixError($error);
                unset($this->errors[$key]);
            }

            return $this;

        }

        protected function fixError(int $error)
        {

        }

        public function getErrors()
        {

            return $this->errors;

        }

        /**
         * Check if service is running and active
         *
         * @param string $service
         *
         * @return bool
         */
        protected function checkService(string $service) : bool
        {

            $command = '/etc/init.d/'.$service.' status | sed -n -r "s/.*Active: active .*/1/p" | wc --lines';

            $result = shell_exec($command) * 1;
            if ($result <= 0) {
                return false;
            }

            return true;

        }

        /**
         * restarts service
         *
         * @param string $service
         */
        protected function restartService(string $service)
        {

            $command = '/etc/init.d/'.$service.' restart';
            shell_exec($command);

        }

    }