<?php

    namespace Sender4you;

    use Common\Connection\MaxiApi;
    use Exception;

    /**
     * Class SettingsBuffer
     * Gets from Maxi API information about this Vps or RedirectServer
     *
     * @package Sender4you\Sender
     */
    class SettingsBuffer
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
         * @var array - holds different settings for this Vps and User
         */
        private $settings
            = array(
                'ips'              => array(),
                'redirect_domains' => array()
            );

        /**
         * ContentProcessor constructor.
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
         * Get Ips from cache or from central server
         *
         * @return bool|array - array with Ips or false if can't retrieve
         */
        public function getIps()
        {

            // check if Ips already cached
            if ( !empty($this->settings['ips'])) {
                return $this->settings['ips'];
            }

            // retrieve Ips from Maxi API and write to cache
            $this->retrieveIps();

            // return false if we can't retrieve Ips
            if (empty($this->settings['ips'])) {
                return false;
            }

            // return from cache
            return $this->settings['ips'];

        }

        // TODO: complete retrieveIps

        /**
         * Retrieve Ips from Maxi API
         */
        private function retrieveIps()
        {


        }

        public function getRedirectDomains()
        {

            // check if Ips already cached
            if ( !empty($this->settings['redirect_domains'])) {
                return $this->settings['redirect_domains'];
            }

            // retrieve Ips from Maxi API and write to cache
            $this->retrieveRedirectDomains();

            // return false if we can't retrieve Ips
            if (empty($this->settings['redirect_domains'])) {
                return false;
            }

            // return from cache
            return $this->settings['redirect_domains'];

        }

        private function retrieveRedirectDomains()
        {

            // get connection
            $api_connection = MaxiApi::getInstance();

            // make request
            $response = $api_connection->makeRequest('redirectDomains');

            // if we have some response - write it to cache
            if ( !empty($response)) {
                $this->settings['redirect_domains'] = $response;
            }

        }

    }