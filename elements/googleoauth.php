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
		$element = $node->attributes('element');

		$this->fetchPluginParameters($element);

	}

	private function fetchPluginParameters($element)
	{
		// Fetch parameters via database query
		$this->db  = JFactory::getDBO();
		$sql = 'SELECT ' . $this->db->nameQuote('params') .
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
}