<?php

    namespace Rapidmail;

    /**
     * rapidmail base class
     */
    class Rapidmail {

        /**
         * Test domain to use for translation
         *
         * @var string
         */
        const TEXT_DOMAIN = 'rapidmail';

        /**
         * Plugin version number
         *
         * @var string
         */
        const PLUGIN_VERSION = '2.1.5';

        /**
         * @var Options
         */
        private $options;

        /**
         * @var Api
         */
        private $api;

        /**
         * @var Rapidmail
         */
        private static $instance;

        /**
         * @var array
         */
        private $config = [];

        /**
         * Get rapidmail instance
         *
         * @return Rapidmail
         */
        public static function instance() {

            if (self::$instance === NULL) {
                self::$instance = new self();
            }

            return self::$instance;

        }

        /**
         * Constructor
         */
        public function __construct() {

            $this->loadConfig();
            $this->registerAutoloader();

            $this->updateInitialVersion();

            if (\is_admin()) {
                $admin = new Admin($this->getOptions(), $this->getApi());
                $admin->init();
            }

            \add_action('widgets_init', function()  {
                Widget::register($this->getApi());
            });

            if ($this->getOptions()->get('comment_subscription_active')) {
                $this->addCommentFormCheckbox();
            }

        }

        /**
         * Update initial version option
         */
        private function updateInitialVersion() {

            $options = $this->getOptions();

            if (!$options->get('initial_version', false)) {

                // If initial version is not set, we need to set it

                if ($options->get('api_version') === NULL) {
                    // No config was found yet, assume this is the first activation
                    $initialVersion = self::PLUGIN_VERSION;
                    $options->set('apiv3_automatic_fields', 1);
                } else {
                    // Config is already there, we assume initial version was 2.0
                    $initialVersion = '2.0.0';
                    $options->set('apiv3_automatic_fields', 0);
                }

                $options->set('initial_version', $initialVersion);
                $options->save();
                $options->update();

            }

        }

        /**
         * Load config
         */
        private function loadConfig() {

            $this->config = require __DIR__ . '/../config/config.php';

            if (is_file(__DIR__ . '/../config/development.config.php')) {
                $this->config = array_replace_recursive($this->config, require __DIR__ . '/../config/development.config.php');
            }

        }

        /**
         * @return Options
         */
        public function getOptions() {

            if ($this->options === NULL) {
                $this->options = new Options();
            }

            return $this->options;

        }

        /**
         * @return Api
         */
        public function getApi() {

            if ($this->api === NULL) {
                $this->api = new Api($this->getOptions());
            }

            return $this->api;

        }

        /**
         * Register rapidmail autoloader
         */
        private function registerAutoloader() {

            \spl_autoload_register(function($className) {

                if (\substr($className, 0, 10) !== 'Rapidmail\\') {
                    return;
                }

                require_once __DIR__ . '/'
                    . \ltrim(\str_replace('\\', \DIRECTORY_SEPARATOR, \substr($className, 10)), '\\')
                    . '.php';

            });

        }

        /**
         * Add checkbox to comment form
         */
        private function addCommentFormCheckbox() {

            \add_action('comment_form_after_fields', function() {

                $label = $this->getOptions()->get('comment_subscription_label');

                if (empty($label)) {
                    $label = \__('Newsletter abonnieren (jederzeit wieder abbestellbar)', self::TEXT_DOMAIN);
                }

                echo '<p class="comment-form-rm-subscribe">'
                    . '<input type="checkbox" name="rm_subscribe" id="rm_subscribe" value="1" />'
                    . '<label for="rm_subscribe">' . esc_html__($label, self::TEXT_DOMAIN) . '</label>'
                    . '</p>';

            });

            \add_action('comment_post', function($commentId) {

                if (empty($_POST['rm_subscribe'])) {
                    return;
                }

                $comment = \get_comment($commentId);

                $this->getApi()
                    ->subscribeRecipient($this->options->getRecipientlistId(), [
                        'email' => $comment->comment_author_email
                    ]);

            });

            \add_action('wp_enqueue_scripts', function() {

                if (\is_singular()) {
                    \wp_enqueue_style('rapidmail-comment-form', \plugins_url('css/comment-form.css', __DIR__));
                }

            });

        }

        /**
         * Get API base URL
         *
         * @return mixed|null
         */
        public function getConfig($key) {

            if (isset($this->config[$key])) {
                return $this->config[$key];
            }

            return NULL;

        }

    }
