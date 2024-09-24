<?php

    namespace Rapidmail\Api;

    /**
     * APIv1 adapter class
     */
    class Apiv1 implements AdapterInterface {

        /**
         * @var \rapidmail_apiclient
         */
        private $apiclient;

        /**
         * @var int
         */
        private $nodeId;

        /**
         * @var int
         */
        private $recipientlistId;

        /**
         * @var string
         */
        private $apiKey;

        /**
         * Constructor
         *
         * @param int $nodeId
         * @param int $recipientlistId
         * @param string $apiKey
         */
        public function __construct($nodeId, $recipientlistId, $apiKey) {

            $this->nodeId = $nodeId;
            $this->recipientlistId = $recipientlistId;
            $this->apiKey = $apiKey;

        }

        /**
         * Return apiclient
         *
         * @return null|\rapidmail_apiclient
         */
        private function apiclient() {

            if ($this->apiclient !== NULL) {
                return $this->apiclient;
            }

            try {

                require_once __DIR__ . '/../../vendor/rapidmail/rapidmail_apiclient.class.php';

                return $this->apiclient = new \rapidmail_apiclient(
                    (int)$this->nodeId,
                    (int)$this->recipientlistId,
                    (string)$this->apiKey
                );

            } catch (\Exception $e) {
                // Ignore
            }

            return NULL;

        }

        /**
         * @inheritdoc
         */
        public function isConfigured() {
            return !empty($this->nodeId) && !empty($this->recipientlistId) && !empty($this->apiKey);
        }

        /**
         * @inheritdoc
         */
        public function isAuthenticated() {

            if ($this->isConfigured()) {
                $recipientlist = $this->getRecipientlist(0);
                return !empty($recipientlist);
            }

            return false;

        }

        /**
         * @inheritdoc
         */
        public function getRecipientlist($recipientlistId) {

            if (!$this->isConfigured()) {
                return NULL;
            }

            try {
                return $this->apiclient()->get_metadata();
            } catch (\Exception $e) {
                return null;
            }

        }

        /**
         * @inheritdoc
         */
        public function subscribeRecipient($recipientlistId, array $recipientData) {

            $email = $recipientData['email'];
            unset($recipientData['email']);

            $recipientData['status'] = 'new';
            $recipientData['activationmail'] = 'yes';

            try {
                $this->apiclient()->add_recipient($email, $recipientData);
            } catch (\Exception $e) {
                return null;
            }

        }

        /**
         * @inheritdoc
         */
        public function getFormFields($recipientlistId)
        {
            throw new \RuntimeException('Feature not supported by APIv1');
        }

    }