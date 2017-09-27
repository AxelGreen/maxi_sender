<?php

    namespace Sender4you\Sender;

    use Common\Converter;
    use Exception;

    /**
     * Class HeadersProcessor
     * Creates all headers for letter
     *
     * @package Sender4you\Sender
     */
    class HeadersProcessor
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

        private $headers
            = array(
                'message_id' => '',
                'hostname'   => '',
                'encoding'   => 'base64',
                'charset'    => 'UTF-8',
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

        public function reset()
        {

            $this->headers = array(
                'message_id' => '',
                'hostname'   => '',
                'encoding'   => 'base64',
                'charset'    => 'UTF-8',
            );

        }

        public function process(array $pool)
        {

            // message_id
            $this->headers['message_id'] = $this->messageId($pool);

            // hostname
            $this->headers['hostname'] = $pool['host'];

            return $this;

        }

        public function getHeaders()
        {

            return $this->headers;

        }

        private function messageId(array $pool)
        {

            $task_id = Converter::numberToString($pool['task_id']);
            $email_id = Converter::numberToString($pool['email_id']);
            $host = $pool['host'];
            $header = '<'.$task_id.'u'.$email_id.'@'.$host.'>';

            return $header;

        }

    }