<?php

    namespace Sender4you\Distributor;

    use Common\Connection\MaxiApi;
    use Common\Connection\MemcachedConnect;
    use Config\SenderConfig;
    use Exception;

    class BigBuffer
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
         * @var array - bigs cache - contains already loaded data
         */
        private $bigs = array();

        /**
         * BigBuffer constructor.
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
         * Get bigs from cache or from central server
         *
         * @return bool|array|null - array contains big_ids as keys and speed limits as values
         */
        public function getBigs()
        {

            // retrieve template from cache
            if ( !empty($this->bigs)) {
                return $this->bigs;
            }

            // retrieve bigs from central server
            $this->retrieveBigs();

            // return false if we can't retrieve template
            if (empty($this->bigs)) {
                return false;
            }

            // return bigs from cache
            return $this->bigs;

        }

        /**
         * Retrieve bigs from central server via Maxi API and write it ot cache
         */
        private function retrieveBigs()
        {

            // get connection
            $api_connection = MaxiApi::getInstance();

            // make request
            $response = $api_connection->makeRequest('bigs');

            // if we have some response - write it to cache
            if ( !empty($response)) {
                $this->bigs = $response;
            }

        }

        public function getBounceLimits()
        {

            $settings = SenderConfig::getInstance();
            $memcached = MemcachedConnect::getInstance();

            // try to get them from Memcache
            $limits = $memcached->get($settings->memcached_bounce_limit['param']);
            $limits = json_decode($limits, true);

            if ( !empty($limits)) { // we have information in Memcache, so we don't need to get it from central server
                return $limits;
            }

            // data is not set already or expired, retrieve new information from central server
            // get connection
            $api_connection = MaxiApi::getInstance();

            // make request
            $response = $api_connection->makeRequest('bounceLimits');

            if (empty($response)) {
                return array(); // we have no information, return empty array
            }

            // update memcache
            $memcached->set($settings->memcached_bounce_limit['param'], $response, $settings->memcached_bounce_limit['expiration']);

            return $response;

        }

    }