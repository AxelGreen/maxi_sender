<?php
    /**
     * Created by PhpStorm.
     * User: axel
     * Date: 10/27/17
     * Time: 1:00 PM
     */

    namespace Sender4you\Pause;

    use Common\Connection\MemcachedConnect;
    use Common\Connection\PgConnection;
    use Config\SenderConfig;

    /**
     * Class Helper
     * helper functions for pause processing
     *
     * @package Sender4you\Pause
     */
    class Helper
    {

        public function retrieveHost(string $exim_id) : string
        {

            $sending_host = '';

            if (preg_match('%.{6}-.{6}-.{2}$%', $exim_id) === false) { // wrong exim id
                return $sending_host;
            }

            // retrieve this message from DB
            $query
                = '
                SELECT host FROM public.exim_logs
                  WHERE exim_id = $1
            ';
            $pg_conn = PgConnection::getInstance();
            $log = $pg_conn->query($query,
                array(
                    'exim_id' => $exim_id
                ));
            $sending_host = $log[0]['host'];

            return $sending_host;

        }

        public function parseBounce(string $bounce) : string
        {

            $email_domain = '';

            if (empty($bounce)) { // no message supplied
                return $email_domain;
            }

            $email = explode(' ', $bounce, 2);
            $email = $email[0];

            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) { // email not found
                return $email_domain;
            }
            $email_domain = explode('@', $email);
            $email_domain = $email_domain[1];

            return $email_domain;

        }

        public function detectBigId(string $email_domain, array $bounce_limits) : int
        {

            $big = 0;
            foreach ($bounce_limits as $big_id => $big_data) {

                if ($big_id <= 0) { // no pauses for not determined Bigs or tests
                    continue;
                }

                if (preg_match($big_data['pattern'], $email_domain)) {
                    $big = $big_id;
                }

            }

            return $big;

        }

        public function getPreviousBounces(string $sending_host, int $big_id) : array
        {

            $previous = array();

            // get previous bounces for this sending_host and Big
            $settings = SenderConfig::getInstance();
            $memcached = MemcachedConnect::getInstance();

            $cache_key = $sending_host.'|||'.$big_id.'|||'.$settings->pause_params['previous'];
            $previous_bounces = $memcached->get($cache_key);
            if (empty($previous_bounces)) {
                return $previous;
            }
            $previous = json_decode($previous_bounces, true);

            return $previous;

        }

        public function filterPreviousBounces(array $previous_bounces) : array
        {

            $settings = SenderConfig::getInstance();

            foreach ($previous_bounces as $key => $time) {

                if ($time < time() - $settings->pause_params['expiration']) {
                    unset($previous_bounces[$key]);
                }

            }
            $previous_bounces = array_values($previous_bounces);

            return $previous_bounces;

        }

        public function savePreviousBounces(string $sending_host, int $big_id, array $previous_bounces)
        {

            // get previous bounces for this sending_host and Big
            $settings = SenderConfig::getInstance();
            $memcached = MemcachedConnect::getInstance();

            $cache_key = $sending_host.'|||'.$big_id.'|||'.$settings->pause_params['previous'];

            $memcached->set($cache_key, $previous_bounces);

        }

        public function checkBouncesLimit()
        {

        }

    }