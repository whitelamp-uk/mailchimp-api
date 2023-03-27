<?php
// blotto2 config and functions
require '/home/blotto/config/ffd.cfg.php';
require '/home/dom/blotto/blotto2/scripts/functions.php';

// Get an instance of this class
$api = email_api ();

// Set the API key
$api->keySet (MAILCHIMP_KEY);

//$response = $api->ping();
//echo $response."\n";

$data = ['firstname' => 'Screaming Lord Sutch', 'CHARITY' => 'Bognor Donkey Sanctuary', 'amount' => '£43.40',];

$api->send ('hello','dom.latter@thefundraisingfoundry.com',$data); // template, recipient, data


