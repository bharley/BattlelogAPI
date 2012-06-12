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
 * Battlefield 3 solider container.
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
		// Create loaders for this class
		$loaderOptions = array($id, $api);
		$this->_addNamedLoader('overview', $loaderOptions);
		$this->_addNamedLoader('awards', $loaderOptions);
		
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
		$data = json_decode($data, true);
		$data = $data['data'];
		
		if (!is_array($data) || !array_key_exists('overviewStats', $data))
		{
			throw new BattlelogException('Invalid soldier data');
		}
		
		// Build the kitmap
		$kitMap = array();
		foreach ($data['kitMap'] as $id => $kit)
		{
			$kitMap['byName'][$kit['name']] = $id;
			$kitMap['byId'][$id] = $kit['name'];
		}
		
		// Build the return data
		$returnData = array(
			'id'              => $data['personaId'],
			'name'            => $data['user']['username'],
			'created'         => new DateTime("@{$data['user']['createdAt']}"),
			'timePlayed'      => $this->_ternShort($data['overviewStats']['timePlayed']),
			'rank'            => $this->_ternShort($data['overviewStats']['rank']),
			'rankImage' => array(
//				'tiny'   => BattlelogUtils::getRankImage($this->_ternShort($data['overviewStats']['rank']), 'tiny'),
//				'small'  => BattlelogUtils::getRankImage($this->_ternShort($data['overviewStats']['rank']), 'small'),
//				'medium' => BattlelogUtils::getRankImage($this->_ternShort($data['overviewStats']['rank']), 'medium'),
//				'large'  => BattlelogUtils::getRankImage($this->_ternShort($data['overviewStats']['rank']), 'large')
			),
			'pointsNeeded'    => $this->_ternShort($data['rankNeeded']['pointsNeeded']),
			'kills'           => $this->_ternShort($data['overviewStats']['kills']),
			'killAssists'     => $this->_ternShort($data['overviewStats']['killAssists']),
			'scorePerMinute'  => $this->_ternShort($data['overviewStats']['scorePerMinute']),
			'score'           => $this->_ternShort($data['overviewStats']['score']),
			'totalScore'      => $this->_ternShort($data['overviewStats']['totalScore']),
			'combatScore'     => $this->_ternShort($data['overviewStats']['combatScore']),
			'deaths'          => $this->_ternShort($data['overviewStats']['deaths']),
			'kdRatio'         => $this->_ternShort($data['overviewStats']['kdRatio']),
			'accuracy'        => $this->_ternShort($data['overviewStats']['accuracy']),
			'longestHeadshot' => $this->_ternShort($data['overviewStats']['longestHeadshot']),
			'roundsPlayed'    => $this->_ternShort($data['overviewStats']['numRounds']),
			'roundWins'       => $this->_ternShort($data['overviewStats']['numWins']),
			'roundLosses'     => $this->_ternShort($data['overviewStats']['numLosses']),
			'quitPercentage'  => $this->_ternShort($data['overviewStats']['quitPercentage']),
			'kits'            => array(),
			'topWeapons'      => array()
		);
		
		// Get stats per kit
		$kits = array('assault', 'engineer', 'recon', 'support');
		foreach ($kits as $kit)
		{
			$returnData['kits'][$kit] = array(
				'score'               => $data['overviewStats']['kitScores'][$kitMap['byName'][$kit]],
				'serviceStars'        => $data['overviewStats']['serviceStars'][$kitMap['byName'][$kit]],
				'time'                => $data['overviewStats']['kitTimes'][$kitMap['byName'][$kit]],
				'timePercent'         => $data['overviewStats']['kitTimesInPercentage'][$kitMap['byName'][$kit]],
				'serviceStarProgress' => $data['overviewStats']['serviceStarsProgress'][$kitMap['byName'][$kit]],
				'image'               => array(
//						'small'  => BattlelogUtils::getKitImage($kit, 'us', 'small'),
//						'medium' => BattlelogUtils::getKitImage($kit, 'us', 'medium'),
//						'large'  => BattlelogUtils::getKitImage($kit, 'us', 'large')
				)
			);
		}
		
		// Get top stats if available
		if (is_array($data['topStats']))
		{
			foreach($data['topStats'] as $weapon)
			{
				$returnData['topWeapons'][] = array(
					'name'                 => $weapon['name'],
					'category'             => $weapon['category'],
					'code'                 => $weapon['code'],
					'timeEquipped'         => $weapon['timeEquipped'],
					'kit'                  => $kitMap['byId'][$weapon['kit']],
					'kills'                => $weapon['kills'],
					'headshots'            => $weapon['headshots'],
					'shotsFired'           => $weapon['shotsFired'],
					'shotsHit'             => $weapon['shotsHit'],
					'accuracy'             => $weapon['accuracy'],
					'serviceStars'         => $weapon['serviceStars'],
					'serviceStarsProgress' => $weapon['serviceStarsProgress'],
					'image' => array(
//						'tiny'   => BattlelogUtils::getItemImage($weapon['name'], 'tiny'),
//						'small'  => BattlelogUtils::getItemImage($weapon['name'], 'small'),
//						'medium' => BattlelogUtils::getItemImage($weapon['name'], 'medium'),
//						'large'  => BattlelogUtils::getItemImage($weapon['name'], 'large')
					)
				);
			}
		}
		
		return $returnData;
	}
}

/**
 * Gets a list of the given soldier's awards.
 * 
 * @package BattlelogApi
 * @author  Blake Harley <contact@blakeharley.com>
 * @since   2.0
 * @uses    BattlelogLoader  
 */
class BattlelogBF3SoldierAwardsLoader extends BattlelogLoader
{
	/**
	 * @see BattlelogLoader::_init()
	 */
	protected function _init()
	{
		// Set the URI
		$this->_uri = 'bf3/awardsPopulateStats/[[ID]]/1/';
	}
	
	/**
	 * @see BattlelogLoader::_parse()
	 */
	protected function _parse($data)
	{
		$data = json_decode($data, true);
		if (!is_array($data['data']['awards']))
		{
			throw new BattlelogException('Invalid awards data');
		}
		
		$ribbons = $data['data']['awards']['AwardGroup_Ribbons'];
		$medals = $data['data']['awards']['AwardGroup_Medals'];
		$locale = $data['data']['bf3GadgetsLocale']['awards'];
		
		$result = array();
		$result['ribbons'] = array();
		$result['medals'] = array();
		
		foreach ($ribbons as $ribbon)
		{
			$id = (int) substr($ribbon['unlockId'], 1);
			$result['ribbons'][$id] = array(
				'id'       => $id,
				'unlockId' => $ribbon['unlockId'],
				'name'     => $this->_api->translate($locale[$ribbon['unlockId']]['name']),
				'taken'    => $ribbon['unlocked'] == 100 ? true: false,
				'amount'   => (int) $ribbon['actualValue'],
				'image' => array(
//					'small'  => BattlelogUtils::getRibbonImage($id, 'small'),
//					'medium' => BattlelogUtils::getRibbonImage($id, 'medium'),
//					'large'  => BattlelogUtils::getRibbonImage($id, 'large')
				)
			);
		}

		foreach ($medals as $medal)
		{
			$id = (int) substr($medal['unlockId'], 1);
			$result['medals'][$id] = array(
				'id'       => $id,
				'unlockId' => $medal['unlockId'],
				'name'     => $this->_api->translate($locale[$medal['unlockId']]['name']),
				'taken'    => $medal['unlocked'] == 100 ? true: false,
				'amount'   => (int) $medal['actualValue'],
				'image' => array(
//					'small'  => BattlelogUtils::getMedalImage($id, 'small'),
//					'medium' => BattlelogUtils::getMedalImage($id, 'medium'),
//					'large'  => BattlelogUtils::getMedalImage($id, 'large')
				)
			);
		}

		$this->_awards = $result;
		
		return $result;
	}
}