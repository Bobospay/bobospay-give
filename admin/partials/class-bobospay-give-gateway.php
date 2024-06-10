<?php

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\PaymentGateway;


/**
 * @inheritDoc
 */
class Bobospay_Give_Gateway extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public $secureRouteMethods = [
        'handleCreatePaymentRedirect',
    ];

    private function setup_credentials()
    {
        $credentials = bobospay_give_get_credentials();
        $client_id = $credentials['client_id'];
        $client_secret = $credentials['client_secret'];
        $encryption_key = $credentials['encryption_key'];

        \Bobospay\Bobospay::setClientId($client_id);
        \Bobospay\Bobospay::setClientSecret($client_secret);
        \Bobospay\Bobospay::setEnvironment(bobospay_give_get_environment());
    }

    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'bobospay_give';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return __('Bobospay', 'bobospay-give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Bobospay', 'bobospay-give');
    }

    /**
     * Register a js file to display gateway fields for v3 donation forms
     */
    public function enqueueScript(int $formId)
    {
    }

    /**
     * Send form settings to the js gateway counterpart
     */
    public function formSettings(int $formId): array
    {
        return [
            'fields' => [
                'heading' => __('Make your donation quickly and securely with Bobospay', 'bobospay-give'),
                'subheading' => __('How it works', 'bobospay-give'),
                'body' => __(
                    'You will be redirected to Bobospay to complete your donation with your debit card, credit card, mobile money or with your Bobospay account. Once complete, you will be redirected back to this site to view your receipt.',
                    'bobospay-give'
                ),
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        // For an offsite gateway, this is just help text that displays on the form. 
        return (new BobospayGiveBillingFields())($formId);
    }

    /**
     * Get Sample Data to send to a gateway.
     */
    public function getParameters(Donation $donation): array
    {
        $this->setup_credentials();

        $callback_url = $this->generateSecureGatewayRouteUrl(
            'handleCreatePaymentRedirect',
            $donation->id,
            [
                'givewp-donation-id' => $donation->id,
                'givewp-success-url' => urlencode(give_get_success_page_uri()),
                'givewp-cancel-url' => urlencode(give_get_failed_transaction_uri()),
            ]
        );

        return [
            "note" => sprintf(__('Donation via GiveWP, ID %s', 'bobospay-give'), $donation->id),
            "amount" => $donation->amount->formatToDecimal(),
            "currency" => give_get_currency($donation->id),
            "callback_url" => $callback_url,
            "custom_data" => [
                "donation_id" => $donation->id
            ],
            "customer" => [
                "firstname" => $donation->firstName,
                "lastname" => $donation->lastName,
                "email" => $donation->email,
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData)
    {
        $data = $this->getParameters($donation);
        $transaction = \Bobospay\Transaction::create($data);

        $gatewayUrl = $transaction->generateToken()->url;

        // Step 4: Return a RedirectOffsite command with the generated URL to redirect the donor to the gateway.
        return new RedirectOffsite($gatewayUrl);
    }

    public function verifyPayment($transaction){
        $this->setup_credentials();
        $transaction = \Bobospay\Transaction::retrieve($transaction->id);
        return $transaction->status;
    }

    protected function handleCancelledPaymentReturn(array $queryParams, $transaction = null): RedirectResponse
    {
        $donationId = (int)$queryParams['givewp-donation-id'];

        /** @var Donation $donation */
        $donation = Donation::find($donationId);
        $donation->status = DonationStatus::CANCELLED();
        if($transaction) $donation->gatewayTransactionId = (string)$transaction->id;
        $donation->save();

        return new RedirectResponse(esc_url_raw($queryParams['givewp-cancel-url']));
    }

    protected function handleSuccessPaymentReturn(array $queryParams, $transaction): RedirectResponse
    {
        $donationId = (int)$queryParams['givewp-donation-id'];

        /** @var Donation $donation */
        $donation = Donation::find($donationId);
        $donation->status = DonationStatus::COMPLETE();
        if($transaction) $donation->gatewayTransactionId = (string)$transaction->id;

        $donation->save();



        DonationNote::create([
            'donationId' => $donation->id,
            'content' => 'Donation Completed from Bobospay.'
        ]);

        return new RedirectResponse(esc_url_raw($queryParams['givewp-success-url']));
    }


    /**
     * An example of using a secureRouteMethod for extending the Gateway API to handle a redirect.
     *
     * @throws Exception
     */
    protected function handleCreatePaymentRedirect(array $queryParams): RedirectResponse
    {
        if(!isset($queryParams["transaction"])){
            return $this->handleCancelledPaymentReturn($queryParams);
        }

        $transaction = bobospay_give_decode_transaction($queryParams["transaction"]);


        $status = $this->verifyPayment($transaction);

        if($status == "Success"){
            return $this->handleSuccessPaymentReturn($queryParams, $transaction);
        }elseif ($status == "Blocked"){
            return $this->handleCancelledPaymentReturn($queryParams, $transaction);
        }

        $donationId = (int)$queryParams['givewp-donation-id'];
        // Step 2: Typically you will find the donation from the donation ID.
        /** @var Donation $donation */
        $donation = Donation::find($donationId);

        // Step 3: Use the Donation model to update the donation based on the transaction and response from the gateway.
        $donation->status = DonationStatus::PROCESSING();
        $donation->gatewayTransactionId = (string)$transaction->id;
        $donation->save();

        // Step 4: Return a RedirectResponse to the GiveWP success page.
        return new RedirectResponse($queryParams['givewp-success-url']);
    }

    /**
     * @inerhitDoc
     * @throws Exception
     */
    public function refundDonation(Donation $donation): PaymentRefunded
    {
        throw new Exception('Method has not been implemented yet. Please use the legacy method in the meantime.');
    }
}