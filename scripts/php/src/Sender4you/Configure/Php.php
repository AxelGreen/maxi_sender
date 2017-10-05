<?php

    namespace Sender4you\Configure;

    use Common\Connection\MaxiApi;
    use PHPMailer\PHPMailer\Exception;

    class Php
    {

        public function run()
        {

            $id = $this->retrieveOwnerUserId();

            if (empty($id)) {
                throw new Exception(_('Can\'t retrieve owner user id'));
            }

            $this->setDistributorOwnerUserId($id);

        }

        private function retrieveOwnerUserId() : int
        {

            // get connection
            $api_connection = MaxiApi::getInstance();

            // make request
            $response = $api_connection->makeRequest('ownerId');

            return $response * 1;

        }

        private function setDistributorOwnerUserId(int $id)
        {

            $command = 'sed -i "s/^.*\$owner_user_id.*=.*$/\t\t\$owner_user_id = '.$id.';/" /etc/sender4you/distributor.php';
            //var_dump($command);
            shell_exec($command);

        }

    }