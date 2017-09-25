<?php

    namespace Sender4you\Sender;

    use Common\Connection\MaxiApi;
    use Exception;

    class TemplateBuffer
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
        private static $refresh_period = '6 hours';

        /**
         * @var array - templates cache - contains already loaded templates
         */
        private $templates = array();

        /**
         * TemplateBuffer constructor.
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
         * Get template from cache or from central server
         *
         * @param int $task_id - task to get template for
         *
         * @return bool|array|null - array contains template details or null if required task_id is empty or false if can't retrieve template
         */
        public function getTemplate(int $task_id)
        {

            // can't take template without task_id
            if (empty($task_id)) {
                return null;
            }

            // retrieve template from cache
            if ( !empty($this->templates[$task_id])) {
                return $this->templates[$task_id];
            }

            // retrieve template from central server
            $this->retrieveTemplate($task_id);

            // return false if we can't retrieve template
            if (empty($this->templates[$task_id])) {
                return false;
            }

            // return template from cache
            return $this->templates[$task_id];

        }

        /**
         * Retrieve template from central server via Maxi API and write it ot cache
         *
         * @param int $task_id
         */
        private function retrieveTemplate(int $task_id)
        {

            // get connection
            $api_connection = MaxiApi::getInstance();

            // make request
            $response = $api_connection->makeRequest('template',
                array(
                    'task_id' => $task_id
                ));

            // if we have some response - write it to cache
            if ( !empty($response)) {
                $this->templates[$task_id] = $response;
            }

        }

    }