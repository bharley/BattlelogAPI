<?php
/**
 * Copyright (c) 2011-2012 Blake Harley
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 * This software is licensed under the MIT license. See LICENSE
 * for more information.
 */

/**
 * Provides an interface for producing container classes with array access
 * quickly.
 * 
 * @package  BattlelogApi
 * @author   Blake Harley <contact@blakeharley.com>
 * @since    1.3
 * @abstract 
 * @uses     ArrayAccess
 */
abstract class BattlelogContainer implements ArrayAccess
{
	/**
	 * @since 1.3
	 * @var   array
	 */
	protected $_data = null;
	
	/**
	 * @since 1.3
	 * @var   BattlelogApi
	 */
	protected $_api = null;
	
	/**
	 * @since 2.0
	 * @var   array
	 */
	protected $_loaders = array();
	
	/**
	 * Stores the API and the ID. Will not load the container's data until accessed
	 * or lazy loading is disabled.
	 * 
	 * @since  1.3
	 * @param  BattlelogApi $api
	 * @param  string $id The id for this container
	 * @param  bool $load Whether or not to load the data contents right away
	 * @throws BattlelogException If the API is not an instance of BattlelogApi
	 */
	public function __construct($api, $load = false)
	{
		if ($api == null || !($api instanceof BattlelogApi))
		{
			throw new BattlelogException('Must be passed an instace of BattlelogApi');
		}
		
		$this->_api = $api;
		$this->_initLoaders();
		
		if ($load)
		{
			$this->_load();
		}
	}
	
	/**
	 * This method should set up all of the loaders for this container. All of
	 * the loaders will be iterated over when this class is loaded.
	 * 
	 * @since    2.0
	 * @abstract
	 */
	abstract protected function _initLoaders();
	
	/**
	 * Adds the given loader to the list of loaders.
	 * 
	 * @since  2.0
	 * @param  BattlelogLoader $loader 
	 * @throws BattlelogException
	 */
	protected function _addLoader($loader)
	{
		if (!$loader instanceof BattlelogLoader)
		{
			throw new BattlelogException('Loader must be of type BattlelogLoader');
		}
		
		$this->_loaders[] = $loader;
	}
	
	/**
	 * Loads the data into the container.
	 * 
	 * @since  1.3
	 * @param  bool $force Whether or not to force load regardless of the cache
	 */
	protected function _load($force = false)
	{
		if ($force)
		{
			$this->_data = null;
		}
		
		if ($this->_data === null)
		{
			$data = array();
			
			foreach ($this->_loaders as $loader)
			{
				$data = array_merge($data, $loader->getData());
			}
		}
	}
	
	/**
	 * Since PHP <5.3 doesn't support ternary operators without the middle paramter,
	 * I've created this silly little helper method to cut down on code duplication.
	 * Esentially, this method will return $return if $var is null. Otherwise it will
	 * return $var.
	 * 
	 * @since  1.3
	 * @param  mixed $var The value to short ternary against
	 * @param  mixed $return The return in the case that $var is null
	 * @return mixed The value of $var if it's not null, otherwise returns the value of $return
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
	 * @since  1.0-beta
	 * @param  string $name The location
	 * @return mixed The value stored at the given position
	 */
	public function get($name)
	{
		$this->_load();
		return isset($this->_data[$name]) ? $this->_data[$name] : null;
	}
	
	/**
	 * Sets the value of the given location.
	 * 
	 * @since 1.0-beta
	 * @param string $name The location
	 * @param mixed $value The new value
	 */
	public function set($name, $value)
	{
		$this->_load();
		$this->_data[$name] = $value;
	}
	
	/**
	 * @since  1.0-beta
	 * @param  string $offset
	 * @return mixed The contents of $this->$offset
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}
	
	/**
	 * @since 1.0-beta
	 * @param string $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}
    
	/**
	 * @since  1.0-beta
	 * @param  string $offset
	 * @return bool Whether or not the ArrayAccess offset exists
	 */
	public function offsetExists($offset)
	{
		$this->_load();
		return array_key_exists($offset, $this->_data);
	}
	
	/**
	 * This method is not supported by this class.
	 * 
	 * @since  1.0-beta
	 * @param  string $offset
	 * @throws BattlelogException This method is not supported
	 */
	public function offsetUnset($offset)
	{
		throw new BattlelogException('This method is unsupported');
	}
	
	/**
	 * Dumps all of this container's data.
	 * 
	 * @since  1.0-beta
	 * @return array All of data in the data container
	 */
	public function getData()
	{
		$this->_load();
		return $this->_data;
	}
}

/**
 * This class will serve as the base for a load component. Since any given
 * Battlelog container might need to query multiple URLs to get all of the
 * information it needs to populate itself, it is easy to create a parser for
 * each page this way and then iterate over them at load time.
 * 
 * @package  BattlelogApi
 * @author   Blake Harley <contact@blakeharley.com>
 * @since    2.0
 * @abstract
 */
abstract class BattlelogLoader
{
	/**
	 * @since 2.0
	 * @var   string
	 */
	protected $_id = '';
	
	/**
	 * @since 2.0
	 * @var   string
	 */
	protected $_uri = '';
	
	/**
	 * @since 2.0
	 * @var   BattlelogApi
	 */
	protected $_api = null;
	
	/**
	 * Creates a new loader instance using the given uri identification and API
	 * instance.
	 * 
	 * @since 2.0
	 * @param string $id
	 * @param BattlelogApi $api 
	 */
	public function __construct($id, $api = null)
	{
		$this->_id = (string) $id;
		$this->setApi = $this->setApi($api);
		$this->_init();
	}
	
	/**
	 * This empty method allows child classes to implement construction
	 * functionality without having to override and duplicate the parent's
	 * constructor signature.
	 * 
	 * @since 2.0
	 */
	protected function _init()
	{
		// Intentionally left empty
	}
	
	/**
	 * Sets the API.
	 * 
	 * @since  2.0
	 * @param  BattlelogApi $api The instance of the Api to attach to this
	 * @throws BattlelogException If the API is not an instance of BattlelogApi
	 */
	public function setApi($api)
	{
		if ($api && !$api instanceof BattlelogApi)
		{
			throw new BattlelogException('API must be of type BattlelogApi');
		}
		
		$this->_api = $api;
	}
	
	/**
	 * This method should parse the data from the Uri this loader is reponsible
	 * for and return an array of relevant information.
	 * 
	 * @since    2.0
	 * @abstract
	 * @param    string $data
	 * @return   array The data parsed out of this page
	 */
	abstract protected function _parse($data);
	
	/**
	 * Returns the parsed contents of the page this loader is responsible for.
	 * 
	 * @since  2.0
	 * @return array The values parsed from this location
	 * @throws BattlelogException If the API hasn't been set yet
	 */
	public function getData()
	{
		if (!$this->_api)
		{
			throw new BattlelogException('API not set');
		}
		
		$uri = str_replace('[[ID]]', $this->_id, $this->_uri);
		$data = $this->_api->getUri($uri);
		
		return $this->_parse($data);
	}
}