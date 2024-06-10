<?php

function bobospay_give_get_credentials(): array
{
    if (give_is_test_mode()) {
        return array(
            'client_id' => give_get_option('bobospay_sandbox_client_id', ''),
            'client_secret' => give_get_option('bobospay_sandbox_client_secret', ''),
            'encryption_key' => give_get_option('bobospay_sandbox_encryption_key', ''),
        );
    }
    return array(
        'client_id' => give_get_option('bobospay_live_client_id', ''),
        'client_secret' => give_get_option('bobospay_live_client_secret', ''),
        'encryption_key' => give_get_option('bobospay_live_encryption_key', ''),
    );
}

function bobospay_give_has_credentials(): bool
{
    if (give_is_test_mode()) {
        return !empty(give_get_option('bobospay_sandbox_client_id', ''))
            && !empty(give_get_option('bobospay_sandbox_client_secret', ''))
            && !empty(give_get_option('bobospay_sandbox_encryption_key', ''));
    }
    return !empty(give_get_option('bobospay_live_client_id', ''))
        && !empty(give_get_option('bobospay_live_client_secret', ''))
        && !empty(give_get_option('bobospay_live_encryption_key', ''));
}

function bobospay_give_get_environment(): string
{
    return give_is_test_mode() ? 'sandbox' : 'live';
}

function bobospay_give_is_currency_supported($currency): bool
{
    return in_array($currency, bobospay_give_get_supported_currencies());
}

function bobospay_give_get_supported_currencies(): array
{
    return array('USD', 'EUR', "XOF", "NGN");
}

function bobospay_give_decode_transaction($base_64_string){
    return json_decode(base64_decode($base_64_string));
}