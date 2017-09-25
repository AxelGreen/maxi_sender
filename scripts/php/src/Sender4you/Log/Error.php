<?php

    namespace Sender4you\Log;

    use Config\SenderConfig;
    use Exception;

    class Error
    {

        public static function push(Exception $exception)
        {

            // get sender settings
            $sender_settings = SenderConfig::getInstance();

            // filename from settings
            $filename = $sender_settings->logs['error'];

            // generate log entry
            $error = array(
                'date'    => date('Y-m-d H:i:s'),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'message' => '"'.$exception->getMessage().'"'
            );
            $error_message = implode(' ', $error).PHP_EOL;

            // push to log
            try {
                file_put_contents($filename, $error_message, FILE_APPEND);
            } catch (Exception $ex) {

            }

        }

    }