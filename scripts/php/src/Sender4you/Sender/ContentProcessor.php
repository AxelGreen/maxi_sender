<?php

    namespace Sender4you\Sender;

    use Common\Converter;
    use Exception;

    /**
     * Class ContentProcessor
     * Creates letter based on Poll and Template
     *
     * @package Sender4you\Sender
     */
    class ContentProcessor
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
         * @var array - contains fixed shortcodes with number of required parameters
         */
        private $fixed_shortcodes
            = array(
                'fix_int'  => 1,
                'fix_link' => 1,
                'fix_text' => 1,
                'fix_date' => 1,
            );

        /**
         * @var array - contains Letter information
         */
        private $letter
            = array(
                'from_name'    => '',
                'from_email'   => '',
                'sender_email' => '',
                'subject'      => '',
                'html_body'    => '',
                'plain_body'   => ''
            );

        /**
         * @var array contains Pool information
         */
        private $pool = array();

        /**
         * @var array contains Template information
         */
        private $template = array();

        /**
         * @var array contains list of domains to make redirects throw
         */
        private $redirect_domains = array();

        /**
         * @var array - contains replacement for all shortcodes. Randoms in shortcodes already processed
         */
        private $shortcodes
            = array();

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
         * reset inner state for processor
         */
        public function reset()
        {

            $this->letter = array(
                'from_name'    => '',
                'from_email'   => '',
                'sender_email' => '',
                'subject'      => '',
                'html_body'    => '',
                'plain_body'   => ''
            );

            $this->pool = array();
            $this->template = array();
            $this->redirect_domains = array();
            $this->shortcodes = array();
        }

        /**
         * Set inner data
         *
         * @param array $pool     - array which contains Pool information - all data about current letter
         * @param array $template - array which contains Template - all data for current Task
         * @param array $redirect_domains
         *
         * @return $this - return itself for chaining
         */
        public function set(array $pool, array $template, array $redirect_domains)
        {

            $this->pool = $pool;
            $template['shortcodes'] = json_decode($template['shortcodes'], true);
            $template['settings'] = json_decode($template['settings'], true);
            $this->template = $template;
            $this->redirect_domains = $redirect_domains;

            return $this;
        }

        /**
         * Retrieve Letter
         *
         * @return array - array with Letter information
         */
        public function getLetter()
        {

            return $this->letter;

        }

        /**
         * Creates Letter based on Pool and Template. Replace all shortcodes and randoms in template using information from Template.shortcodes and Pool
         */
        public function process()
        {

            $this->shortcodes = $this->prepareShortcodes($this->pool, $this->template);

            // from_name
            $from_name = $this->fromName($this->template['from_name']);
            $this->letter['from_name'] = $from_name;
            $this->shortcodes['from_name'] = $from_name;

            // from_email
            $from_email = $this->fromEmail($this->template['from_email']);
            if ($this->validateEmail($from_email) === false) {
                $from_email = $this->nameToEmail($from_name, $this->pool['host']);
            }
            $this->letter['from_email'] = $from_email;
            $this->shortcodes['from_email'] = $from_email;

            // sender_email
            $sender_email = $this->senderEmail($this->template['sender_email']);
            if ($this->validateEmail($sender_email) === false) {
                $sender_email = $this->nameToEmail($from_name, $this->pool['host']);
            }
            $this->letter['sender_email'] = $sender_email;

            // subject
            $subject = $this->subject($this->template['subject']);
            $this->letter['subject'] = $subject;
            $this->shortcodes['letter_subject'] = $subject;

            // pixel
            $pixel = $this->pixel();
            $this->shortcodes['letter_pixel'] = $pixel;

            // html_body, plain_body
            // TODO: remove plan_body if PHPMailer create it ok by itself
            $body = $this->body($this->template['body']);
            $this->letter['html_body'] = $body['html_body'];
            $this->letter['plain_body'] = $body['plain_body'];

            return true;

        }

        /**
         * @param string $email
         *
         * @return bool
         */
        private function validateEmail(string $email)
        {

            preg_match('>^(?:[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])>',
                $email,
                $rez
            );

            return !empty($rez[0]);

        }

        /**
         * Prepare all default shortcodes (except from_name and from_email - will be ready after fromName() and fromEmail called and except fix_int - it processed each time) and Template specific
         * shortcodes. Processing randoms for shortcodes.
         *
         * @param $pool
         * @param $template
         *
         * @return array - list of shortcodes (shortcode => replacement)
         */
        private function prepareShortcodes(array $pool, array $template)
        {

            $shortcodes = array();

            // Email based
            // name
            if ( !empty($pool['data']['n'])) {
                $shortcodes['client_name'] = $pool['data']['n'];
            }

            // email
            $shortcodes['client_email'] = $pool['email'];

            // city
            if ( !empty($pool['data']['c'])) {
                $shortcodes['client_city'] = $pool['data']['c'];
            }

            // phone
            if ( !empty($pool['data']['p'])) {
                $shortcodes['client_phone'] = $pool['data']['p'];
            }

            if ( !empty($template['shortcodes'])) {
                foreach ($template['shortcodes'] as $shortcode => $values) {
                    // no values for this shortcode
                    if (empty($values)) {
                        continue;
                    }

                    // don't rewrite previously set shortcode value - protect default shortcodes
                    if (isset($shortcodes[$shortcode])) {
                        continue;
                    }

                    $shortcodes[$shortcode] = $values[array_rand($values)];
                }
            }

            return $shortcodes;

        }

        /**
         * Replace shortcodes which saved in $this->shortcodes. Process shortcodes from $this->fixed_shortcodes. If shortcode not found in shortcodes - don't changed (for processing as random)
         *
         * @param string $target - string to replace shortcodes in
         *
         * @param array  $shortcodes
         * @param array  $fixed_shortcodes
         *
         * @return string - $target with replaced shortcodes
         */
        private function replaceShortcodes(string $target, array $shortcodes, array $fixed_shortcodes)
        {

            $target = preg_replace('%https?://({fix_link:\d+})%', '$1', $target);

            $pool = $this->pool;
            $redirect_domains = $this->redirect_domains;
            $settings = $this->template['settings'];

            $replaced = preg_replace_callback('%{([^|{}]*)}%',
                function ($matches) use ($shortcodes, $fixed_shortcodes, $pool, $redirect_domains, $settings) {

                    if (preg_match('%('.implode('|', array_keys($fixed_shortcodes)).')(?::\d+){1,}%', $matches[1], $rez)) {
                        $shortcode_params = explode(':', $rez[0]);
                        if (count($shortcode_params) - 1 < $fixed_shortcodes[$shortcode_params[0]]) {
                            return $matches[0];
                        }

                        switch ($shortcode_params[0]) {
                            case 'fix_int': {

                                if (count($shortcode_params) - 1 == 2) { // range

                                    return rand($shortcode_params[1], $shortcode_params[2]);

                                } else { // length

                                    $number = (string)rand(1, 9);
                                    for ($i = 0; $i < $shortcode_params[1] - 1; $i ++) {
                                        $number .= rand(0, 9);
                                    }

                                    return $number;

                                }

                            }
                                break;
                            case 'fix_link': { // replace link shortcode
                                $redirect_domain = $redirect_domains[array_rand($redirect_domains)];
                                $subdomain_params = array(
                                    'task_id'  => Converter::numberToString($pool['task_id']),
                                    'email_id' => Converter::numberToString($pool['email_id']),
                                    'link_id'  => Converter::numberToString((int)$shortcode_params[1])
                                );
                                $subdomain = implode('u', $subdomain_params);

                                $redirect_variant = $settings['r'][array_rand($settings['r'])];

                                $redirect_prefixes = array(
                                    1 => 'http://',
                                    2 => 'https://www.google.com/search?btnI&q=site:'
                                    // analogs for https://www.google.com - alias
                                    //http://3H6k7lIAiqjfNeN@0xd8.0x3a.0xd6.0xce
                                    //http://0xd83ad6ce
                                    //http://3231223831
                                    //http:00330.00072.0000326.00000316
                                );

                                $redirect_link = $redirect_prefixes[$redirect_variant].$subdomain.'.'.$redirect_domain;

                                return $redirect_link;

                            }
                                break;
                            case 'fix_text': {
                                $formats = array(
                                    0 => 'russian',
                                    1 => 'english'
                                );
                                $current_format = $formats[$shortcode_params[1] % count($formats)];
                                $input_file = '/etc/sender4you/assets/random_text_'.$current_format;

                                $paragraphs_count = 5;
                                $command = 'shuf -n '.$paragraphs_count.' '.$input_file;
                                $lines = shell_exec($command);
                                $lines = explode(PHP_EOL, $lines);

                                // shuffle parts of string
                                if (isset($shortcode_params[2])) {

                                    $shuffle_delimiters = array(
                                        0 => '.', // shuffle sentences
                                        1 => ' ' // shuffle words
                                    );

                                    $current_delimiter = $shuffle_delimiters[$shortcode_params[2] % count($shuffle_delimiters)];
                                    $lines = array_map(function ($line) use ($current_delimiter) {

                                        $line_parts = explode($current_delimiter, $line);
                                        shuffle($line_parts);
                                        $line = implode($current_delimiter, $line_parts);

                                        return $line;

                                    },
                                        $lines);

                                }

                                $lines = implode('<br>'.PHP_EOL, $lines);

                                return $lines;

                            }
                                break;
                            case 'fix_date': {
                                $formats = array(
                                    0  => 'd-m-Y',
                                    1  => 'd-m-y',
                                    2  => 'd/m/Y',
                                    3  => 'd/m/y',
                                    4  => 'm-d-Y',
                                    5  => 'm-d-y',
                                    6  => 'm/d/Y',
                                    7  => 'm/d/y',
                                    8  => 'Y-m-d',
                                    9  => 'y-m-d',
                                    10 => 'Y/m/d',
                                    11 => 'y/m/d'
                                );
                                $current_format = $formats[$shortcode_params[1] % count($formats)];

                                return date($current_format);

                            }
                                break;
                        }

                    }

                    if ( !isset($shortcodes[$matches[1]])) {
                        return $matches[0];
                    }

                    return $shortcodes[$matches[1]];

                },
                $target);

            return $replaced;
        }

        /**
         * Replace randoms with one of their values
         *
         * @param string $target - string to replace randoms in
         *
         * @return string - $target with replaced randoms
         */
        private function replaceRandoms(string $target)
        {

            $replaced = preg_replace_callback('%{([^{}]*)}%',
                function ($matches) {

                    $variants = explode('|', $matches[1]);

                    return $variants[array_rand($variants)];
                },
                $target);

            return $replaced;
        }

        /**
         * @param string $original
         *
         * @return string
         */
        private function transliterate(string $original)
        {

            $transliterated = strtr($original,
                array(
                    'а' => 'a',
                    'б' => 'b',
                    'в' => 'v',
                    'г' => 'g',
                    'д' => 'd',
                    'е' => 'e',
                    'ё' => 'yo',
                    'ж' => 'j',
                    'з' => 'z',
                    'и' => 'i',
                    'й' => 'y',
                    'к' => 'k',
                    'л' => 'l',
                    'м' => 'm',
                    'н' => 'n',
                    'о' => 'o',
                    'п' => 'p',
                    'р' => 'r',
                    'с' => 's',
                    'т' => 't',
                    'у' => 'u',
                    'ф' => 'f',
                    'х' => 'h',
                    'ц' => 'c',
                    'ч' => 'ch',
                    'ш' => 'sh',
                    'щ' => 'sch',
                    'ъ' => 'y',
                    'ы' => 'y',
                    'ь' => '',
                    'э' => 'e',
                    'ю' => 'yu',
                    'я' => 'ya',
                ));

            return $transliterated;
        }

        /**
         * @param string $name
         * @param string $host
         *
         * @return string
         */
        private function nameToEmail(string $name, string $host)
        {

            $name = mb_strtolower($name);
            $name = $this->transliterate($name);
            $space_replacements = array('.', '_', '');
            $rand = array_rand($space_replacements);
            $name = str_replace(' ', $space_replacements[$rand], $name);
            $name = preg_replace('%[^a-z0-9._]*%', '', $name);
            $email = $name.rand(10, 99).'@'.$host;

            return $email;
        }

        /**
         * Prepare from_name
         *
         * @param string $name
         *
         * @return string
         */
        private function fromName(string $name)
        {

            $name = $this->replaceShortcodes($name, $this->shortcodes, $this->fixed_shortcodes);
            $name = $this->replaceRandoms($name);

            return $name;

        }

        /**
         * @param string $email
         *
         * @return string
         */
        private function fromEmail(string $email)
        {

            if (empty($email)) { // generate based on from_name and Pool.host

                return $this->nameToEmail($this->letter['from_name'], $this->pool['host']);

            }

            $email = $this->replaceShortcodes($email, $this->shortcodes, $this->fixed_shortcodes);
            $email = $this->replaceRandoms($email);

            // only first part of email provided
            if (substr_count($email, '@') === 0) {
                return $email.'@'.$this->pool['host'];
            }

            return $email;

        }

        /**
         * @param string $email
         *
         * @return string
         */
        private function senderEmail(string $email)
        {

            if (empty($email)) {
                $from_email_parts = explode('@', $this->letter['from_email']);

                return $from_email_parts[0].'@'.$this->pool['host'];
            }

            $email = $this->replaceShortcodes($email, $this->shortcodes, $this->fixed_shortcodes);
            $email = $this->replaceRandoms($email);

            $email_parts = explode('@', $email);

            return $email_parts[0].'@'.$this->pool['host'];

        }

        /**
         * @param string $subject
         *
         * @return string
         */
        private function subject(string $subject)
        {

            $subject = $this->replaceShortcodes($subject, $this->shortcodes, $this->fixed_shortcodes);
            $subject = $this->replaceRandoms($subject);

            return $subject;

        }

        /**
         * @return string
         */
        private function pixel()
        {

            if ( !empty($this->template['settings']['p'])) {
                return '';
            }

            $redirect_domain = $this->redirect_domains[array_rand($this->redirect_domains)];
            $subdomain_params = array(
                'task_id'  => Converter::numberToString($this->pool['task_id']),
                'email_id' => Converter::numberToString($this->pool['email_id'])
            );
            $subdomain = implode('u', $subdomain_params);
            $image_name = substr(sha1(time()), 0, 10).'.gif';
            $url = 'http://'.$subdomain.'.'.$redirect_domain.'/'.$image_name;
            $pixel = '<div style="color:#ffffff; background-color:#ffffff; font-size:1px;"><img alt="'.$image_name.'" src="'.$url.'" style="height:1px;width:1px;"></div>';

            return $pixel;

        }

        /**
         * @param string $body
         *
         * @return string
         */
        private function wrapBody(string $body)
        {

            $html
                = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0"/><title>
{letter_subject}
</title></head><body>'
                  .$body.'{letter_pixel}</body></html>';

            return $html;
        }

        /**
         * @param string $body
         *
         * @return array
         */
        private function body(string $body)
        {

            $body = $this->wrapBody($body);
            $body = $this->replaceShortcodes($body, $this->shortcodes, $this->fixed_shortcodes);
            $body = $this->replaceRandoms($body);

            $return = array(
                'html_body'  => $body,
                'plain_body' => strip_tags($body)
            );

            return $return;
        }

    }
