<?php

    namespace Rapidmail\Api;

    interface AdapterInterface {

        /**
         * API version 2
         *
         * @var int
         */
        const API_V1 = 1;

        /**
         * API version 2
         *
         * @var int
         */
        const API_V3 = 3;

        /**
         * Check if required settings for establishing a connection have been set
         *
         * @return bool
         */
        public function isConfigured();

        /**
         * Check if we have valid credentials
         * Note that it is not recommended to call this in frontend-context, but only to make sure we're properly
         * authenticated against the API in backend
         *
         * @return bool
         */
        public function isAuthenticated();

        /**
         * Get recipientlist
         *
         * @param int $recipientlistId
         * @return array|NULL
         */
        public function getRecipientlist($recipientlistId);

        /**
         * Subscribe recipient to given recipientlist
         *
         * @param int $recipientlistId
         * @param array $recipientData
         */
        public function subscribeRecipient($recipientlistId, array $recipientData);

        /**
         * Get list of form fields
         *
         * @param int $recipientlistId
         * @return array|null
         */
        public function getFormFields($recipientlistId);

    }