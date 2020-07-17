<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Ipaytest extends CI_Controller {

    public function __construct() {
	parent::__construct();
		$this->load->library('iPayAfrica');
    }

    public function ipaytest() {
		/*
		 * ref url https://dev.ipayafrica.com/C2B.html
		 * options
		 * live -- mode of ipay
		 * oid -- Order ID
		 */	
		
		$orderID = 5;
		$amount = 45.00;//always in amount format
		$invoiceNo = FALSE;//ipay will take orderID if this is false
		$vendorId = '**********'; // IPay vendor ID
		$hashKey = '**********'; // Generated Hash key
		$ipaymode = 0; //0=>demo 1=>live
		$currency = 'USD'; // currency
		// Payloads for return values
		$payload1 = '**********'; 
		$payload2 = '**********';
		$payload3 = '**********'; 
		$payload4 = '**********';
		//customer details
		$custname = 'xxxxxxxxxxxxx';
		$custemail = 'xxxxxxxxx@xxxxx.xxx';
		$returnsuccessUrl = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];//change this to your success url
		$returnfailedUrl = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];//change this to your failure url
		$callbackmode = 0; //0=>http mode,1=>data stream of comma separated values,2=>for a json data stream.
		$sendMailtoCustomer = TRUE;
		$p = $this->ipayafrica; //ipayAfricaLib
		if($ipaymode == 0){
		$p->isDemo();			
		}
		$p->usingVendorId($vendorId, $hashKey);
		$p->withCallback($returnsuccessUrl,$returnfailedUrl,$callbackmode);
		$p->usingCurrency('USD');
		$p->withPayloads($payload1,$payload2,$payload3,$payload4);
		$p->withCustomer($custname,$custemail,$sendMailtoCustomer);
		$p->transact($amount,$orderID,$invoiceNo);
    }

    public function ipaysuccess() {
	print_r($_POST);
	exit;
    }
    public function ipayfail() {
	print_r($_POST);
	exit;
    }

}
