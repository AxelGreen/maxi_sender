<?php

    use Bunny\Channel;
    use Bunny\Client;
    use Bunny\Message;
    use Common\Connection\BunnyConnection;
    use Config\SenderConfig;
    use PhpAmqpLib\Message\AMQPMessage;
    use Sender4you\Log\Error;
    use Sender4you\Log\Info;
    use Sender4you\Sender\ContentProcessor;
    use Sender4you\Sender\HeadersProcessor;
    use Sender4you\Sender\Sender;
    use Sender4you\Sender\TemplateBuffer;
    use Sender4you\SettingsBuffer;

    require_once __DIR__.'/vendor/autoload.php';

    // unique identifier for this process.
    $sender_hash = sha1(microtime(true).rand());

    // write log which shows that process starts
    Info::push(_('Sender start').' ('.$sender_hash.')');

    // sender config
    $sender_settings = SenderConfig::getInstance();

    // timestamp when script must stop processing and exit. One last message will be processed until exit
    $time_limit = $sender_settings->time_to_live + (rand(1, 60) * 60);

    // connect to Bunny
    $bunny_connection = BunnyConnection::getInstance($sender_settings->local_bunny['connection_name'])->getConnection();
    $bunny_connection->connect();
    $bunny_channel = $bunny_connection->channel();

    // declare queue
    $bunny_channel->queueDeclare($sender_settings->local_bunny['queue_name'], false, true, false, false);

    // callback to process messages
    $callback = function (Message $message, Channel $channel) {

        // decode message
        $pool = json_decode($message->content, true);

        try {

            // check required params
            if (empty($pool['t']) || empty($pool['e']) || empty($pool['i']) || empty($pool['h'])) {
                throw new Exception(_('Some of Pool params is empty'));
            }
            $pool = array(
                'task_id'  => $pool['t'],
                'email'    => $pool['e'],
                'email_id' => $pool['i'],
                'data'     => $pool['d'],
                'host'     => $pool['h']
            );

            // get redirect domains
            $settings_buffer = SettingsBuffer::getInstance();
            $redirect_domains = $settings_buffer->getRedirectDomains();

            // get Template
            $templates_buffer = TemplateBuffer::getInstance();
            $template = $templates_buffer->getTemplate($pool['task_id']);
            if ($template === false) {
                throw new Exception(_('Can\'t retrieve TaskTemplate').' (task_id='.$pool['task_id'].')');
            }

            // process content
            $content_processor = ContentProcessor::getInstance();
            $process_status = $content_processor->set($pool, $template, $redirect_domains)->process();
            if ($process_status === false) {
                throw new Exception(_('Can\'t generate Letter').' (task_id='.$pool['task_id'].'; email_id='.$pool['email_id'].')');
            }
            $letter = $content_processor->getLetter();
            $content_processor->reset();

            // process headers
            $headers_processor = HeadersProcessor::getInstance();
            $headers = $headers_processor->process($pool)->getHeaders();
            $headers_processor->reset();

            // send Letter
            if (Sender::send($pool, $letter, $headers) === false) {
                throw new Exception(_('Can\'t send Letter').' (task_id='.$pool['task_id'].'; email_id='.$pool['email_id'].')');
            }

        } catch (Exception $ex) {

            // write error log
            Error::push($ex);

        }

        // acknowledge message processing
        $channel->ack($message);

    };

    // process messages one by one. Allows get new task already after this is completed (disable Fair dispatch)
    $bunny_channel->qos(0, 1, null);

    // consume to queue
    $bunny_channel->consume($callback, $sender_settings->local_bunny['queue_name'], '', false, false, false, false);

    // listen for new messages
    $bunny_connection->run($time_limit);

    // close channel and connection
    $bunny_channel->close();
    $bunny_connection->disconnect();

    // write log which shows that process stops
    Info::push(_('Sender stop').' ('.$sender_hash.')');

    // stop execution
    die();
