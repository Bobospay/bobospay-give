<?php

/**
 * Class GiveBobospayGatewaySettings
 *
 * @since 1.0
 */
class GiveBobospayGatewaySettings
{
    /**
     * @since  1.0
     * @access static
     * @var GiveBobospayGatewaySettings $instance
     */
    static private $instance;

    /**
     * @since  1.0
     * @access private
     * @var string $section_id
     */
    private $section_id;

    /**
     * @since  1.0
     * @access private
     * @var string $section_label
     */
    private $section_label;

    /**
     * GiveBobospayGatewaySettings constructor.
     */
    private function __construct()
    {
    }

    /**
     * get class object.
     *
     * @return GiveBobospayGatewaySettings
     * @since 1.0
     */
    static function get_instance(): GiveBobospayGatewaySettings
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Setup hooks.
     *
     * @since 1.0
     */
    public function setup_hooks()
    {
        $this->section_id = 'bobospay';
        $this->section_label = __('Bobospay', 'bobospay-give');

//        // Add payment gateway to payment gateways list.
//        add_filter('give_payment_gateways', array($this, 'add_gateways'));

        if (is_admin()) {

            // Add section to payment gateways tab.
            add_filter('give_get_sections_gateways', array($this, 'add_section'));

            // Add section settings.
            add_filter('give_get_settings_gateways', array($this, 'add_settings'));
        }
    }

    /**
     * Add payment gateways to gateways list.
     *
     * @param array $gateways array of payment gateways.
     *
     * @return array
     * @since 1.0
     *
     */
    public function add_gateways($gateways): array
    {
        $gateways[$this->section_id] = array(
            'admin_label' => __('Bobospay', 'bobospay-give'),
            'checkout_label' => __('Bobospay', 'bobospay-give'),
        );

        return $gateways;
    }

    /**
     * Add setting section.
     *
     * @param array $sections Array of section.
     *
     * @return array
     * @since 1.0
     *
     */
    public function add_section($sections): array
    {
        $sections[$this->section_id] = $this->section_label;

        return $sections;
    }

    /**
     * Add plugin settings.
     *
     * @param array $settings Array of setting fields.
     *
     * @return array
     * @since 1.0
     *
     */
    public function add_settings($settings): array
    {
        $current_section = give_get_current_setting_section();

        if ($this->section_id === $current_section) {
            $settings = array(
                array(
                    'id' => 'give_bobospay_payments_setting',
                    'type' => 'title',
                ),
                array(
                    'title' => __('Live Client Id', 'bobospay-give'),
                    'id' => 'bobospay_live_client_id',
                    'type' => 'text',
                    'desc' => __('The LIVE Client Id provided by bobospay. Required for testing donation payments in LIVE mode.', 'bobospay-give'),
                ),
                array(
                    'title' => __('Live Client Secret', 'bobospay-give'),
                    'id' => 'bobospay_live_client_secret',
                    'type' => 'api_key',
                    'desc' => __('The LIVE Client Secret provided by bobospay. Required for testing donation payments in LIVE mode.', 'bobospay-give'),
                ),
                array(
                    'title' => __('Live Encryption Key', 'bobospay-give'),
                    'id' => 'bobospay_live_encryption_key',
                    'type' => 'api_key',
                    'desc' => __('The LIVE Encryption Key provided by bobospay. Required for testing donation payments in LIVE mode.', 'bobospay-give'),
                ),
                array(
                    'title' => __('Sandbox Client Id', 'bobospay-give'),
                    'id' => 'bobospay_sandbox_client_id',
                    'type' => 'text',
                    'desc' => __('The TEST Client Id provided by bobospay. Required for testing donation payments in TEST mode.', 'bobospay-give'),
                ),
                array(
                    'title' => __('Sandbox Client Secret', 'bobospay-give'),
                    'id' => 'bobospay_sandbox_client_secret',
                    'type' => 'api_key',
                    'desc' => __('The TEST Client Secret provided by bobospay. Required for testing donation payments in TEST mode.', 'bobospay-give'),
                ),
                array(
                    'title' => __('Sandbox Encryption Key', 'bobospay-give'),
                    'id' => 'bobospay_sandbox_encryption_key',
                    'type' => 'api_key',
                    'desc' => __('The TEST Encryption Key provided by bobospay. Required for testing donation payments in LIVE mode.', 'bobospay-give'),
                ),
                array(
                    'title' => __('Collect Billing Details', 'bobospay-give'),
                    'id' => 'bobospay_billing_details',
                    'type' => 'radio_inline',
                    'options' => array(
                        'enabled' => esc_html__('Enabled', 'bobospay-give'),
                        'disabled' => esc_html__('Disabled', 'bobospay-give'),
                    ),
                    'default' => 'disabled',
                    'description' => __('If enabled, required billing address fields are added to Bobospay forms. These fields are not required by Bobospay to process the transaction, but you may have a need to collect the data. Billing address details are added to both the donation and donor record in GiveWP.', 'bobospay-give'),
                ),
                array(
                    'id' => 'give_bobospay_payments_setting',
                    'type' => 'sectionend',
                ),
            );
        }// End if().

        return $settings;
    }
}