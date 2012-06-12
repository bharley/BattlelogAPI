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
 * You can change this if you want. Some user agents have been blocked, so it is
 * best to stick with impersonating a real browser.
 * For the curious, the current user agent is Mozilla Firefox 6.0.2 on Windows 7
 * 64-bit.
 * 
 * @package BattlelogApi
 * @since   1.2-pre
 * @var     string
 */
define('BLA_USER_AGENT', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2');

/**
 * This factory class will return filled objects for various requested Battlelog
 * information and provide a layer for retrieving content from Battlelog.
 * 
 * @package BattlelogApi
 * @author  Blake Harley <contact@blakeharley.com>
 * @since   1.0-beta
 */
class BattlelogApi
{
	/**
	 * @since 2.0
	 * @var   boolean
	 */
	protected $_useGzip = true;
	
	/**
	 * @since 1.3
	 * @var   BattlelogLanguage
	 */
	protected $_lang = null;
	
	/**
	 * Sets up autoloading.
	 * 
	 * @since 1.0-beta
	 */
	public function __construct()
	{
		// Register our autoloader function with PHP
		spl_autoload_register(array($this, 'autoload'));
		
		// Start the language translation class
		$this->_lang = new BattlelogLanguage($this);
	}
	
	/**
	 * Creates and returns a BattlelogBF3Soldier using the given soldier's ID.
	 * 
	 * @since  1.0-beta
	 * @param  string|int $soldierId The ID of the solider to fetch
	 * @return BattlelogBF3Soldier The requested soldier
	 */
	public function getBF3Soldier($soldierId)
	{
		return new BattlelogBF3Soldier($soldierId, $this);
	}
	
	/**
	 * Fetches the contents of the requested URI. Will fetch from cache if available.
	 * 
	 * @since  2.0
	 * @param  string $uri The portion of the URL after the hostname that needs to be fetched
	 * @return string The contents of the URI
	 */
	public function getUri($uri)
	{
		// TODO: Instance caching
		// if uri in cache
		//   return cache entry
		// else
		return $this->_curi($uri);
	}
	
	/**
	 * Fetches the contents of the given URI.
	 * 
	 * @since  2.0
	 * @param  string $uri The URI to fetch with cUrl
	 * @return string The contents of the URI
	 */
	protected function _curi($uri)
	{
		// Although we claim to only deal with URIs, we also accept URLs
		if (strpos($uri, 'http') === 0)
		{
			$url = $uri;
		}
		else
		{
			$url = "http://battlelog.battlefield.com/$uri";
		}
		
		// Initialize and set cUrl options
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Expect:',
			'User-Agent: ' . BLA_USER_AGENT,
			'Accept: */*',
			'Accept-Language: en-us,en;q=0.5',
			'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
			'X-Requested-With: XMLHttpRequest',
			'X-AjaxNavigation: 1'
		));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		// Use compression?
		if ($this->_useGzip)
		{
			curl_setopt($curl, CURLOPT_ENCODING, "gzip");
		}
		
		// Get the data and close cUrl
		$data = curl_exec($curl);
		curl_close($curl);
		
		return $data;
	}
	
	/**
	 * If true, this API will use gzip compression when accessing pages with
	 * cUrl. This will decrease bandwidth usage at the cost of increased CPU
	 * usage.
	 * 
	 * @since 2.0
	 * @param boolean $useGzip Sets whether or not cUrl should use gzip compression (default true)
	 */
	public function useGzip($useGzip = true)
	{
		$this->_useGzip = (boolean) $useGzip;
	}
	
	/**
	 * Runs the given nameplate through the BF3 locale file to get the actual
	 * name of the item.
	 * 
	 * @param  string $string The nameplate to run through the translator
	 * @return mixed The translated id
	 * @since  1.3
	 */
	public function translate($string)
	{
		return $this->_lang[$string];
	}
	
	/**
	 * This method will get registered as an autoloader with PHP so that the
	 * BattlelogApi can be successfully executed by only including one file.
	 * This could also decrease file access if you use this instead of including
	 * every library file.
	 * Only 
	 * 
	 * @since 2.0
	 * @param string $classname The name of the class that needs to be loaded
	 */
	public function autoload($classname)
	{
		// Make sure this class belongs to this library, otherwise don't bother
		if (strpos($classname, 'Battlelog') !== 0)
		{
			return;
		}
		
		include __DIR__ . DIRECTORY_SEPARATOR . $classname .'.php';
	}
}

/**
 * Provides exceptions for this API.
 * 
 * @package BattlelogApi
 * @since   1.3
 * @uses    Exception
 */
class BattlelogException extends Exception
{
}