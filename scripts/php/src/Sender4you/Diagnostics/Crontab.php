<?php

    namespace Sender4you\Diagnostics;

    class Crontab extends Tester
    {

        /**
         * Error codes
         *      10 - crontab process not active
         *      11 - content of crontab_template is empty
         *      12 - content of current crontab is empty
         *      13 - content of crontab_template and current crontab not equal
         */

        protected function runTests()
        {

            parent::runTests();

            $this->emptyTemplate();
            if ( !empty($this->errors)) { // no sense to check more if crontab_template is empty
                return;
            }

            $status = $this->checkService('cron');
            if ($status === false) {
                $this->errors[] = 10;
            }
            $this->empty();
            $this->equal();

        }

        protected function fixError(int $error)
        {

            parent::fixError($error);

            switch ($error) {
                case 10: {
                    $this->restartService('cron');
                }
                    break;
                case 12:
                case 13: {
                    $this->copyFromTemplate();
                    $this->restartService('cron');
                }
                    break;
            }
        }

        /**
         * check if crontab_template not empty
         */
        private function emptyTemplate()
        {

            $error = 11;

            $command = 'cat /etc/sender4you/bash/crontab_template | wc --lines';
            $result = shell_exec($command) * 1;
            if ($result <= 0) {
                $this->errors[] = $error;
            }

        }

        /**
         * check if current crontab not empty and number of lines in template and current are equal
         */
        private function empty()
        {

            $error = 12;

            $command = 'echo "$(crontab -l)" | wc --lines';
            $result = shell_exec($command) * 1;
            if ($result <= 0) {
                $this->errors[] = $error;
            }

        }

        /**
         * Check if content of current crontab and crontab_template is equal
         */
        private function equal()
        {

            $error = 13;

            $command = 'if [ "$(cat /etc/sender4you/bash/crontab_template)" = "$(crontab -l)" ]; then echo "1"; else echo "0"; fi;';
            $result = shell_exec($command) * 1;
            if ($result <= 0) {
                $this->errors[] = $error;
            }

        }

        private function copyFromTemplate()
        {

            $command = 'crontab -r; crontab /etc/sender4you/bash/crontab_template;';
            shell_exec($command);

        }
    }