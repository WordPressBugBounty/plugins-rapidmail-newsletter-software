<?php

    namespace Rapidmail\Api;

    use Rapidmail\Rapidmail;

    /**
     * APIv3 adapter class
     */
    class Apiv3 implements AdapterInterface {

        /**
         * @var string
         */
        const API_GET_RL_RESOURCE = '/v1/recipientlists/%u';

        /**
         * @var string
         */
        const API_GET_RLS_RESOURCE = '/v1/recipientlists';

        /**
         * @var string
         */
        const API_CREATE_RCPT_RESOURCE = '/v1/recipients';

        /**
         * @var string
         */
        const API_GET_FORM_FIELDS_RESOURCE = '/v1/forms/%u-default';

        /**
         * @var string
         */
        private $username;

        /**
         * @var string
         */
        private $password;

        /**
         * @var int
         */
        private $recipientlistId;

        /**
         * @var bool
         */
        private $isAuthenticated = false;

        /**
         * Constructor
         *
         * @param string $username
         * @param string $password
         * @param int $recipientlistId
         */
        public function __construct($username, $password, $recipientlistId) {

            $this->username = $username;
            $this->password = $password;
            $this->recipientlistId = $recipientlistId;

        }

        /**
         * @inheritdoc
         */
        public function isAuthenticated() {

            if ($this->isAuthenticated) {
                return true;
            }

            $response = $this->request($this->url(self::API_GET_RLS_RESOURCE));

            if (\is_wp_error($response)) {
                return false;
            }

            $response = $response['http_response'];
            $statusCode = $response->get_status();
            return $this->isAuthenticated = $statusCode >= 200 && $statusCode < 300;

        }

        /**
         * @inheritdoc
         */
        public function isConfigured() {
            return !empty($this->username) && !empty($this->password);
        }

        /**
         * Trigger wp_remote_get call
         *
         * @param string $url
         * @param array $args
         * @return \WP_HTTP_Response|\WP_Error
         */
        private function request($url, array $args = []) {

            if (!isset($args['headers'])) {
                $args['headers'] = [];
            }

            $args['headers']['Authorization'] = 'Basic ' . \base64_encode($this->username . ':' . $this->password);
            $args['headers']['User-Agent'] = 'rapidmail Wordpress Plugin ' . Rapidmail::PLUGIN_VERSION . ' on Wordpress ' . \get_bloginfo('version');
            $args['headers']['Accept'] = 'application/json';

            return \wp_remote_request(
                $url,
                $args
            );

        }

        /**
         * Get list of recipientlists
         *
         * @return array
         */
        public function getRecipientlists() {

            $recipientlists = [];
            $page = 1;

            do {

                $response = $this->request($this->url(self::API_GET_RLS_RESOURCE, [], ['page' => $page]));

                if (\is_wp_error($response)) {
                    return [];
                }

                $response = $response['http_response'];
                $statusCode = (int)$response->get_status();

                if ($statusCode === 200) {

                    $response = \json_decode($response->get_data(), true);

                    foreach ($response['_embedded']['recipientlists'] AS $recipientlist) {
                        $recipientlists[$recipientlist['id']] = $recipientlist['name'];
                    }

                }

                $page++;

            } while ($statusCode === 200 && $response['page'] < $response['page_count']);

            return $recipientlists;

        }

        /**
         * @inheritdoc
         */
        public function getRecipientlist($recipientlistId) {

            $response = $this->request($this->url(self::API_GET_RL_RESOURCE, [$recipientlistId]));

            if (\is_wp_error($response)) {
                return null;
            }

            $response = $response['http_response'];

            if ((int)$response->get_status() === 200) {
                return \json_decode($response->get_data(), true);
            }

            return null;

        }

        /**
         * Get full URL for given resource URL
         *
         * @param string $resource_url
         * @param array $args
         * @param array $queryParams
         * @return string
         */
        private function url($resource_url, array $args = [], array $queryParams = []) {

            $url = Rapidmail::instance()->getConfig('apiv3_baseurl') . \vsprintf(
                $resource_url,
                $args
            );

            if (!empty($queryParams)) {
                $url .= '?' . http_build_query($queryParams);
            }

            return $url;

        }

        /**
         * @param int $recipientlistId
         * @param array $recipientData
         * @return bool|null
         */
        public function subscribeRecipient($recipientlistId, array $recipientData) {

            $recipientData['recipientlist_id'] = $recipientlistId;

            $response = $this->request($this->url(self::API_CREATE_RCPT_RESOURCE, [], ['send_activationmail' => 'yes', 'track_stats' => 'yes']), [
                'method' => 'POST',
                'body' => json_encode($recipientData),
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            if (\is_wp_error($response)) {
                return null;
            }

            if (intval($response['http_response']->get_status()) === 201) {
                return true;
            }

            return null;

        }

        /**
         * @inheritdoc
         */
        public function getFormFields($recipientlistId)
        {

            $response = $this->request($this->url(self::API_GET_FORM_FIELDS_RESOURCE, [$recipientlistId]), [
                'method' => 'GET',
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            if (!\is_wp_error($response) && intval($response['http_response']->get_status()) === 200) {
                return \json_decode($response['http_response']->get_data(), true)['fields'];
            }

            return null;

        }

    }
