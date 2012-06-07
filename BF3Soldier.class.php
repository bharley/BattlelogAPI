<?php

/**
 * Battlefield 3 Soldier
 * 
 * Provides the class definition for the soldier container class.
 * 
 * @author		Blake Harley <contact@blakeharley.com>
 * @version		1.3
 * @package		BattlelogApi
 * @copyright	Copyright (c) 2011, Blake Harley
 * @license		http://creativecommons.org/licenses/by/3.0/
 * @since		1.3
 */

/**
 * Battlefield 3 soldier container.
 * 
 * @author		Blake Harley <contact@blakeharley.com>
 * @package		BattlelogApi
 * @since		1.0-beta
 */
class BF3Soldier extends BF3Container
{
	/**
	 * @access protected
	 * @var string
	 * @since 1.3
	 */
	protected $_accessUrl = "bf3/overviewPopulateStats/[[ID]]/None/1/";
	
	/**
	 * @access private
	 * @var array
	 * @since 1.0-beta
	 */
	private $_kitMap = array('byId' => array(), 'byName' => array());
	
	/**
	 * @access private
	 * @var array
	 * @since 1.1-beta
	 */
	private $_friends = null;
	
	/**
	 * @access private
	 * @var array
	 * @since 1.3
	 */
	private $_user = null;
	
	/**
	 * @access private
	 * @var array
	 * @since 1.3
	 */
	private $_awards = null;
	
	/**
	 * Parses the raw soldier data into something useful.
	 * 
	 * @access protected
	 * @param string $data
	 * @return array The final data
	 * @since 1.0-beta
	 */
	protected function _parseData($data)
	{
		$data = json_decode($data, true);
		$data = $data['data'];
		
		if (!is_array($data) || !array_key_exists('overviewStats', $data))
		{
			throw new BattlelogException('Invalid soldier data');
		}
		
		// Get the kit data sorted out
		$this->_kitMap = array();
		foreach($data['kitMap'] as $id => $kit)
		{
			$this->_kitMap['byName'][$kit['name']] = $id;
			$this->_kitMap['byId'][$id] = $kit['name'];
		}
		
		$returnData = array(
				'id' => $data['personaId'],
				'name' => $data['user']['username'],
				'created' => new DateTime("@{$data['user']['createdAt']}"),
				'timePlayed' => $this->_ternShort($data['overviewStats']['timePlayed']),
				'rank' => $this->_ternShort($data['overviewStats']['rank']),
				'rankImage' => array(
						'tiny' => BattlelogUtils::getRankImage($this->_ternShort($data['overviewStats']['rank']), 'tiny'),
						'small' => BattlelogUtils::getRankImage($this->_ternShort($data['overviewStats']['rank']), 'small'),
						'medium' => BattlelogUtils::getRankImage($this->_ternShort($data['overviewStats']['rank']), 'medium'),
						'large' => BattlelogUtils::getRankImage($this->_ternShort($data['overviewStats']['rank']), 'large')
				),
				'pointsNeeded' => $this->_ternShort($data['rankNeeded']['pointsNeeded']),
				'kills' => $this->_ternShort($data['overviewStats']['kills']),
				'killAssists' => $this->_ternShort($data['overviewStats']['killAssists']),
				'scorePerMinute' => $this->_ternShort($data['overviewStats']['scorePerMinute']),
				'score' => $this->_ternShort($data['overviewStats']['score']),
				'totalScore' => $this->_ternShort($data['overviewStats']['totalScore']),
				'combatScore' => $this->_ternShort($data['overviewStats']['combatScore']),
				'deaths' => $this->_ternShort($data['overviewStats']['deaths']),
				'kdRatio' => $this->_ternShort($data['overviewStats']['kdRatio']),
				'accuracy' => $this->_ternShort($data['overviewStats']['accuracy']),
				'longestHeadshot' => $this->_ternShort($data['overviewStats']['longestHeadshot']),
				'roundsPlayed' => $this->_ternShort($data['overviewStats']['numRounds']),
				'roundWins' => $this->_ternShort($data['overviewStats']['numWins']),
				'roundLosses' => $this->_ternShort($data['overviewStats']['numLosses']),
				'quitPercentage' => $this->_ternShort($data['overviewStats']['quitPercentage']),
				'kits' => array(),
				'topWeapons' => array()
		);
		
		$kits = array('assault', 'engineer', 'recon', 'support');
		foreach($kits as $kit)
		{
			$returnData['kits'][$kit] = array(
					'score' => $data['overviewStats']['kitScores'][$this->_kitMap['byName'][$kit]],
					'serviceStars' => $data['overviewStats']['serviceStars'][$this->_kitMap['byName'][$kit]],
					'time' => $data['overviewStats']['kitTimes'][$this->_kitMap['byName'][$kit]],
					'timePercent' => $data['overviewStats']['kitTimesInPercentage'][$this->_kitMap['byName'][$kit]],
					'serviceStarProgress' => $data['overviewStats']['serviceStarsProgress'][$this->_kitMap['byName'][$kit]],
					'image' => array(
							'small' => BattlelogUtils::getKitImage($kit, 'us', 'small'),
							'medium' => BattlelogUtils::getKitImage($kit, 'us', 'medium'),
							'large' => BattlelogUtils::getKitImage($kit, 'us', 'large')
					)
			);
		}
		
		if (is_array($data['topStats']))
		{
			foreach($data['topStats'] as $weapon)
			{
				$returnData['topWeapons'][] = array(
						'name' => $weapon['name'],
						'category' => $weapon['category'],
						'code' => $weapon['code'],
						'timeEquipped' => $weapon['timeEquipped'],
						'kit' => $this->_kitMap['byId'][$weapon['kit']],
						'kills' => $weapon['kills'],
						'headshots' => $weapon['headshots'],
						'shotsFired' => $weapon['shotsFired'],
						'shotsHit' => $weapon['shotsHit'],
						'accuracy' => $weapon['accuracy'],
						'serviceStars' => $weapon['serviceStars'],
						'serviceStarsProgress' => $weapon['serviceStarsProgress'],
						'image' => array(
								'tiny' => BattlelogUtils::getItemImage($weapon['name'], 'tiny'),
								'small' => BattlelogUtils::getItemImage($weapon['name'], 'small'),
								'medium' => BattlelogUtils::getItemImage($weapon['name'], 'medium'),
								'large' => BattlelogUtils::getItemImage($weapon['name'], 'large')
						)
				);
			}
		}
		
		return $returnData;
	}
	
	/**
	 * Gets an array of this soldier's user information. This function requires
	 * another scrapping run at Battlelog, and the results of this scrape are cached
	 * so subsequent requests don't need to re-scrape.
	 * 
	 * @access public
	 * @param bool $force If true, this will force the method to query Battlelog every time regardless of the cache
	 * @return array This soldier's user information
	 * @since 1.3
	 */
	public function getUser($force = false)
	{
		if ($force)
		{
			$this->_user = null;
		}
		
		if ($this->_user == null)
		{
			$data = $this->_api->getUrl("bf3/user/{$this['name']}/");
			$data = json_decode($data, true);
			$data = $data['context'];
			
			$this->_user = array(
					'id'			=> $data['profileCommon']['user']['userId'],
					'username'		=> $data['profileCommon']['user']['username'],
					'created'		=> new DateTime("@{$data['profileCommon']['user']['createdAt']}"),
					'lastLogin'		=> new DateTime("@{$data['profileCommon']['userinfo']['lastLogin']}"),
					'friendCount'	=> $data['profileCommon']['friendCount'],
					'presence'		=> array()
			);
			
			foreach($data['profileCommon']['user']['presence'] as $k => $v)
			{
				if ($k != 'userId')
				{
					$this->_user['presence'][$k] = $v;
				}
			}
		}
		
		return $this->_user;
	}
	
	/**
	 * Gets an array of this soldier's friends. This function requires another
	 * scrapping run at Battlelog, and the results of this scrape are cached
	 * so subsequent requests don't need to re-scrape.
	 * 
	 * @access public
	 * @param bool $force If true, this will force the method to query Battlelog every time regardless of the cache
	 * @return array This soldier's friends
	 * @since 1.1-beta
	 */
	public function getFriends($force = false)
	{
		if ($force)
		{
			$this->_friends = null;
		}
		
		if ($this->_friends == null)
		{
			$data = $this->_api->getUrl("bf3/user/{$this['name']}/friends/");
			$data = json_decode($data, true);
			if (!is_array($data['context']['friends']))
			{
				throw new BattlelogException('Invalid friends data');
			}
			$friends = $data['context']['friends'];
			
			$this->_friends = array();
			foreach($friends as $key => $friend)
			{
				$friend = $friend['user'];
				$this->_friends[$key] = array(
						'id'		=> $friend['userId'],
						'username'	=> $friend['username'],
						'created'	=> new DateTime("@{$friend['createdAt']}")
				);
				
				foreach($friend['presence'] as $k => $v)
				{
					if ($k != 'userId')
					{
						$this->_friends[$key][$k] = $v;
					}
				}
			}
		}
		
		return $this->_friends;
	}
	
	/**
	 * Gets an array of this soldier's awards. This function requires
	 * another scrapping run at Battlelog, and the results of this scrape are cached
	 * so subsequent requests don't need to re-scrape.
	 * 
	 * @access public
	 * @param bool $force If true, this will force the method to query Battlelog every time regardless of the cache
	 * @return array This soldier's awards
	 * @since 1.3
	 */
	public function getAwards($force = false)
	{
		if ($force)
		{
			$this->_awards = null;
		}
		
		if ($this->_awards == null)
		{
			$data = $this->_api->getUrl("bf3/awardsPopulateStats/{$this->_id}/1/");
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
						'id'		=> $id,
						'unlockId'	=> $ribbon['unlockId'],
						'name'		=> $this->_api->translate($locale[$ribbon['unlockId']]['name']),
						'taken'		=> $ribbon['unlocked'] == 100 ? true: false,
						'amount'	=> (int) $ribbon['actualValue'],
						'image' => array(
								'small' => BattlelogUtils::getRibbonImage($id, 'small'),
								'medium' => BattlelogUtils::getRibbonImage($id, 'medium'),
								'large' => BattlelogUtils::getRibbonImage($id, 'large')
						)
				);
			}
			
			foreach ($medals as $medal)
			{
				$id = (int) substr($medal['unlockId'], 1);
				$result['medals'][$id] = array(
						'id'		=> $id,
						'unlockId'	=> $medal['unlockId'],
						'name'		=> $this->_api->translate($locale[$medal['unlockId']]['name']),
						'taken'		=> $medal['unlocked'] == 100 ? true: false,
						'amount'	=> (int) $medal['actualValue'],
						'image' => array(
								'small' => BattlelogUtils::getMedalImage($id, 'small'),
								'medium' => BattlelogUtils::getMedalImage($id, 'medium'),
								'large' => BattlelogUtils::getMedalImage($id, 'large')
						)
				);
			}
			
			$this->_awards = $result;
		}
		
		return $this->_awards;
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
		// Special cases
		if ($name == 'friends')
		{
			return $this->getFriends();
		}
		else if ($name == 'user')
		{
			return $this->getUser();
		}
		else if ($name == 'ribbons')
		{
			$awards = $this->getAwards();
			return $awards['ribbons'];
		}
		else if ($name == 'medals')
		{
			$awards = $this->getAwards();
			return $awards['medals'];
		}
		
		return parent::get($name);
	}
	
	/**
	 * Dumps all of this soldier's data.
	 * 
	 * @access public
	 * @return array All of data in the data container
	 * @since 1.3
	 */
	public function debugData()
	{
		$data = parent::debugData();
		
		$data['user']		= $this->get('user');
		$data['friends']	= $this->get('friends');
		$data['ribbons']	= $this->get('ribbons');
		$data['medals']		= $this->get('medals');
		
		return $data;
	}
}
