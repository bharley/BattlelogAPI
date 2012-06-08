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
	 * Adds the loader for general player information
	 * 
	 * @see BattlelogContainer::_initLoaders()
	 */
	protected function _initLoaders()
	{
		
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
		$this->_uri = '';
	}
}