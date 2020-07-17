<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class IPayAfrica {
   /**
     * MPesa channel.
     */
    const CHANNEL_MPESA = 'mpesa';

    /**
     * Airtel channel.
     */
    const CHANNEL_AIRTEL = 'airtel';

    /**
     * Equity Bank channel.
     */
    const CHANNEL_EQUITY = 'equity';

    /**
     * Mobile banking channel.
     */
    const CHANNEL_MOBILE_BANKING = 'mobilebanking';

    /**
     * Debit card channel.
     */
    const CHANNEL_DEBIT_CARD = 'debitcard';

    /**
     * Credit card channel.
     */
    const CHANNEL_CREDIT_CARD = 'creditcard';

    /**
     * Mkopo rahisi channel.
     */
    const CHANNEL_MKOPO_RAHISI = 'mkoporahisi';

    /**
     * Saida channel.
     */
    const CHANNEL_SAIDA = 'saida';

    /**
     * This is for http/https callback.
     */
    const CALLBACK_MODE_HTTP = 0;

    /**
     * This is for a data stream of comma separated values.
     */
    const CALLBACK_MODE_CSV = 1;

    /**
     * This is for a json data stream.
     */
    const CALLBACK_MODE_JSON_STREAM = 2;

    /**
     * State whether the request is demo or production.
     *
     * @var int
     */
    protected $isLive = 1;

    /**
     * The security key that has been generated.
     *
     * @var
     */
    protected $hashKey;

    /**
     * The transaction details.
     *
     * @var array
     */
    private $transaction = [
	'amount' => 0,
	'orderId' => null,
	'invoiceNumber' => null,
    ];

    /**
     * All available transaction channels.
     *
     * @var array
     */
    private $allChannels = [
	self::CHANNEL_MPESA,
	self::CHANNEL_AIRTEL,
	self::CHANNEL_EQUITY,
	self::CHANNEL_MOBILE_BANKING,
	self::CHANNEL_DEBIT_CARD,
	self::CHANNEL_CREDIT_CARD,
	self::CHANNEL_MKOPO_RAHISI,
	self::CHANNEL_SAIDA,
    ];

    /**
     * Default active channels.
     *
     * @var array
     */
    private $activeChannels = [
	self::CHANNEL_MPESA,
	self::CHANNEL_AIRTEL,
	self::CHANNEL_EQUITY,
	self::CHANNEL_CREDIT_CARD,
	self::CHANNEL_DEBIT_CARD,
    ];

    /**
     * The customer details.
     *
     * @var array
     */
    private $customer = [
	'telephoneNumber' => null,
	'email' => null,
	'sendEmail' => 0,
    ];

    /**
     * The vendor ID to be used when transacting.
     *
     * @var
     */
    private $vendorId;

    /**
     * The currency to be used. Either USD or KES.
     *
     * @var string
     */
    private $currency = 'KES';

    /**
     * Additional parameters that are to be sent with the request.
     *
     * @var array
     */
    private $payloads = [
	'payload1' => null,
	'payload2' => null,
	'payload3' => null,
	'payload4' => null,
    ];

    /**
     * The url to be called when the transaction succeeds.
     *
     * @var
     */
    private $callback;

    /**
     * The url to be called when the transaction fails.
     *
     * @var
     */
    private $failedCallback;

    /**
     * The callback mode to be used.
     *
     * @var int
     */
    private $callbackMode = self::CALLBACK_MODE_JSON_STREAM;

    /**
     * The transaction endpoint to be called.
     *
     * @var string
     */
    private $initiateEndpoint = 'https://payments.ipayafrica.com/v3/ke';
    public function __construct() {
//	$this->client = new Client([
//	    'verify' => false,
//	    'timeout' => 60,
//	    'allow_redirects' => true,
//	]);
    }
    /**
     * Set the transaction to demo and not production.
     *
     * @return $this
     */
    public function isDemo() {
	$this->isLive = 0;

	return $this;
    }

    /**
     * Set the customer details to be used during the transaction.
     *
     * @param $telephone
     * @param null $email
     * @param bool $sendEmail
     * @return $this
     */
    public function withCustomer($telephone, $email = null, $sendEmail = false) {
	$this->customer = [
	    'telephoneNumber' => $telephone,
	    'email' => $email,
	    'sendEmail' => $sendEmail ? 1 : 0,
	];

	return $this;
    }

    /**
     * Set up the vendor details.
     *
     * @param $vendorId
     * @param $hash
     * @return $this
     */
    public function usingVendorId($vendorId, $hash) {
	$this->vendorId = $vendorId;
	$this->hashKey = $hash;

	return $this;
    }

    /**
     * Set the currency to be used to transact.
     *
     * @param string $currency
     * @return $this
     */
    public function usingCurrency($currency = 'KES') {
	$this->currency = $currency;

	return $this;
    }

    /**
     * @param $channels
     * @return $this
     */
    public function usingChannels($channels) {
	$this->activeChannels = $channels;

	return $this;
    }

    /**
     * Set up the additional parameters to go with the request.
     *
     * @param $first
     * @param $second
     * @param $third
     * @param $fourth
     * @return $this
     */
    public function withPayloads($first, $second, $third, $fourth) {
	$this->payloads = [
	    'payload1' => $first,
	    'payload2' => $second,
	    'payload3' => $third,
	    'payload4' => $fourth,
	];

	return $this;
    }
    /**
     * Set up the callbacks.
     *
     * @param $successCallback
     * @param null $failedCallback
     * @param int $callbackMode
     * @return $this
     */
    public function withCallback($successCallback, $failedCallback = null, $callbackMode = self::CALLBACK_MODE_HTTP) {
	$this->callback = $successCallback;
	$this->failedCallback = $failedCallback;
	$this->callbackMode = $callbackMode;

	return $this;
    }

    /**
     * Set up the transaction details and forward the request to be handled.
     *
     * @param $amount
     * @param $orderId
     * @param null $invoiceNumber
     * @return string
     */
    public function transact($amount, $orderId, $invoiceNumber = null) {
	$this->transaction = [
	    'amount' => $amount,
	    'orderId' => $orderId,
	    'invoiceNumber' => $invoiceNumber ?: $orderId,
	];

	return $this->initiateTransaction();
    }

    /**
     * Initiate the transaction and send the information to IPay.
     *
     * @return string
     */
    public function initiateTransaction() {
	$params = [
	    "live" => $this->isLive,
	    "oid" => $this->transaction['orderId'],
	    "inv" => $this->transaction['invoiceNumber'] ?: $this->transaction['orderId'],
	    "ttl" => $this->transaction['amount'],
	    "tel" => $this->customer['telephoneNumber'],
	    "eml" => $this->customer['email'],
	    "vid" => $this->vendorId,
	    "curr" => $this->currency,
	    "p1" => $this->payloads['payload1'],
	    "p2" => $this->payloads['payload2'],
	    "p3" => $this->payloads['payload3'],
	    "p4" => $this->payloads['payload4'],
	    "lbk" => $this->failedCallback,
	    "cbk" => $this->callback,
	    "cst" => $this->customer['sendEmail'],
	    "crl" => $this->callbackMode,
	    "hsh" => $this->generateInitialHash(),
	];
	echo "<html>\n";
	echo "<head><title>Processing Payment...</title></head>\n";
	echo "<body onLoad=\"document.forms['ipayAfrica_form'].submit();\">\n";
	echo "<body>\n";
	echo "<h2>Please wait, your order is being processed and you";
	echo " will be redirected to the IPAY AFRICA website.</h2>\n";
	echo "<form method=\"post\" name=\"ipayAfrica_form\" ";
	echo "action=\"" . $this->initiateEndpoint . "\">\n";
	foreach ($params as $key => $value) {
	    echo $key.' :<input name="'.$key.'" type="text" value="'.$value.'"></br>';
	}
	echo "<br/><br/>If you are not automatically redirected to ";
	echo "ipayAfrica within 5 seconds...<br/><br/>\n";
	echo "<input type=\"submit\" value=\"Click Here\">\n";

	echo "</form>\n";
	echo "</body></html>\n";
    }
    /**
     * Generate the hash from the data to be sent.
     *
     * @return string
     */
    private function generateInitialHash() {
	$mergedString = $this->isLive . $this->transaction['orderId'] . $this->transaction['invoiceNumber'] .
		$this->transaction['amount'] . $this->customer['telephoneNumber'] . $this->customer['email'] .
		$this->vendorId . $this->currency . $this->payloads['payload1'] . $this->payloads['payload2'] .
		$this->payloads['payload3'] . $this->payloads['payload4'] . $this->callback .
		$this->customer['sendEmail'] . $this->callbackMode;

	return hash_hmac('sha1', $mergedString, $this->hashKey);
    }

}
