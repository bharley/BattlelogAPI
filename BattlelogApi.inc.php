<?php

/**
 * Battlelog API
 * 
 * This API allows for the grabbing of Battlefield 3 player stats.
 * Note: It seems a valid EA login is still required to pull down
 * data...
 * 
 * For examples, updates and additional information, see the project page
 * at http://www.blakeharley.com/projects/battlelogapi/.
 * 
 * @author		Blake Harley <contact@blakeharley.com>
 * @version		1.3
 * @package		BattlelogApi
 * @copyright	Copyright (c) 2011, Blake Harley
 * @license		http://creativecommons.org/licenses/by/3.0/
 * @since		1.0-beta
 */

require_once 'BF3Container.class.php';
require_once 'BattlelogLang.inc.php';
require_once 'BF3Soldier.class.php';
require_once 'BF3Server.class.php';

/**
 * This is where the cookies will be stored while connecting with the Battlelog
 * servers. Make sure PHP has read and write access to the file location.
 * 
 * @package BattlelogApi
 * @var string
 * @since 1.0-beta
 */
define('BLA_COOKIE_FILE', 'cookies.txt');

/**
 * You can change this is you want. Some user agents have been blocked, so it's
 * best to stick with impersonating a real browser.
 * For the curious, the current user agent is Mozilla Firefox 6.0.2 on Windows 7 64-bit.
 * 
 * @package BattlelogApi
 * @var string
 * @since 1.2-pre
 */
define('BLA_USER_AGENT', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2');

/**
 * You probably shouldn't touch this.
 * 
 * @package BattlelogApi
 * @var string
 * @since 1.0-beta
 */
define('BLA_LOGIN_URL', 'https://battlelog.battlefield.com/bf3/gate/login/');


/**
 * This class is the main accessor for the stats engine.
 * 
 * Example:
 * <code>
 * $battlelog = new BattlelogApi('player@email.com', 'p4ssword');
 * $jones = $battlelog->getBF3Player('123456789');
 * echo $jones->getScorePerMinute();
 * </code>
 * 
 * @author		Blake Harley <contact@blakeharley.com>
 * @package		BattlelogApi
 * @since		1.0-beta
 */
class BattlelogApi
{
	/**
	 * @access private
	 * @var string
	 * @since 1.0-beta
	 */
	private $_username = null;
	
	/**
	 * @access private
	 * @var string
	 * @since 1.0-beta
	 */
	private $_password = null;
	
	/**
	 * @access private
	 * @var bool
	 * @since 1.0-beta
	 */
	private $_hasCredentials = false;
	
	/**
	 * @access private
	 * @var BattlelogLang
	 * @since 1.3
	 */
	private $_lang = null;

	/**
	 * Stores the EA credentials so accounts can be scraped.
	 * 
	 * @access public
	 * @param string $username The EA account name
	 * @param string $password The EA account password
	 * @param string $lang The language to use for the translator (experimental)
	 * @since 1.0-beta
	 */
	public function __construct($username, $password, $lang = null)
	{
		$this->_username = (string) $username;
		$this->_password = (string) $password;
		
		$this->_lang = new BattlelogLang($this, $lang);
	}
	
	/**
	 * Creates and returns a BF3Soldier using the given soldier's
	 * ID.
	 * 
	 * @access public
	 * @param string $soldierId The ID of the soldier
	 * @return BF3Solider The requested soldier
	 * @since 1.0-beta
	 */
	public function getBF3Soldier($soldierId)
	{
		return new BF3Soldier($this, $soldierId, true);
	}
	
	/**
	 * Creates and returns a BF3Server using the given server's
	 * ID.
	 * 
	 * @access public
	 * @param string $serverId The ID of the server
	 * @return BF3Server The requested server
	 * @since 1.3
	 */
	public function getBF3Server($serverId)
	{
		return new BF3Server($this, $serverId, true);
	}
	
	/**
	 * Uses the current session to get the text on the given url.
	 * 
	 * @access public
	 * @param string $url The url to grab data from
	 * @param bool $battlelog Whether or not this use is a subportion of 'http://battlelog.battlefield.com/' and needs the cookies
	 * @return string The contents of the given url
	 * @since 1.3
	 */
	public function getUrl($url, $battlelog = true)
	{
		if (!$this->_hasCredentials)
		{
			$this->_getCredentials();
			$this->_hasCredentials = true;
		}
		
		if ($battlelog === true)
		{
			$ch = curl_init("http://battlelog.battlefield.com/$url");
			curl_setopt($ch, CURLOPT_COOKIEFILE, BLA_COOKIE_FILE);
		}
		else
		{
			$ch = curl_init($url);
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Expect:',
				'User-Agent: ' . BLA_USER_AGENT,
				'Accept: */*',
				'Accept-Language: en-us,en;q=0.5',
				'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
				'X-Requested-With: XMLHttpRequest',
				'X-AjaxNavigation: 1'
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		$data = curl_exec($ch);
		curl_close($ch);
		
		return $data;
	}
	
	/**
	 * Gets credentials required the connect to the Battlelog servers. 
	 * 
	 * @access private
	 * @since 1.0-beta
	 */
	private function _getCredentials()
	{
		$postchars = http_build_query(array(
				'redirect' => '|bf3|',
				'email' => $this->_username,
				'password' => $this->_password,
				'submit' => 'Sign+in'
		), '', '&');
		
		$ch = curl_init(BLA_LOGIN_URL);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Expect:',
				'User-Agent: ' . BLA_USER_AGENT,
				'Accept: */*',
				'Accept-Language: en-us,en;q=0.5',
				'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7'
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, BLA_COOKIE_FILE);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postchars);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_exec($ch);
		curl_close($ch);
	}
	
	/**
	 * Runs the given nameplate through the BF3 locale file to get the
	 * actual name of the item.
	 * 
	 * @access public
	 * @param string $string The nameplate to run through the translator
	 * @return mixed The translated id
	 * @since 1.3
	 */
	public function translate($string)
	{
		return $this->_lang[$string];
	}
}


/**
 * Provides common utility methods for miscellaneous battlelog things.
 * 
 * @author		Blake Harley <contact@blakeharley.com>
 * @package		BattlelogApi
 * @since		1.0-beta
 */
class BattlelogUtils
{
	/**
	 * Returns the URL of a rank from the Battlelog CDN.
	 * 
	 * @param string $size The desired image size. Valid sizes are 'tiny', 'small', 'medium' and 'large'. Default size is small.
	 * @since 1.0-beta
	 */
	public static function getRankImage($rank, $size = 'small')
	{
		$validSizes = array('tiny', 'small', 'medium', 'large');
		$size = strtolower($size);
		
		if (!in_array($size, $validSizes))
		{
			throw new BattlelogException("Invalid size '$size' specified");
		}
		
		return "http://battlelog-cdn.battlefield.com/public/profile/bf3/stats/ranks/$size/r$rank.png";
	}
	
	/**
	 * Returns the URL of a kit image from the Battlelog CDN.
	 * 
	 * @param string $kit The kit name. Valid kit names are 'assault', 'support', 'engineer', 'recon'.
	 * @param string $team The team designation. Valid teams are 'us' and 'ru'. Defaults to 'us'.
	 * @param string $size The image size. Valid image sizes are 'small', 'medium' and 'large'.
	 * @since 1.0-beta
	 */
	public static function getKitImage($kit, $team = 'us', $size = 'medium')
	{
		$validKits = array('assault', 'recon', 'engineer', 'support');
		$validTeams = array('us', 'ru');
		$validSizes = array('small', 'medium', 'large');
		$kit = strtolower($kit);
		$team = strtolower($team);
		$size = strtolower($size);
		
		if (!in_array($kit, $validKits))
		{
			throw new BattlelogException("Invalid kit '$kit' specified");
		}
		if (!in_array($team, $validTeams))
		{
			throw new BattlelogException("Invalid team '$team' specified");
		}
		if (!in_array($size, $validSizes))
		{
			throw new BattlelogException("Invalid size '$size' specified");
		}
		
		return "http://battlelog-cdn.battlefield.com/public/profile/kits/{$size[0]}/bf3-$team-$kit.png";
	}
	
	/**
	 * Returns the URL of a ribbon image from the Battlelog CDN.
	 * 
	 * @param string|int $id The ribbon id.
	 * @param string $size The image size. Valid image sizes are 'small', 'medium' and 'large'.
	 * @since 1.0-beta
	 */
	public static function getRibbonImage($id, $size = 'medium')
	{
		$validSizes = array('small', 'medium', 'large');
		$size = strtolower($size);
		
		if (!in_array($size, $validSizes))
		{
			throw new BattlelogException("Invalid size '$size' specified");
		}
		
		$id = str_pad($id, 2, '0', STR_PAD_LEFT);
		return "http://battlelog-cdn.battlefield.com/public/profile/bf3/stats/ribbons/{$size[0]}/r$id.png";
	}
	
	/**
	 * Returns the URL of a medal image from the Battlelog CDN.
	 * 
	 * @param string|int $id The medal id.
	 * @param string $size The image size. Valid image sizes are 'small', 'medium' and 'large'.
	 * @since 1.3
	 */
	public static function getMedalImage($id, $size = 'medium')
	{
		$validSizes = array('small', 'medium', 'large');
		$size = strtolower($size);
		
		if (!in_array($size, $validSizes))
		{
			throw new BattlelogException("Invalid size '$size' specified");
		}
		
		$id = str_pad($id, 2, '0', STR_PAD_LEFT);
		return "http://battlelog-cdn.battlefield.com/public/profile/bf3/stats/medals/{$size[0]}/m$id.png";
	}
	
	/**
	 * Returns the URL of an item image from the Battlelog CDN.
	 * 
	 * @param string $name The item name
	 * @param string $size The image size. Valid sizes are 'tiny', 'small', 'medium' and 'large'. Default size is medium.
	 * @since 1.0-beta
	 */
	public static function getItemImage($name, $size = 'medium')
	{
		$validSizes = array(
				'tiny'		=> '79x43',
				'small'		=> '90x54',
				'medium'	=> '147x88',
				'large'		=> '512x308'
		);
		$size = strtolower($size);
		
		if (!array_key_exists($size, $validSizes))
		{
			throw new BattlelogException("Invalid size '$size' specified");
		}
		
		$name = strtolower($name);
		return "http://battlelog-cdn.battlefield.com/public/profile/bf3/stats/items_{$validSizes[$size]}/$name.png";
	}
}

/**
 * Part of my efforts to produce proper error messages.
 * 
 * @author		Blake Harley <contact@blakeharley.com>
 * @package		BattlelogApi
 * @since		1.3
 */
class BattlelogException extends Exception { }
