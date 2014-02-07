<?php defined('_JEXEC') or die;

/**
 * File       diagnostic.php
 * Created    2/12/13 11:59 AM
 * Author     Matt Thomas
 * Website    http://betweenbrain.com
 * Email      matt@betweenbrain.com
 * Support    https://github.com/betweenbrain/
 * Copyright  Copyright (C) 2012 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v3 or later
 */
class JElementDiagnostic extends JElement
{

	/**
	 * Element name
	 *
	 * @access       protected
	 * @var          string
	 */
	var $_name = 'Diagnostic';

	function __construct($parent = null)
	{
		$this->app = JFactory::getApplication();
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

		$element    = $node->attributes('element');
		$cacheDir   = $node->attributes('cachedir');
		$cacheDir   = JPATH_SITE . '/cache/' . $cacheDir;
		$cacheFiles = $node->attributes('cachefiles');

		// Fetch parameters via database query
		$db  = JFactory::getDBO();
		$sql = 'SELECT ' . $db->nameQuote('params') .
			' FROM ' . $db->nameQuote('#__plugins') .
			' WHERE ' . $db->nameQuote('element') . ' = ' . $db->quote($element);
		$db->setQuery($sql);
		$params = $db->loadResult();
		$params = parse_ini_string($params);

		// Initialize variables
		$result   = null;
		$messages = null;
		$errors   = null;

		if ($params['showDiagnostic'] == 1)
		{

			// Check caching parameter
			if (isset($params['cache']) && $params['cache'] == 1)
			{
				$messages[] = ucfirst($element) . 'caching parameter is enabled.';
			}

			// Check cache files
			$cacheFiles = explode(',', $cacheFiles);
			foreach ($cacheFiles as $cacheFile)
			{
				$cacheFile = $cacheDir . '/' . $cacheFile;

				if (file_exists($cacheFile))
				{
					$cacheAge   = date("F d Y H:i:s", filemtime($cacheFile));
					$messages[] = "Cache file at $cacheFile exists.";
					$messages[] = "Cache file was created $cacheAge.";
				}
				else
				{
					$errors[] = "Cache file at $cacheFile does not exist!";
				}
			}

			if (isset($params['cacheage']))
			{
				$messages[] = "Cache lifetime is " . $params['cacheage'] . " minute(s).<br/>";
			}

			if (is_dir($cacheDir))
			{
				$messages[] = "Cache directory at $cacheDir exists.";
				if (is_writable($cacheDir))
				{
					$messages[] = "Cache directory at $cacheDir is writable.";
				}
			}
			else
			{
				$errors[] = "Cache directory at $cacheDir does not exist!";
				if (!is_writable($cacheDir))
				{
					$errors[] = "Cache directory at $cacheDir is not writable!";
				}
			}

			if ($messages[0])
			{
				foreach ($messages as $message)
				{
					$this->app->enqueueMessage($message);
				}
			}

			if ($errors[0])
			{
				foreach ($errors as $error)
				{
					$this->app->enqueueMessage($error, 'error');
				}
			}

			if ($result)
			{
				return print_r($result, false);
			}

			return false;
		}

		return false;
	}
}