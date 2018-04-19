<?php

namespace Crazymeeks\MailChimp\Subcribers;

use Closure;

trait ManageSubscribers
{
	
	/**
	 * The expected required key when adding new subscriber from the list
	 *
	 * @var array
	 */
	protected static $required_subscriber_params_keys = array('email', 'status', 'firstname', 'lastname');

	/**
	 * The keys expected when adding new subscriber from the list
	 * 
	 * @var array
	 */
	protected $subscriber_params = array();

	/**
	 * The additional subscriber parameters.
	 *
	 * The developers can add new parameters when adding
	 * new subscribers in the list.
	 *
	 * @var array
	 */
	protected $additional_subscriber_params_keys = array();

	/**
	 * MailChimp API manage subscriber post url
	 *
	 * @link https://developer.mailchimp.com/documentation/mailchimp/guides/manage-subscribers-with-the-mailchimp-api/
	 *
	 * @var string
	 * 'https://us16.api.mailchimp.com/3.0/lists/ec11c06ab4/members/'
	 */
	protected $subscriber_post_url = 'api.mailchimp.com';

	/**
	 * Subscriber list key. Go to your mailchimp account then Lists > Settings to get the key
	 *
	 * @var string
	 */
	protected $list_key;

	/**
	 * MailChimp Subscription Status
	 * @link https://developer.mailchimp.com/documentation/mailchimp/guides/manage-subscribers-with-the-mailchimp-api/
	 */
	protected static $subscription_status = array(
		self::SUBSCRIBED, self::UNSUBSCRIBED,
		self::PENDING, self::CLEANED,
	);

	/**
	 * Add subscriber to Mailchimp
	 *
	 * @param
	 */
	public function addSubscriber()
	{

	}

	/**
	 * Add subscribers to Mailchimp
	 *
	 * @param array $subscribers           The subscriber to add
	 * @param \Closure $callback           The callback
	 *
	 * @return $this
	 */
	public function addSubscribers(array $subscribers, Closure $callback = null)
	{

		$this->setSubscriberParams($subscribers);

		if(!is_null($callback) && $callback instanceof Closure){
			call_user_func($callback, $this);
		}
		return $this;
	}

	/**
	 * Add additional parameter.
	 *
	 * This is useful if you want add new data when adding subscribers
	 * to mailchimp on the fly.
	 * 
	 * @param array $params      The array of parameters to add. Parameter should be key/value pairs.
	 * @see MailChimp api
	 * @link https://developer.mailchimp.com/documentation/mailchimp/guides/manage-subscribers-with-the-mailchimp-api/
	 *
	 * @return void
	 * @throws Exception
	 */
	public function subscriberAddParams(array $params)
	{
		if(!is_array($params)){
			throw new \Exception('Invalid parameter. Array expected, ' . gettype($params) . ' given.');
		}
		
		if(($key = key($params)) === 0 ){

			throw new \Exception('Invalid array key. Key must be same with the mailchimp expected key.');
		}

		$this->additional_subscriber_params_keys = $params;

		$this->subscriber_params = array_merge($this->subscriber_params, $params);

		return $this;
	}

	/**
	 * Getter: The added subscriber parameters
	 *
	 * @return array
	 */
	public function getSubscriberAddedParams()
	{
		return $this->additional_subscriber_params_keys;
	}


	/**
	 * Setter: Subscriber parameters
	 *
	 * @param array $subscribers         The array of subscriber parameters
	 * @see MailChimp api
	 * @link 
	 *
	 * @return $this
	 */
	public function setSubscriberParams(array $subscribers)
	{
		foreach($subscribers as $key => $subscriber){
			if(!array_key_exists($key, array_flip(self::$required_subscriber_params_keys))){
				throw new \Exception('Minimum required parameters is/are missing. Please make sure these ' . implode(',', self::$required_subscriber_params_keys) .' are supplied');
			}
		}
		$this->subscriber_params = $subscribers;
		return $this;
	}

	/**
	 * Getter: Get mailchimp list of subscription status
	 *
	 * @return array
	 */
	public function getSubscribeStatus()
	{
		return static::$subscription_status;
	}

	/**
	 * Final execution. Add subscriber to MailChimp
	 *
	 * @return array
	 */
	public function add()
	{


		$headers = array(
	        'Authorization: apikey ' . $this->getApiKey(),
	        'Accept: application/json'
	    );

	    // $subscriber_post_url = 'api.mailchimp.com';
	    // 'https://us16.api.mailchimp.com/3.0/lists/ec11c06ab4/members/'
	    $url = 'https://' . $this->getDataCenter() . '.' . $this->subscriber_post_url . '/' . $this->version . '/lists/' . $this->getListKey() . '/members/';

		$curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => 1,
            CURLOPT_CUSTOMREQUEST => strtoupper('post'),
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $encodedBody = $this->getSubscriberParams();
        curl_setopt($curl, CURLOPT_POSTFIELDS, $encodedBody);
        $headers = array_merge($headers, ['Content-Type: application/json']);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);

        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);

		// Get the response body
		$body = json_decode(substr($response, $header_size));

        curl_close($curl);

        return $httpcode == 200 ? array('code' => $httpcode, 'message' => 'New subscriber has been successfully added.') : array('code' => $httpcode, 'message' => $body->detail, 'errors' => $body->errors);
	}

	/**
	 * Getter: Lists key
	 */
	public function getListKey()
	{
		$keys = $this->getMailChimpKeys();
		if(!array_key_exists('mail_list_key', $keys)){
			throw new \Exception('Cannot add new subscriber. Lists key is missing');
		}

		return $keys['mail_list_key'];
	}

	/**
	 * Getter: Subscriber param
	 *
	 * @return json_encode
	 */
	public function getSubscriberParams()
	{

		$sp = array(
			'email_address' => $this->subscriber_params['email'],
			'status'        => $this->subscriber_params['status'],
			'merge_fields'  => array_merge(
				array('FNAME' => $this->subscriber_params['firstname'], 'LNAME' => $this->subscriber_params['lastname']),
				$this->subscriber_params),
		);

		unset($this->subscriber_params['email']);
		unset($this->subscriber_params['status']);
		unset($this->subscriber_params['firstname']);
		unset($this->subscriber_params['lastname']);

		return json_encode((count($this->subscriber_params)) > 0 ? array_merge($sp, $this->subscriber_params) : $sp);
		
	}
}