<?php

    namespace Sender4you\Distributor;

    use Common\Connection\MaxiApi;
    use Exception;

    class HostBuffer
    {

        /**
         * @var null|self - instance of this class, used to implement Singleton pattern
         */
        private static $instance = null;

        /**
         * @var int - timestamp when this connection expires - instance will be recreated when expires
         */
        private static $expires;

        /**
         * @var string - period of time to wait while recreate instance
         */
        private static $refresh_period = '1 hours';

        /**
         * @var array - hosts cache - contains already loaded data
         */
        private $hosts = array();

        /**
         * HostBuffer constructor.
         */
        private function __construct()
        {
        }

        private function __clone()
        {
        }

        public function __wakeup()
        {

            throw new Exception('Cannot unserialize singleton');
        }

        /**
         * Method to retrieve instance of this class
         *
         * @return null|self
         */
        public static function getInstance()
        {

            if (self::$expires < time()) {
                self::$expires = strtotime(self::$refresh_period);
                self::$instance = new static();
            }

            return self::$instance;
        }

        /**
         * Get hosts from cache or from central server
         *
         * @return bool|array|null - array contains hosts which connected to this VPS
         */
        public function getHosts()
        {

            // retrieve hosts from cache
            if ( !empty($this->hosts)) {
                return $this->hosts;
            }

            // retrieve hosts from central server
            $this->retrieveHosts();

            // return false if we can't retrieve hosts
            if (empty($this->hosts)) {
                return false;
            }

            // return hosts from cache
            return $this->hosts;

        }

        /**
         * Retrieve hosts from central server via Maxi API and write it ot cache
         */
        private function retrieveHosts()
        {

            // get connection
            $api_connection = MaxiApi::getInstance();

            // make request
            $response = $api_connection->makeRequest('hosts');

            // if we have some response - write it to cache
            if ( !empty($response)) {
                $this->hosts = $response;
            }

        }

    }