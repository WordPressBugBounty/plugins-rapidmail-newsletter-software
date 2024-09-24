<?php

    namespace Rapidmail;

    /**
     * Rapidmail widget class
     */
    class Widget extends \WP_Widget {

        /**
         * Constructor
         */
        public function __construct() {

            parent::__construct(
                'rapidmail_subscription_form',
                \__('rapidmail Anmeldeformular', Rapidmail::TEXT_DOMAIN),
                [
                    'customize_selective_refresh' => true,
                ]
            );

        }

        /**
         * Get checkbox configs
         *
         * @return array
         */
        private static function getDefaultFieldConfigs() {

            return [
                'email' => [
                    'type' => 'email',
                    'label' => \__('E-Mail', Rapidmail::TEXT_DOMAIN),
                    'constraints' => [
                        'required' => true,
                        'maxlength' => 255
                    ]
                ],
                'firstname' => [
                    'type' => 'text',
                    'label' => \__('Vorname', Rapidmail::TEXT_DOMAIN),
                    'constraints' => [
                        'required' => false,
                        'maxlength' => 255
                    ]
                ],
                'lastname' => [
                    'type' => 'text',
                    'label' => \__('Nachname', Rapidmail::TEXT_DOMAIN),
                    'constraints' => [
                        'required' => false,
                        'maxlength' => 255
                    ]
                ],
                'gender' => [
                    'type' => 'radio',
                    'constraints' => [
                        'required' => false
                    ],
                    'label' => \__('Anrede', Rapidmail::TEXT_DOMAIN),
                    'values' => [
                        'male' => \__('männlich'),
                        'female' => \__('weiblich')
                    ]
                ],
                'mailtype' => [
                    'label' => \__('Format', Rapidmail::TEXT_DOMAIN),
                    'type' => 'radio',
                    'constraints' => [
                        'required' => false
                    ],
                    'values' => [
                        'html' => \__('HTML'),
                        'text' => \__('Text')
                    ]
                ]
            ];

        }

        /**
         * Get form config depending on API and config
         *
         * @return array
         */
        private static function getFormConfig() {

            $fields = [];

            foreach (self::getRawFormConfig() as $fieldName => $fieldConfig) {

                if ($fieldName === 'consent_text') {
                    $fieldConfig['label'] = \__('Text zur Einwilligung', Rapidmail::TEXT_DOMAIN);
                }

                if ($fieldName !== 'captcha') {
                    $fields[$fieldName] = $fieldConfig;
                }

            }

            return $fields;

        }

        /**
         * Get raw form config
         *
         * @return array
         */
        private static function getRawFormConfig() {

            $rapidmail = Rapidmail::instance();
            $options = $rapidmail->getOptions();

            if ($options->getApiVersion() === Api::API_V3 && $options->get('apiv3_automatic_fields') === 1) {
                return $rapidmail->getApi()->getFormFields($options->getRecipientlistId());
            }

            return $formConfig = self::getDefaultFieldConfigs();

        }

        /**
         * @inheritdoc
         */
        public function form($instance) {

            $defaults = [
                'title' => \__('Newsletter Anmeldung', Rapidmail::TEXT_DOMAIN)
            ];

            $args = \wp_parse_args((array)$instance, $defaults);

            ?>
            <p>
                <label for="<?php echo \esc_attr($this->get_field_id('title')); ?>"><?php \_e('Überschrift', Rapidmail::TEXT_DOMAIN); ?></label>
                <input class="widefat" id="<?php echo \esc_attr($this->get_field_id('title')); ?>" name="<?php echo \esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo \esc_attr($args['title']); ?>" />
            </p>
            <?php

            foreach (self::getFormConfig() AS $id => $config) {
                if ($config['type'] !== 'honeypot') {
            ?>
                <p>
                    <input class="checkbox" type="checkbox" id="<?php echo \esc_attr($this->get_field_id('show_' . $id)); ?>" name="<?php echo \esc_attr($this->get_field_name('show_' . $id)); ?>" <?php if ($args['show_' . $id] || $config['constraints']['required']) { ?> checked="checked" <?php } if ($config['constraints']['required']) { ?> disabled="disabled" title="<?php \_e('Pflichtfeld', Rapidmail::TEXT_DOMAIN); ?>"<?php } ?>/>
                    <label for="<?php echo \esc_attr($this->get_field_id('show_' . $id)); ?>"><?php echo $config['label']; ?></label>
                </p>
            <?php
                }
            }

        }

        /**
         * @inheritdoc
         */
        public function update($new_instance, $instance) {

            $instance['title'] = isset($new_instance['title']) ? \wp_strip_all_tags($new_instance['title']) : '';
            $formConfig = self::getFormConfig();

            foreach ($formConfig AS $checkbox => $title) {
                $instance['show_' . $checkbox] = isset($new_instance['show_' . $checkbox]) || !empty($rawFormConfig[$checkbox]['constraints']['required']);
            }

            return $instance;

        }

        /**
         * @inheritdoc
         */
        public function widget($args, $instance) {

            $rapidmail = Rapidmail::instance();

            $args['rm_is_api_configured'] = $rapidmail->getApi()->isConfigured();

            $template = new Template();
            $template->assign([
                'instance' => (array)$instance,
                'settings' => $args,
                'widget' => $this,
                'options' => $rapidmail->getOptions(),
                'form_config' => self::getRawFormConfig()
            ]);

            $template->display('widget');

        }

        /**
         * Add widget styles and scripts
         */
        public static function addResources() {

            \wp_enqueue_style('rapidmail-widget-css', \plugins_url('css/widget.css', __DIR__));

            $options = Rapidmail::instance()->getOptions();
            $errorMessage = $options->get('form_subscription_error_message');
            $successMessage = $options->get('form_subscription_success_message');

            \wp_register_script('rapidmail-widget-js', \plugins_url('js/widget.js', __DIR__), ['jquery-core']);
            \wp_localize_script('rapidmail-widget-js', 'rmwidget', [
                'msg_an_error_occurred' => empty($errorMessage) ? \__('Es ist ein Fehler aufgetreten', Rapidmail::TEXT_DOMAIN) : $errorMessage,
                'msg_subscribe_success' => empty($successMessage) ? \__('Vielen Dank für Ihre Anmeldung!', Rapidmail::TEXT_DOMAIN) : $successMessage,
                'spinner_uri' => \get_site_url(null, '/wp-includes/images/wpspin_light.gif')
            ]);
            \wp_enqueue_script('rapidmail-widget-js');

        }

        /**
         * Register widget
         *
         * @param Api $api
         */
        public static function register(Api $api) {

            if (!\is_admin() || $api->isAuthenticated()) {

                \register_widget('Rapidmail\\Widget');
                \add_action('wp_enqueue_scripts', [__CLASS__, 'addResources']);

                \add_shortcode( 'rm_form', function($args) {

                    $args = array_merge([
                        'show_firstname' => false,
                        'show_lastname' => false,
                        'show_gender' => false,
                        'show_mailtype' => false
                    ], (empty($args) || !is_array($args) ? [] : $args));

                    $template = new Template();
                    $template->assign([
                        'instance' => $args,
                        'settings' => [],
                        'widget' => (object)[
                            'number' => uniqid()
                        ],
                        'options' => Rapidmail::instance()->getOptions(),
                        'form_config' => self::getRawFormConfig()
                    ]);

                    return $template->render('shortcode');

                });
            }

        }

    }
