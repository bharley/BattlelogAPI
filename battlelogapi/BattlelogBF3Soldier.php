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
 * 
 * 
 * @package BattlelogApi
 * @author  Blake Harley <contact@blakeharley.com>
 * @since   1.0-beta
 * @uses    BattlelogContainer 
 */
class BattlelogBF3Soldier extends BattlelogContainer
{
	/**
	 * Stores the API and the ID. Will not load the container's data until accessed
	 * or lazy loading is disabled.
	 * 
	 * @since  2.0
	 * @param  BattlelogApi $api
	 * @param  string $id The id for this soldier
	 * @param  bool $load Whether or not to load the data contents right away
	 * @see    BattlelogContainer::__construct()
	 */
	public function __construct($id, $api, $load = false)
	{
		// Create a loader for this ID
		$loader = new BattlelogBF3SoldierOverviewLoader($id);
		$this->_addLoader($loader);
		
		// Do parent things
		parent::__construct($api, $load);
	}
}

/**
 * Grabs overview information about a BF3 soldier. Wow, these class names are
 * getting long.
 * 
 * @package BattlelogApi
 * @author  Blake Harley <contact@blakeharley.com>
 * @since   2.0
 * @uses    BattlelogLoader 
 */
class BattlelogBF3SoldierOverviewLoader extends BattlelogLoader
{
	/**
	 * @see BattlelogLoader::_init()
	 */
	protected function _init()
	{
		// Set the URI
		$this->_uri = 'bf3/overviewPopulateStats/[[ID]]/None/1/';
	}
	
	/**
	 * @see BattlelogLoader::_parse()
	 */
	protected function _parse($data)
	{
		echo $data; exit;
	}
}