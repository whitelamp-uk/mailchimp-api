<?php
// blotto2 config and functions
require '/home/blotto/config/ffd.cfg.php';
require '/home/dom/blotto/blotto2/scripts/functions.php';

$send = true;
$received = false;

// Get an instance of this class
$api = email_api ();

// Set the API key
$api->keySet (MAILCHIMP_MARKETING_KEY.' '.MAILCHIMP_MARKETING_SERVER.' '.MAILCHIMP_TRANSACTIONAL_KEY);

//$response = $api->ping(); echo $response."\n";

$data = ['firstname' => 'Screaming Lord Sutch', 'CHARITY' => 'Bognor Donkey Sanctuary', 'amount' => '£43.40',];

$campaign_ref = "668647d550"; // web_id is 358693 which is what you see in the 'control panel' 668647d550

//$r=$api->ping(); echo $r;
if ($send) {
	$emref = $api->send ($campaign_ref,'dom.latter@thefundraisingfoundry.com',$data); // template, recipient, data
	if (!$emref) {
		echo $api->errorLast;
	} else {
		echo "$emref is email ref";
	}
}

if ($received) {
	//$r = $api->received('', 'c2cccb8cdd1d4c5d8cb419deac914482'); // good
	//$r = $api->received('', '0297a4355ce14d6887817f444c0a673f'); // invalid email (dom.latter @thefundraisingfoundry.com) - server excpetion
	//$r = $api->received('', '32d3540bef8f4b9aad99c001c3bda3d4'); // recipient-domain-mismatch dom.latter@thefundraisingfoundry.co - mail rejected
	$r = $api->received('', '32d3540bef8f4b9aad99c001c3bda3'); // invalid ref-domain-mismatch dom.latter@thefundraisingfoundry.co - server exeption
	echo "\nresponse:\n";
	print_r($r);
}