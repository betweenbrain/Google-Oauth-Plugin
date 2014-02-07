<?php defined('_JEXEC') or die;

/**
 * File       googleoauth.php
 * Created    2/7/14 3:40 AM
 * Author     Matt Thomas | matt@betweenbrain.com | http://betweenbrain.com
 * Support    https://github.com/betweenbrain/
 * Copyright  Copyright (C) 2014 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v3 or later
 */
class PlgSystemGoogleoauth extends JPlugin
{
	/**
	 * @param $subject
	 * @param $config
	 *
	 * https://cloud.google.com/console/project => APIs & auth => Credentials
	 * Create new client ID as web application
	 * Generate new server key
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->app                = JFactory::getApplication();
		$this->plugin             =& JPluginHelper::getPlugin('system', 'googleoauth');
		$this->params             = new JParameter($this->plugin->params);
		$this->googleApiKey       = $this->params->get('googleApiKey');
		$this->googleClientId     = $this->params->get('googleClientId');
		$this->googleClientSecret = $this->params->get('googleClientSecret');
		$this->accessToken        = JPATH_SITE . '/cache/plg_googleoauth/access.token';
		$this->refreshToken       = JPATH_SITE . '/cache/plg_googleoauth/refresh.token';
		$this->redirectUri        = JURI::base();
	}

	function onAfterRoute()
	{
		if ($this->app->isAdmin())
		{
			$code = JRequest::getVar('code');
			if (isset($code))
			{
				$url = 'https://accounts.google.com/o/oauth2/token';

				$parameters = array(
					'code'          => $code,
					'client_id'     => $this->googleClientId,
					'client_secret' => $this->googleClientSecret,
					'redirect_uri'  => $this->redirectUri,
					'grant_type'    => 'authorization_code'
				);

				$query = http_build_query($parameters);

				//open connection
				$curl = curl_init();

				// Make a POST request to get bearer token
				curl_setopt_array($curl, Array(
					CURLOPT_URL            => $url,
					CURLOPT_POST           => true,
					CURLOPT_POSTFIELDS     => $query,
					CURLOPT_RETURNTRANSFER => 1
				));

				//execute post
				$response = curl_exec($curl);
				$response = json_decode($response);

				//close connection
				curl_close($curl);

				if (isset($response->access_token))
				{
					$this->app->enqueueMessage(JText::_('PLG_SYSTEM_GOOGLEOAUTH_GOOGLE_ACCESS_TOKEN_RECIEVED_MESSAGE'), 'message');
					file_put_contents($this->accessToken, $response->access_token);
				}
				if (isset($response->refresh_token))
				{
					$this->app->enqueueMessage(JText::_('PLG_SYSTEM_GOOGLEOAUTH_GOOGLE_REFRESH_TOKEN_RECIEVED_MESSAGE'), 'message');
					file_put_contents($this->refreshToken, $response->refresh_token);
				}
			}
		}
	}
}
