<?php defined('_JEXEC') or die;

/**
 * File       googleoauth.php
 * Created    2/7/14 3:40 AM
 * Author     Matt Thomas | matt@betweenbrain.com | http://betweenbrain.com
 * Support    https://github.com/betweenbrain/
 * Copyright  Copyright (C) 2014 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v3 or later
 */
class JElementGoogleoauth extends JElement
{

	/**
	 * Element name
	 *
	 * @access       protected
	 * @var          string
	 */
	var $_name = 'Googleoauth';

	/**
	 * @param null $parent
	 */
	function __construct($parent = null)
	{
		$this->parent       = $parent;
		$this->db           = JFactory::getDBO();
		$this->redirectUri  = JURI::base();
		$this->refreshToken = 'refresh.token';
		$this->accessToken  = 'access.token';
	}

	/**
	 * @param $name          unique name of the parameter, from the name argument.
	 * @param $value         current value of the parameter.
	 * @param $node          JSimpleXMLElement object representing the <param> element.
	 * @param $control_name  parameter type from the type argument (eg. 'category' or 'newparm')
	 *
	 * @return bool|mixed
	 */
	function fetchElement($name, $value, &$node, $control_name)
	{
		// Dynamically set plugin parameters as class properties
		$element = $node->attributes('element');
		$this->fetchPluginParameters($element);

		$cacheDir = $node->attributes('cachedir');
		$cacheDir = JPATH_SITE . '/cache/' . $cacheDir;
		$this->checkCacheDir($cacheDir);

		if (!file_exists($cacheDir . '/' . $this->accessToken))
		{
			return $this->renderAuthLink();
		}
	}

	private function checkCacheDir($cacheDir)
	{
		if (!is_dir($cacheDir))
		{
			mkdir($cacheDir);
		}
	}

	private function fetchPluginParameters($element)
	{
		// Fetch parameters via database query
		$this->db = JFactory::getDBO();
		$sql      = 'SELECT ' . $this->db->nameQuote('params') .
			' FROM ' . $this->db->nameQuote('#__plugins') .
			' WHERE ' . $this->db->nameQuote('element') . ' = ' . $this->db->quote($element);
		$this->db->setQuery($sql);
		$params = $this->db->loadResult();
		$params = parse_ini_string($params);

		foreach ($params as $name => $value)
		{
			$this->{$name} = $value;
		}

	}

	private function renderAuthLink()
	{
		$parameters = array(
			'client_id'       => $this->googleClientId,
			'redirect_uri'    => $this->redirectUri,
			'scope'           => 'https://www.googleapis.com/auth/yt-analytics.readonly',
			'response_type'   => 'code',
			'approval_prompt' => 'force',
			'access_type'     => 'offline'
		);

		$url   = 'https://accounts.google.com/o/oauth2/auth?';
		$query = http_build_query($parameters);

		return '<a href="' . $url . $query . '">Get Authorization Code</a><br/><br/><br/>';
	}
}