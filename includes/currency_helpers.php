<?php
function get_supported_currencies() {
    return ['USD', 'EUR', 'GBP', 'NGN'];
}

function get_currency_symbol($currency) {
    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'NGN' => '₦',
    ];
    $currency = preg_replace('/[^A-Z]/', '', strtoupper($currency));
    return $symbols[$currency] ?? $currency . ' ';
}

function get_usd_rates() {
    static $rates = null;
    if ($rates !== null) return $rates;
    $url = "https://v6.exchangerate-api.com/v6/f5bbc8d2e19e51a669c909b1/latest/USD";
    $json = @file_get_contents($url);
    if ($json === false) return [];
    $data = json_decode($json, true);
    $rates = isset($data['conversion_rates']) && is_array($data['conversion_rates']) ? $data['conversion_rates'] : [];
    return $rates;
}

function convert_usd_to_currency($amount, $to_currency) {
    $to_currency = preg_replace('/[^A-Z]/', '', strtoupper($to_currency));
    if (!in_array($to_currency, get_supported_currencies())) {
        return $amount; // fallback if unsupported currency
    }
    $rates = get_usd_rates();
    if (isset($rates[$to_currency]) && is_numeric($rates[$to_currency])) {
        return $amount * $rates[$to_currency];
    }
    return $amount; // fallback
}

function convert_to_usd($amount, $from_currency) {
    $from_currency = preg_replace('/[^A-Z]/', '', strtoupper($from_currency));
    if ($from_currency === 'USD') return $amount;
    $rates = get_usd_rates();
    if (isset($rates[$from_currency]) && is_numeric($rates[$from_currency]) && $rates[$from_currency] > 0) {
        return $amount / $rates[$from_currency];
    }
    return $amount; // fallback
}
?>
