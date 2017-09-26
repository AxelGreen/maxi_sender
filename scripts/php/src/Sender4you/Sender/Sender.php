<?php

    namespace Sender4you\Sender;

    use PHPMailer\PHPMailer\PHPMailer;

    class Sender
    {

        public static function send($pool, $letter, $headers)
        {

            $mailer = new PHPMailer();
            $mailer->isMail();

            // headers
            $mailer->MessageID = $headers['message_id'];
            $mailer->Hostname = $headers['hostname'];
            $mailer->Encoding = $headers['encoding'];
            $mailer->CharSet = $headers['charset'];
            // disable X-Mailer
            $mailer->XMailer = ' ';

            // content
            $mailer->Sender = $letter['sender_email'];
            $mailer->setFrom($letter['from_email'], $letter['from_name']);
            $mailer->isHTML(true);
            $mailer->Subject = $letter['subject'];
            $mailer->msgHTML($letter['html_body']);

            // add recipient
            $mailer->addAddress($pool['email'], $pool['data']['n']);

            // send
            return $mailer->send();

        }

    }