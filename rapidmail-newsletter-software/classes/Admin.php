<?php

namespace Rapidmail;

use Rapidmail\Api\AdapterInterface;

/**
 * rapidmail admin options
 */
class Admin {

    /**
     * @var Options
     */
    private $options;

    /**
     * @var Api
     */
    private $api;

    /**
     * Constructor
     *
     * @param Options $options
     * @param Api $api
     */
    public function __construct(Options $options, Api $api) {
        $this->options = $options;
        $this->api = $api;
    }

    /**
     * Initialize admin menu item
     */
    public function initMenu() {

        \add_options_page(
            \__('rapidmail Options', Rapidmail::TEXT_DOMAIN),
            'rapidmail',
            'manage_options',
            'rapidmail',
            [
                $this,
                'showOptionsPage'
            ]
        );

    }

    /**
     * Show options page
     */
    public function showOptionsPage() {

        $link = '<a href="https://www.rapidmail.de/wordpress-plugin/lp?pid=o-wp&tid2=wpplugin&version=' . Rapidmail::PLUGIN_VERSION . '" target="_blank">' . \__('Jetzt kostenlos bei rapidmail anmelden!', Rapidmail::TEXT_DOMAIN) . '</a>';
        $help_link = '<a href="https://de.rapidmail.wiki/faq/wordpress-plugin/" target="_blank">' . \__('rapidmail Hilfebereich', Rapidmail::TEXT_DOMAIN) . '</a>';

        ?>
        <div class="wrap">
            <h1><?php \_e('Einstellungen', Rapidmail::TEXT_DOMAIN); ?> &rsaquo; rapidmail</h1>
            <p><?php \printf(\__('Bitte hinterlegen Sie hier Ihre rapidmail API Zugangsdaten. Wenn Sie noch kein Kunde bei rapidmail sind, können Sie sich hier kostenlos anmelden: %s Eine Anleitung finden Sie außerdem im %s.', Rapidmail::TEXT_DOMAIN), $link, $help_link); ?></p>
            <?php if ($this->options->getApiVersion() === 1): ?>
                <p style="color: #807359; border: 1px solid #e5cfa1; padding: 5px 5px 5px 35px; background: #fcf4e3 url(<?php echo \esc_url(\admin_url('images/no.png' )); ?>) no-repeat 10px center;">
                    <?php echo \__('Sie verwenden zurzeit die veraltete Version 1 der rapidmail API. Um den vollen Funktionsumfang sowie regelmäßige Updates dieses Plugins genießen zu können, sollten Sie in Zukunft auf die Version 3 der rapidmail API umstellen. Das ist mit wenigen Klicks im Kundenbereich von rapidmail möglich.', Rapidmail::TEXT_DOMAIN); ?>
                </p>
            <?php endif; ?>
            <form method="post" action="options.php">
                <?php
                \settings_fields( 'rapidmail' );
                \do_settings_sections( 'rapidmail' );
                \submit_button(null, 'primary', 'save');
                ?>
            </form>
        </div>
        <?php

    }

    /**
     * Sanitize options before saving
     *
     * @param array $values
     * @return array
     */
    public function sanitizeOptions(array $values) {

        $sane_data = [];
        $sane_data['api_version'] = \intval($values['api_version']);
        $sane_data['version'] = isset($values['version']) ? $values['version'] : Rapidmail::PLUGIN_VERSION;
        $sane_data['initial_version'] = isset($values['initial_version']) ? $values['initial_version'] : Rapidmail::PLUGIN_VERSION;

        if (!\in_array($sane_data['api_version'], [1, 3], true)) {

            \add_settings_error(
                Options::OPTION_KEY,
                \esc_attr('api_version'),
                \__('Ungültige API-Version', Rapidmail::TEXT_DOMAIN),
                'error'
            );

            return $sane_data;

        }

        if ($sane_data['api_version'] === AdapterInterface::API_V1) {

            $sane_data['api_key'] = \sanitize_text_field($values['api_key']);
            $sane_data['recipient_list_id'] = empty($values['recipient_list_id']) ? '' : \intval($values['recipient_list_id']);
            $sane_data['node_id'] = empty($values['node_id']) ? '' : \intval($values['node_id']);

        } elseif ($sane_data['api_version'] === AdapterInterface::API_V3) {

            $sane_data['apiv3_automatic_fields'] = (int)(!$this->options->wasInstalledBefore210() || !empty($values['apiv3_automatic_fields']));
            $sane_data['apiv3_username'] = \sanitize_text_field($values['apiv3_username']);
            $sane_data['apiv3_password'] = \sanitize_text_field($values['apiv3_password']);

            $this->options->setAll($sane_data);
            $this->api->reset();

            if ($this->api->isAuthenticated()) {

                if (!\preg_match('/^[1-9][0-9]*$/', $values['apiv3_recipientlist_id']) || !\array_key_exists($values['apiv3_recipientlist_id'], $this->api->adapter()->getRecipientlists())) {

                    \add_settings_error(
                        Options::OPTION_KEY,
                        \esc_attr('apiv3_recipientlist_id'),
                        \__('Bitte eine gültige Empfängerliste auswählen', Rapidmail::TEXT_DOMAIN),
                        'error'
                    );

                } else {
                    $sane_data['apiv3_recipientlist_id'] = \intval($values['apiv3_recipientlist_id']);
                }

            }

        }

        $sane_data['comment_subscription_active'] = \intval($values['comment_subscription_active']);
        $sane_data['comment_subscription_label'] = empty($values['comment_subscription_label']) ? NULL : \sanitize_text_field($values['comment_subscription_label']);

        $this->options->setAll($sane_data);
        $this->api->reset();

        if ($this->api->isConfigured()) {

            $sane_data['subscribe_form_url'] = $this->api->getSubscribeFormUrl();

            if ($this->options->getApiVersion() === AdapterInterface::API_V3) {
                $sane_data['apiv3_subscribe_field_key'] = $this->api->getSubscribeFieldKey();
            }

        }

        $sane_data['form_subscription_success_message'] = isset($values['form_subscription_success_message']) ? $values['form_subscription_success_message'] : '';
        $sane_data['form_subscription_error_message'] = isset($values['form_subscription_error_message']) ? $values['form_subscription_error_message'] : '';

        return $sane_data;

    }

    /**
     * Event handler for adminInit event
     */
    public function onAdminInit() {

        \register_setting(
            'rapidmail',
            Options::OPTION_KEY,
            array(
                'description' => \__('Verwendete API Version', Rapidmail::TEXT_DOMAIN),
                'sanitize_callback' => [$this, 'sanitizeOptions']
            )
        );

        \add_settings_section(
            'connection',
            \__('Verbindungseinstellungen', Rapidmail::TEXT_DOMAIN),
            NULL,
            'rapidmail'
        );

        \add_settings_field(
            'api_version',
            \__('API-Version', Rapidmail::TEXT_DOMAIN),
            function() {

                $apiVersion = $this->options->getApiVersion();

                echo '<select name="rm_options[api_version]" id="rm-api-version">
                            <option value="1"' . ($apiVersion === AdapterInterface::API_V1 ? ' selected="selected"' : '') . '>' . \__('V1 (veraltet)', Rapidmail::TEXT_DOMAIN) . '</option>
                            <option value="3"' . ($apiVersion === AdapterInterface::API_V3 ? ' selected="selected"' : '') . '>' . \__('V3', Rapidmail::TEXT_DOMAIN) . '</option>
                          </select>';
            },
            'rapidmail',
            'connection'
        );

        if ($this->options->getApiVersion() === AdapterInterface::API_V1) {

            \add_settings_field(
                'api_key',
                \__('API-Schlüssel', Rapidmail::TEXT_DOMAIN),
                function() {

                    echo '<input type="text" class="regular-text" value="' . \esc_html($this->options->get('api_key')) . '" id="api_key" name="rm_options[api_key]">';

                    if ($this->api->isAuthenticated()) {
                        echo '&nbsp;<img src="' . \esc_url(\admin_url('images/yes.png' )) . '" alt="' . \__('Verbindung hergestellt', Rapidmail::TEXT_DOMAIN) . '" />';
                    } elseif ($this->api->isConfigured()) {
                        echo '&nbsp;<img src="' . \esc_url(\admin_url('images/no.png' )) . '" alt="' . \__('Verbindungsaufbau fehlgeschlagen', Rapidmail::TEXT_DOMAIN) . '" />';
                    }

                    echo '<br><small>' . \__('Den API Key, die ID der Empfängerliste und Node-ID finden Sie im rapidmail Kundenbereich unter Einstellungen &rsaquo; API', Rapidmail::TEXT_DOMAIN) . '</small>';

                },
                'rapidmail',
                'connection'
            );

            \add_settings_field(
                'recipient_list_id',
                \__('ID der Empfängerliste', Rapidmail::TEXT_DOMAIN),
                function() {
                    echo '<input type="text" class="regular-text" value="' . \esc_html($this->options->get('recipient_list_id')) . '" id="recipient_list_id" name="rm_options[recipient_list_id]">';
                },
                'rapidmail',
                'connection'
            );

            \add_settings_field(
                'node_id',
                \__('Node ID', Rapidmail::TEXT_DOMAIN),
                function() {
                    echo '<input type="text" class="regular-text" value="' . \esc_html($this->options->get('node_id')) . '" id="node_id" name="rm_options[node_id]">';
                },
                'rapidmail',
                'connection'
            );

        }

        if ($this->options->getApiVersion() === AdapterInterface::API_V3) {

            \add_settings_field(
                'apiv3_username',
                \__('API-Benutzername', Rapidmail::TEXT_DOMAIN),
                function() {

                    echo '<input type="text" class="regular-text" value="' . \esc_html($this->options->get('apiv3_username')) . '" id="apiv3_username" name="rm_options[apiv3_username]">';

                    if ($this->api->isAuthenticated()) {
                        echo '&nbsp;<img src="' . \esc_url(\admin_url('images/yes.png' )) . '" alt="' . \__('Verbindung hergestellt', Rapidmail::TEXT_DOMAIN) . '" />';
                    } elseif ($this->api->isConfigured()) {
                        echo '&nbsp;<img src="' . \esc_url(\admin_url('images/no.png' )) . '" alt="' . \__('Verbindungsaufbau fehlgeschlagen', Rapidmail::TEXT_DOMAIN) . '" />';
                    }

                    echo '<br><small>' . \esc_html__('Zugangsdaten für die API finden Sie im rapidmail Kundenbereich unter Einstellungen › API', Rapidmail::TEXT_DOMAIN) . '</small>';

                },
                'rapidmail',
                'connection'
            );

            \add_settings_field(
                'apiv3_password',
                \__('API-Passwort', Rapidmail::TEXT_DOMAIN),
                function() {
                    echo '<input type="password" class="regular-text" value="' . \esc_html($this->options->get('apiv3_password')) . '" id="apiv3_password" name="rm_options[apiv3_password]">';
                },
                'rapidmail',
                'connection'
            );

            \add_settings_field(
                'apiv3_recipientlist_id',
                \__('Empfängerliste', Rapidmail::TEXT_DOMAIN),
                function() {

                    echo '<select name="rm_options[apiv3_recipientlist_id]">';

                    if ($this->api->isAuthenticated()) {

                        echo '<option value="0">' . \__('Bitte wählen', Rapidmail::TEXT_DOMAIN) . '</option>';

                        $recipientlists = $this->api->adapter()->getRecipientlists();
                        $recipientlistId = $this->options->get('apiv3_recipientlist_id');

                        foreach ($recipientlists AS $id => $name) {
                            echo '<option value="' . $id . '"' . ($recipientlistId == $id ? ' selected="selected"' : '') . '>' . \esc_html($name) . ' (ID ' . $id . ')</option>';
                        }

                    } else {
                        echo '<option value="0">' . \__('Bitte gültige Zugangsdaten hinterlegen und auf &bdquo;Änderungen speichern&ldquo; klicken', Rapidmail::TEXT_DOMAIN) . '</option>';
                    }

                    echo '</select>';

                },
                'rapidmail',
                'connection'
            );

            if ($this->options->wasInstalledBefore210() && $this->api->isAuthenticated()) {

                $automaticFields = $this->options->get('apiv3_automatic_fields', 0);

                \add_settings_field(
                    'apiv3_automatic_fields',
                    \__('Felder automatisch per API', Rapidmail::TEXT_DOMAIN),
                    function() use($automaticFields) {

                        echo '<fieldset>';
                        echo '<legend class="screen-reader-text">' . \__('Felder automatisch per API', Rapidmail::TEXT_DOMAIN) . '</legend>';
                        echo '<label for="apiv3_automatic_fields_yes"><input type="radio" name="rm_options[apiv3_automatic_fields]" id="apiv3_automatic_fields_yes" value="1"' . ($automaticFields === 1 ? ' checked="checked"' : '') . '> ' . \__('Ja', Rapidmail::TEXT_DOMAIN) . '</label><br>';
                        echo '<label for="apiv3_automatic_fields_no"><input type="radio" name="rm_options[apiv3_automatic_fields]" id="apiv3_automatic_fields_no" value="0"' . ($automaticFields === 0 ? ' checked="checked"' : '') . '> ' . \__('Nein', Rapidmail::TEXT_DOMAIN) . '</label>';
                        echo '</fieldset>';

                    },
                    'rapidmail',
                    'connection'
                );

            }

        }

        \add_settings_section(
            'comments',
            \__('Abonnentengewinnung über Kommentare', Rapidmail::TEXT_DOMAIN),
            function() {
                echo \esc_html__('Durch Aktivierung dieser Funktion wird das Kommentarformular in Ihrem Blog mit einer Newsletter-Bestellmöglichkeit erweitert. 
                            Setzt der Benutzer beim Kommentieren einen Haken erhält er eine Bestätigungs-E-Mail (Double-Opt-In).
                            Nach einem durch Klick auf den Bestätigungslink ist er als aktiver Empfänger in der Empfängerliste eingetragen.', Rapidmail::TEXT_DOMAIN);
            },
            'rapidmail'
        );

        \add_settings_field(
            'comment_subscription_active',
            \__('Aktiv', Rapidmail::TEXT_DOMAIN),
            function() {
                echo '<input type="checkbox" name="rm_options[comment_subscription_active]" id="comment_subscription_active" value="1" ' . (\intval($this->options->get('comment_subscription_active')) ? ' checked="checked"' : '') . ' />';
            },
            'rapidmail',
            'comments'
        );

        \add_settings_field(
            'comment_subscription_label',
            \__('Feldbeschreibung', Rapidmail::TEXT_DOMAIN),
            function() {
                echo '<input type="text" class="regular-text" value="' . \esc_html($this->options->get('comment_subscription_label')) . '" id="comment_subscription_label" name="rm_options[comment_subscription_label]" placeholder="' . esc_html__('Newsletter abonnieren (jederzeit wieder abbestellbar)') . '">';
            },
            'rapidmail',
            'comments'
        );

        \add_settings_section(
            'formtexts',
            \__('Formulartexte', Rapidmail::TEXT_DOMAIN),
            '',
            'rapidmail'
        );

        \add_settings_field(
            'form_subscription_success_message',
            \__('Anmeldung erfolgreich', Rapidmail::TEXT_DOMAIN),
            function() {
                echo '<input type="text" class="regular-text" value="' . \esc_html($this->options->get('form_subscription_success_message')) . '" id="form_subscription_success_message" name="rm_options[form_subscription_success_message]" placeholder="' . esc_html__('Vielen Dank für Ihre Anmeldung!') . '">';
            },
            'rapidmail',
            'formtexts'
        );

        \add_settings_field(
            'form_subscription_error_message',
            \__('Fehler bei der Anmeldung', Rapidmail::TEXT_DOMAIN),
            function() {
                echo '<input type="text" class="regular-text" value="' . \esc_html($this->options->get('form_subscription_error_message')) . '" id="form_subscription_error_message" name="rm_options[form_subscription_error_message]" placeholder="' . esc_html__('Es ist ein Fehler aufgetreten') . '">';
            },
            'rapidmail',
            'formtexts'
        );

    }

    /**
     * Add admin scripts
     *
     * @param string $hook
     */
    public function addScripts($hook) {

        if($hook != 'settings_page_rapidmail') {
            return;
        }

        \wp_register_script('rapidmail-admin', \plugins_url('js/admin.js', __DIR__), ['jquery-core']);
        \wp_enqueue_script('rapidmail-admin');

    }

    /**
     * Add action links for plugins
     */
    private function addPluginActionLinks() {

        \add_filter('plugin_action_links', function($links, $file) {

            if ($file != RAPIDMAIL_PLUGIN_BASENAME) {
                return $links;
            }

            $settings_link = \sprintf(
                '<a href="%1$s">%2$s</a>',
                \menu_page_url('rapidmail', false),
                \esc_html__('Settings', Rapidmail::TEXT_DOMAIN)
            );

            \array_unshift( $links, $settings_link );

            return $links;

        }, 10, 2);

    }

    /**
     * Init admin handling
     */
    public function init() {

        $this->addPluginActionLinks();

        \add_action('admin_init', [$this, 'onAdminInit']);
        \add_action('admin_enqueue_scripts', [$this, 'addScripts']);
        \add_action('admin_menu', [$this, 'initMenu']);

    }

}