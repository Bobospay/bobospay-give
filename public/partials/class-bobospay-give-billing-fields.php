<?php


use Give\Helpers\Form\Utils;

/**
 * This class handle donation form billing field markup for Bobospay.
 *
 */
class BobospayGiveBillingFields
{
    /**
     *
     * @param int $formId
     *
     * @return string
     */
    public function __invoke(int $formId): string
    {
        ob_start();

        if (give_is_setting_enabled(give_get_option('bobospay_billing_details'))) {
            give_default_cc_address_fields($formId);
        } elseif (Utils::isLegacyForm($formId)) {
            echo '';
        } else {
            printf(
                '
                <fieldset class="no-fields">
                    <div style="display: flex; justify-content: center; margin-top: 20px;">
                       %4$s
                    </div>
                    <p style="text-align: center;"><b>%1$s</b></p>
                    <p style="text-align: center;"><b>%2$s</b> %3$s</p>
                </fieldset>
                ',
                esc_html__('Make your donation quickly and securely with Bobospay', 'bobospay-give'),
                esc_html__('How it works:', 'bobospay-give'),
                esc_html__(
                    'You will be redirected to Bobospay to complete your donation with your debit card, credit card, mobile money or with your Bobospay account. Once complete, you will be redirected back to this site to view your receipt.',
                    'bobospay-give'
                ),
                $this->getLogo()
            );
        }

        return ob_get_clean();
    }

    /**
     * Return paypal logo.
     *
     * @since 2.19.0
     * @since 2.19.4 Use correct logo path.
     *
     * @return string
     */
    private function getLogo(): string
    {
        return file_get_contents(
            GIVE_PLUGIN_DIR . 'src/PaymentGateways/Gateways/PayPalStandard/resources/templates/paypal-standard-logo.svg'
        );
    }
}
