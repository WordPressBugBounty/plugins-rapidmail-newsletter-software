<?php

    namespace Rapidmail;

    use Rapidmail\Api\AdapterInterface;

    /**
     * rapidmail options wrapper
     */
    class Options {

        /**
         * Key to use in wordpress options
         *
         * @var string
         */
        const OPTION_KEY = 'rm_options';

        /**
         * @var array
         */
        private $options;

        /**
         * Constructor
         */
        public function __construct() {
            $this->update();
        }

        /**
         * Update options from wordpress
         */
        public function update() {
            $this->options = \get_option(self::OPTION_KEY);
        }

        /**
         * Set all options
         *
         * @param $options
         * @internal
         */
        public function setAll($options) {
            $this->options = $options;
        }

        /**
         * Get API version
         *
         * @return int
         */
        public function getApiVersion() {
            return (int)$this->get('api_version', AdapterInterface::API_V3);
        }

        /**
         * Get value from options by key
         * @param string $key
         * @param null|mixed $default
         * @return mixed|null
         */
        public function get($key, $default = NULL) {

            if (!isset($this->options[$key])) {
                return $default;
            }

            return $this->options[$key];

        }

        /**
         * Save options
         */
        public function save() {
            $this->options['current_version'] = Rapidmail::PLUGIN_VERSION;
            \update_option('rm_options', $this->options);
        }

        /**
         * Set option value
         *
         * @param string $key
         * @param mixed $value
         */
        public function set($key, $value) {
            $this->options[$key] = $value;
            $this->save();
        }

        /**
         * Get recipientlist ID configured for API version
         *
         * @return mixed|null
         * @throws \Exception
         */
        public function getRecipientlistId() {

            switch ($this->getApiVersion()) {

                case AdapterInterface::API_V1:
                    return $this->get('recipient_list_id');

                case AdapterInterface::API_V3:
                    return $this->get('apiv3_recipientlist_id');

                default:
                    throw new \Exception('Invalid API version');

            }

        }

        /**
         * Check if plugin was installed before plugin version 2.1.0
         *
         * @return bool
         */
        public function wasInstalledBefore210() {

            $initialVersion = $this->get('initial_version');

            if ($initialVersion === NULL) {
                return true;
            }

            return version_compare($initialVersion, '2.1.0-beta1', '<');

        }

    }