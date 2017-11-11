<?php

    namespace Common\Connection;

    use Config\ConnectionConfig;
    use Exception;

    /**
     * Class MaxiApi
     * Used to make request to Sender4You Maxi Api
     * Implements Singleton pattern with additional mechanism to refresh connection after $refresh_period
     *
     * @package Common\Connection
     */
    class MaxiApi extends Connection
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
        private static $refresh_period = '1 hour';

        /**
         * @var array|null - configuration information to connect to Maxi api
         */
        private $config = null;

        /**
         * MaxiApi constructor.
         * Prepares curl connection and set basic option for this connection
         */
        private function __construct()
        {

            // get connection information
            $this->config = ConnectionConfig::getInstance()->sender4you_api;

            // create curl handler
            $ch = curl_init();

            // save it to class
            $this->connection = $ch;

            // set basic properties for all requests
            curl_setopt($this->connection, CURLOPT_POST, true);
            curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, true);

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

            // check if instance expired already
            if (self::$expires < time()) { // expired

                // if instance already exist - need to close curl connection handler before create new instance
                if (isset(self::$instance)) {
                    self::$instance->closeConnection();
                }

                // set new expiration time
                self::$expires = strtotime(self::$refresh_period);

                // create new instance
                self::$instance = new static();
            }

            // return instance
            return self::$instance;
        }

        /**
         * Retrieve curl handler for manual request
         *
         * @return resource - curl handler
         */
        public function getConnection()
        {

            return $this->connection;
        }

        /**
         * call curl_close for current connection. Used before creating new instance
         */
        private function closeConnection()
        {

            curl_close($this->connection);
        }

        /**
         * @param string $endpoint    - one of endpoints listed in configuration
         * @param array  $post_params - POST params to send with request
         *
         * @return array|false|null - response from Api decoded to array containing template, or false if $endpoint not found or null if response from Api was empty
         */
        public function makeRequest(string $endpoint, array $post_params = array())
        {

            // exit if such endpoint not set
            if ( !isset($this->config['endpoints'][$endpoint])) {
                return false;
            }

            // get url for this endpoint
            $endpoint_url = $this->config['host'].$this->config['version_prefix'].$this->config['endpoints'][$endpoint];
            curl_setopt($this->connection, CURLOPT_URL, $endpoint_url);

            // add post params
            if ( !empty($post_params)) {
                curl_setopt($this->connection, CURLOPT_POSTFIELDS, $post_params);
            } else { // clear post if something left from previous request
                curl_setopt($this->connection, CURLOPT_POSTFIELDS, array());
            }

            // make request
            $response = curl_exec($this->connection);

            // decode response
            $response = json_decode($response, true);

            if (empty($response)) {
                return null;
            }

            return $response;

        }
    }