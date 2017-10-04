<?php

    use Common\Connection\RabbitConnection;
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
    $end_time = strtotime($sender_settings->time_to_live) + (rand(1, 60) * 60);

    // connect to Rabbit
    $rabbit_connection = RabbitConnection::getInstance($sender_settings->local_rabbit['connection_name'])->getConnection();
    $rabbit_channel = $rabbit_connection->channel();

    // declare queue
    $rabbit_channel->queue_declare($sender_settings->local_rabbit['queue_name'], false, true, false, false);

    // callback to process messages
    $callback = function (AMQPMessage $message) {

        // decode message
        $pool = json_decode($message->body, true);

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
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);

    };

    // process messages one by one. Allows get new task already after this is completed (disable Fair dispatch)
    $rabbit_channel->basic_qos(null, 1, null);

    // consume to queue
    $rabbit_channel->basic_consume($sender_settings->local_rabbit['queue_name'], '', false, false, false, false, $callback);

    // listen for new messages
    //var_dump($rabbit_channel->callbacks);
    //
    //return;
    while (count($rabbit_channel->callbacks)) {
        $rabbit_channel->wait(null, true);
        echo 1;
        if (time() > $end_time) {
            break;
        }
    }

    // close channel and connection
    $rabbit_channel->close();
    $rabbit_connection->close();

    // write log which shows that process stops
    Info::push(_('Sender stop').' ('.$sender_hash.')');

    // stop execution
    die();
