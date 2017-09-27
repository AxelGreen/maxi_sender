<?php

    namespace Sender4you\Configure;

    use Common\Connection\MaxiApi;

    class Exim
    {

        public $domains
            = array();

        private $domains_file = '/etc/exim4/domains.virtual';

        private $dkim_files_folder = '/etc/exim4/dkim/';

        public function run()
        {

            // get domains from central server and save to class
            $this->domains = $this->retrieveDomains();

            // create domains file
            $this->createDomainsFile($this->domains);

            // create dkim files
            $this->createDkimFiles($this->domains);

        }

        private function retrieveDomains()
        {

            // get connection
            $api_connection = MaxiApi::getInstance();

            // make request
            $response = $api_connection->makeRequest('ips');

            return $response;

        }

        /**
         * Create /etc/exim4/domains.virtual file and change it permissions
         *
         * @param array $domains
         */
        private function createDomainsFile(array $domains)
        {

            // remove old file
            shell_exec('rm -rf '.$this->domains_file);

            // generate lines for file
            $lines = array();
            foreach ($domains as $value) {

                $lines[] = $value['domain'];
                $lines[] = '*.'.$value['domain'];

            }
            $content = implode(PHP_EOL, $lines);

            // write to file
            file_put_contents($this->domains_file, $content);

            // change owner of this file
            shell_exec('chown Debian-exim:Debian-exim '.$this->domains_file);
            // change permissions of this file
            shell_exec('chmod +r '.$this->domains_file);

        }

        private function createDkimFiles($domains)
        {

            // remove old files
            shell_exec('rm -rf '.$this->dkim_files_folder);

            // create folder
            shell_exec('mkdir '.$this->dkim_files_folder);

            // create files for each domain
            foreach ($domains as $value) {
                file_put_contents($this->dkim_files_folder.$value['domain'].'.pem', $value['dkim']['private']);
            }

            // change owner for all files
            shell_exec('chown -R Debian-exim:Debian-exim '.$this->dkim_files_folder);
            // change permissions for all files
            shell_exec('chmod 640 '.$this->dkim_files_folder.'*');

        }

        /**
         * restart exim service
         */
        public function restart()
        {

            shell_exec('/etc/init.d/exim4 restart');

        }

    }