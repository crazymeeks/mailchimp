<?php

/*
 * This file is part of the MailChimp package.
 *
 * (c) Jefferson Claud <jeffclaud17@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Crazymeeks\MailChimp;

use JsonSerializable;
use Crazymeeks\MailChimp\Subcribers\ManageSubscribers;
class MailChimp implements JsonSerializable
{
	

	use ManageSubscribers;


	/**
	 * This address is on the list and ready to receive email.
	 * You can only send campaigns to 'subscribed' addresses.
	 * 
	 * @var string
	 */
	const SUBSCRIBED = 'subscribed';

	/**
	 * This address used to be on the list but isn't anymore
	 *
	 * @var string
	 */
	const UNSUBSCRIBED = 'unsubscribed';

	/**
	 * This address requested to be added with double-opt-in but hasn't confirmed their
	 * subscription yet.
	 *
	 * @var string
	 */
	const PENDING = 'pending';

	/**
	 * This address bouned and has been removed from the list
	 *
	 * @var string
	 */
	const CLEANED = 'cleaned';
	

	/**
	 * The mailchimp api key
	 *
	 * @var string
	 */
	protected $apikey;

	/**
	 * MailChimp API version
	 * 
	 * @var string
	 */
	protected $version = '3.0';

	/**
	 * The MailChimp account DataCenter. Something like us16. Look at the end of your api key
	 *
	 * @var string $dc
	 */
	protected $dc;

	/**
	 * List of mailchimp (api) key
	 *
	 * @var array $mailchimp_keys
	 */
	protected $mailchimp_keys = array('mail_list_key');


	/**
	 * Constructor
	 *
	 * @param string $apikey      The MailChimp API key
	 * @param string $dc          The MailChimp DataCenter
	 * @param array $options      The key options. Can be mail list key, etc.
	 *                            For mail list key(adding new subscriber), the `mail_list_key` is expected
	 *
	 * @return void
	 */
	public function __construct($apikey, $dc, array $options = array())
	{
		
		$this->_init($apikey, $dc, $options);
	}

	private function _init($apikey, $dc, $options)
	{
		$this->setApiKey($apikey);
		$this->setDataCenter($dc);
		$this->setOptions($options);
	}

	/**
	 * Set MailChimp DataCenter.
	 *
	 * @param string $dc
	 *
	 * @return void
	 */
	public function setDataCenter($dc)
	{
		$this->dc = $dc;
	}

	public function getDataCenter()
	{
		return $this->dc;
	}

	/**
	 * Set additional options like Mail List key, etc
	 *
	 * @param array $options         The array of options
	 *
	 * @return void
	 */
	public function setOptions(array $options)
	{
		$this->mailchimp_keys = $options;
	}

	/**
	 * Getter: MailChimp keys
	 *
	 * @return array
	 */
	public function getMailChimpKeys()
	{
		return $this->mailchimp_keys;
	}

	/**
	 * @implemented
	 */
	public function jsonSerialize()
	{
		return $this->subscriber_params;
	}

	/**
	 * Setter: Set MailChimp API key
	 *
	 * @param string $apikey           The mailchimp api key
	 *
	 * @return void
	 */
	public function setApiKey($apikey)
	{
		$this->apikey = $apikey;
	}

	/**
	 * Getter: Get MailChimp API key
	 *
	 * @return string
	 */
	public function getApiKey()
	{
		return $this->apikey;
	}


}