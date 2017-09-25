<?php

    namespace Common;

    class Singleton
    {

        private static $instances = array();

        protected function __construct()
        {
        }

        protected function __clone()
        {
        }

        public function __wakeup()
        {
        }

        /**
         * @return $this
         */
        public static function getInstance()
        {

            $cls = get_called_class();
            if ( !isset(self::$instances[$cls])) {
                self::$instances[$cls] = new static;
            }

            return self::$instances[$cls];
        }

    }