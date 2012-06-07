<?php

/**
 * Battlefield 3 Container
 * 
 * Provides the class definition for the abstract container class.
 * 
 * @author		Blake Harley <contact@blakeharley.com>
 * @version		1.3
 * @package		BattlelogApi
 * @copyright	Copyright (c) 2011, Blake Harley
 * @license		http://creativecommons.org/licenses/by/3.0/
 * @since		1.3
 */

/**
 * Provides an interface for producing container classes
 * with array access quickly.
 * 
 * @author		Blake Harley <contact@blakeharley.com>
 * @package		BattlelogApi
 * @since		1.3
 */
abstract class BF3Container implements ArrayAccess
{
	/**
	 * @access protected
	 * @var string
	 * @since 1.3
	 */
	protected $_id = null;
	
	/**
	 * @access protected
	 * @var array
	 * @since 1.3
	 */
	protected $_data = null;
	
	/**
	 * @access protected
	 * @var BattlelogApi
	 * @since 1.3
	 */
	protected $_api = null;
	
	/**
	 * @access protected
	 * @var string
	 * @since 1.3
	 */
	protected $_accessUrl = null;
	
	/**
	 * Stores the API and the ID. Will not load the container's data until accessed
	 * or lazy loading is disabled.
	 * 
	 * @access public
	 * @param BattlelogApi $api
	 * @param string $id The id for this container
	 * @param bool $load Whether or not to load the data contents right away
	 * @since 1.3
	 */
	public function __construct($api, $id, $load = false)
	{
		if ($api == null || !($api instanceof BattlelogApi))
		{
			throw new BattlelogException('Must be passed a BattlelogApi instace');
		}
		
		$this->_api = $api;
		$this->_id = $id;
		
		if ($load)
		{
			$this->_load();
		}
	}
	
	/**
	 * Parses the raw Json data into something useful.
	 * 
	 * @access protected
	 * @param string $data
	 * @return array The final data
	 * @since 1.3
	 */
	abstract protected function _parseData($data);
	
	/**
	 * Loads the data into the container.
	 * 
	 * @access protected
	 * @param bool $force Whether or not to force load
	 * @since 1.3
	 */
	protected function _load($force = false)
	{
		if ($force)
		{
			$this->_data = null;
		}
		
		if ($this->_data === null)
		{
			$url = str_replace('[[ID]]', $this->_id, $this->_accessUrl);
			$data = $this->_api->getUrl($url);
			
			if (strlen($data) < 1)
			{
				throw new BattlelogException('Problem connecting to Battlelog');
			}
			
			$this->_data = $this->_parseData($data);
		}
	}
	
	/**
	 * Since PHP <5.3 doesn't support ternary operators without the middle paramter,
	 * I've created this silly little helper method to cut down on code duplication.
	 * Esentially, this method will return $return if $var is null. Otherwise it will
	 * return $var.
	 * 
	 * @access protected
	 * @param mixed $var The value to short ternary against
	 * @param mixed $return The return in the case that $var is null
	 * @return mixed The value of $var if it's not null, otherwise returns the value of $return
	 * @since 1.3
	 */
	protected function _ternShort($var, $return = 0)
	{
		if ($var == null)
			return $return;
		
		return $var;
	}
	
	/**
	 * Gets the value of the given location.
	 * 
	 * @access public
	 * @param string $name The location
	 * @return mixed The value stored at the given position
	 * @since 1.0-beta
	 */
	public function get($name)
	{
		$this->_load();
		return isset($this->_data[$name]) ? $this->_data[$name] : null;
	}
	
	/**
	 * Sets the value of the given location.
	 * 
	 * @access public
	 * @param string $name The location
	 * @param mixed $value The new value
	 * @since 1.0-beta
	 */
	public function set($name, $value)
	{
		$this->_load();
		$this->_data[$name] = $value;
	}
	
	/**
	 * @access public
	 * @param string $offset
	 * @return mixed The contents of $this->$offset
	 * @since 1.0-beta
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}
	
	/**
	 * @access public
	 * @param string $offset
	 * @param mixed $value
	 * @since 1.0-beta
	 */
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}
    
	/**
	 * @access public
	 * @param string $offset
	 * @return bool Whether or not the ArrayAccess offset exists
	 * @since 1.0-beta
	 */
	public function offsetExists($offset)
	{
		$this->_load();
		return array_key_exists($offset, $this->_data);
	}
	
	/**
	 * This method is not supported by this class.
	 * 
	 * @access public
	 * @param string $offset
	 * @since 1.0-beta
	 */
	public function offsetUnset($offset)
	{
		throw new BattlelogException('This method is unsupported');
	}
	
	/**
	 * Dumps all of this container's data.
	 * 
	 * @access public
	 * @return array All of data in the data container
	 * @since 1.0-beta
	 */
	public function debugData()
	{
		$this->_load();
		return $this->_data;
	}
}
