<?php

    namespace Sender4you\Log;

    use Config\SenderConfig;
    use Exception;

    class Info
    {

        public static function push(string $text)
        {

            // get sender settings
            $sender_settings = SenderConfig::getInstance();

            // filename from settings
            $filename = $sender_settings->logs['info'];

            // generate log entry
            $entry = array(
                'date'    => date('Y-m-d H:i:s'),
                'message' => '"'.$text.'"'
            );
            $entry_message = implode(' ', $entry).PHP_EOL;

            // push to log
            try {
                file_put_contents($filename, $entry_message, FILE_APPEND);
            } catch (Exception $ex) {

            }

        }

    }