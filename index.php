<?php

require_once 'vendor/autoload.php';

use Crazymeeks\MailChimp\MailChimp;


$mailchimp = new MailChimp('youkey', 'dc', ['mail_list_key' => 'your_mail_list_key']);

## See usage below

# Adding additional parameter to the request
# This is useful if developers wants to add
# new parameter(additional parameter required by MailChimp)
# without modifying the core code.
# Just pass closure in the second parameter of MailChimp::addSubscribers()

/*
$response = $mailchimp->addSubscribers(
	[
		'email'     => 'joshua.reyes+b@nuworks.ph',
		'status'    => 'subscribed',
		'firstname' => 'Joshua Clifford',
		'lastname'  => 'Reyes',
	], function($q){
		$q->subscriberAddParams(['test' => 'tests']);
	}
)->add();
*/

# Normal request structure.
# Here, we just wanted to add new subscriber in the list

/*
$response = $mailchimp->addSubscribers(
	[
		'email'     => 'joshua.reyes+c@nuworks.ph',
		'status'    => 'subscribed',
		'firstname' => 'Joshua Clifford',
		'lastname'  => 'Reyes',
	]
)->add();
*/

## The response is an array
# Success, the response structure would be
/*
Array
(
    [code] => 200
    [message] => New subscriber has been successfully added.
)*/


# If failed, the response structure would be

/*
Array
(
    [code] => 400
    [message] => The resource submitted could not be validated. For field-specific details, see the 'errors' array.
    [errors] => Array
        (
            [0] => stdClass Object
                (
                    [field] => merge_fields
                    [message] => Schema describes object, array found instead
                )

        )

)*/

# To see the response:
/*
echo "<pre>";
print_r($response);
*/