<?php

    namespace Sender4you\Diagnostics;

    class Exim extends Tester
    {

        /**
         * Error codes
         *      20 - exim process not active
         *      21 - domains file not exist
         *      22 - DKIM files not exist
         *      23 - number of domains and DKIMs not equal
         */

        /**
         * @var \Sender4you\Configure\Exim
         */
        private $exim_config;

        public function setEximConfig(\Sender4you\Configure\Exim $exim_config)
        {

            $this->exim_config = $exim_config;
        }

        protected function runTests()
        {

            parent::runTests();

            $status = $this->checkService('exim4');
            if ($status === false) {
                $this->errors[] = 20;
            }

            $domains_number = $this->checkDomainsFile($this->exim_config->getDomainsFile());
            $dkims_number = $this->checkDkimFiles($this->exim_config->getDkimFilesFolder());

            if ($domains_number != $dkims_number) {
                $this->errors[] = 23;
            }

        }

        protected function fixError(int $error)
        {

            parent::fixError($error);

            switch ($error) {
                case 20: {
                    $this->restartService('exim4');
                }
                    break;
                case 21:
                case 22:
                case 23: {
                    $this->repeatConfiguration();
                    $this->restartService('exim4');
                }
                    break;
            }
        }

        private function checkDomainsFile(string $domains_file) : int
        {

            $error = 21;
            $command = 'cat '.$domains_file.' 2>/dev/null | sed -n -r "s/^[^*]+.*/1/p" | wc --lines';
            $result = shell_exec($command) * 1;
            if ($result <= 0) {
                $this->errors[] = $error;
            }

            return $result;

        }

        /**
         * Check if dkim files exist and has right permissions
         *
         * @param string $dkim_files_folder
         *
         * @return int
         */
        private function checkDkimFiles(string $dkim_files_folder) : int
        {

            $error = 22;
            $command = 'ls -l '.$dkim_files_folder.'*.pem 2>/dev/null | sed -n -r "s/^-rw-r-----.*Debian-exim Debian-exim.*/1/p" | wc --lines';
            $result = shell_exec($command) * 1;
            if ($result <= 0) {
                $this->errors[] = $error;
            }

            return $result;

        }

        private function repeatConfiguration()
        {

            $this->exim_config->run();

        }

    }