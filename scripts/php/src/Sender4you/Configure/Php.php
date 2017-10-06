<?php

    namespace Sender4you\Configure;

    use Common\Connection\MaxiApi;
    use PHPMailer\PHPMailer\Exception;

    class Php
    {

        public function run()
        {

            $vps = $this->retrieveVps();

            if (empty($vps)) {
                throw new Exception(_('Can\'t retrieve owner user id'));
            }

            $this->setDistributorOwnerUserId($vps['user_id']);

            $this->changePoolState($vps['use']);

        }

        private function retrieveVps() : array
        {

            // get connection
            $api_connection = MaxiApi::getInstance();

            // make request
            $response = $api_connection->makeRequest('vps');

            return $response;

        }

        private function setDistributorOwnerUserId(int $id)
        {

            $command = 'sed -i "s/^.*\$owner_user_id.*=.*$/\t\t\$owner_user_id = '.$id.';/" /etc/sender4you/distributor.php';
            shell_exec($command);

        }

        private function changePoolState($state)
        {

            $command = '/etc/sender4you/bash/state_in_pool.sh';
            if ($state === 't') {
                $command .= ' 1';
            }

            shell_exec($command);

        }

    }