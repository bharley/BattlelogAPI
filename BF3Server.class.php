<?php

/**
 * Battlefield 3 Server
 * 
 * Provides the class definition for the server container class.
 * 
 * @author		Blake Harley <contact@blakeharley.com>
 * @version		1.3
 * @package		BattlelogApi
 * @copyright	Copyright (c) 2011, Blake Harley
 * @license		http://creativecommons.org/licenses/by/3.0/
 * @since		1.3
 */

/**
 * Battlefield 3 server container.
 * 
 * @author		Blake Harley <contact@blakeharley.com>
 * @package		BattlelogApi
 * @since		1.3
 */
class BF3Server extends BF3Container
{
	/**
	 * @access protected
	 * @var string
	 * @since 1.3
	 */
	protected $_accessUrl = "bf3/servers/show/[[ID]]/?json=1";
	
	/**
	 * Parses the raw soldier data into something useful.
	 * 
	 * @access protected
	 * @param string $data
	 * @return array The final data
	 * @since 1.3
	 */
	protected function _parseData($data)
	{
		$data = json_decode($data, true);
		$data = $data['message'];
		$server = $data['SERVER_INFO'];
		$server['players'] = $data['SERVER_PLAYERS'];
		$data = $server;
		
		if (!is_array($data) || !array_key_exists('guid', $data))
		{
			throw new BattlelogException('Invalid server data');
		}
		
		$returnData = array(
				'id'			=> $data['guid'],
				'guid'			=> $data['guid'],
				'name'			=> $data['name'],
				'ip'			=> $data['ip'],
				'port'			=> $data['port'],
				'numPlayers'	=> $data['numPlayers'],
				'maxPlayers'	=> $data['maxPlayers'],
				'numQueued'		=> $data['numQueued'],
				'passworded'	=> $data['hasPassword'],
				'ranked'		=> $data['ranked'],
				'punkbuster'	=> $data['punkbuster'],
				'map'			=> BF3Server::mapName($data['map']),
				'preset'		=> BF3Server::preset($data['preset']),
				'settings'		=> array(
						'showHud'				=> $data['settings']['vhud'] == 1,
						'autobalance'			=> $data['settings']['vaba'] == 1,
						'bulletDamage'			=> $data['settings']['vbdm'],
						'showMinimap'			=> $data['settings']['vmin'] == 1,
						'thirdPersonVehicleCam'	=> $data['settings']['v3ca'] == 1,
						'vehicles'				=> $data['settings']['vvsa'] == 1,
						'healthRegen'			=> $data['settings']['vrhe'] == 1,
						'tksBeforeKick'			=> $data['settings']['vtkc'],
						'killCam'				=> $data['settings']['vkca'] == 1,
						'minimapSpotting'		=> $data['settings']['vmsp'] == 1,
						'playerHealth'			=> $data['settings']['vshe'],
						'downTime'				=> $data['settings']['vpmd'],
						'friendlyFire'			=> $data['settings']['vffi'] == 1,
						'3dSpotting'			=> $data['settings']['v3sp'] == 1,
						'showEnemyNameTags'		=> $data['settings']['vnta'] == 1,
						'idleKickSeconds'		=> $data['settings']['vnit'],
						'kicksToBan'			=> $data['settings']['vtkk'],
						'respawnTime'			=> $data['settings']['vprt'],
						'squadLeaderSpawnOnly'	=> $data['settings']['osls'] == 1
				),
				'mapRotation'	=> array(),
				'players'		=> array()
		);
		
		if (is_array($data['maps']))
		{
			foreach ($data['maps'] as $map)
			{
				$returnData['mapRotation'][] = array(
						'name'		=> BF3Server::mapName($map['map']),
						'mode'		=> BF3Server::mode($map['mapMode'])
				);
			}
		}
		
		if (is_array($data['players']))
		{
			foreach ($data['players'] as $player)
			{
				$returnData['players'][] = array(
						'id'		=> $player['personaId'],
						'name'		=> $player['persona']['personaName'],
						'clanTag'	=> $player['persona']['clanTag']
				);
			}
		}
		
		return $returnData;
	}
	
	/**
	 * Gets the server region for the given ID.
	 * 
	 * @access public
	 * @param int $id The region ID
	 * @return string The server's region
	 * @since 1.3
	 */
	public static function region($id)
	{
		switch ($id)
		{
			case 1:
				return 'North America';
			case 2:
				return 'South America';
			case 4:
				return 'Antarctica';
			case 8:
				return 'Africa';
			case 16:
				return 'Europe';
			case 32:
				return 'Asia';
			case 64:
				return 'Oceania';
			default:
				return 'Unknown';
		}
	}
	
	/**
	 * Gets the mode name for the given ID.
	 * 
	 * @access public
	 * @param int $id The mode ID
	 * @return string The map mode
	 * @since 1.3
	 */
	public static function mode($id)
	{
		switch ($id)
		{
			case 1:
				return 'Conquest';
			case 2:
				return 'Rush';
			case 4:
				return 'Squad Rush';
			case 8:
				return 'Squad DM';
			case 32:
				return 'Team DM';
			case 64:
				return 'Conquest Large';
			default:
				return 'Unknown';
		}
	}
	
	/**
	 * Gets the name ofthe given map.
	 * 
	 * @access public
	 * @param string $id The map ID
	 * @return string The map's name
	 * @since 1.3
	 */
	public static function mapName($id)
	{
		switch ($id)
		{
			case 'MP_001':
				return 'Grand Bazar';
			case 'MP_003':
				return 'Tehran Highway';
			case 'MP_007':
				return 'Caspian Border';
			case 'MP_017':
				return 'Canals';
			case 'MP_013':
				return 'Damavand Peak';
			case 'MP_012':
				return 'Operation Firestorm';
			case 'MP_011':
				return 'Seine Crossing';
			case 'MP_018':
				return 'Kharg Island';
			case 'MP_Subway':
				return 'Operation Metro';
			default:
				return 'Unknown';
		}
	}
	
	/**
	 * Gets the preset for the given preset ID.
	 * 
	 * @access public
	 * @param int $id The preset ID
	 * @return string The server preset
	 * @since 1.3
	 */
	public static function preset($id)
	{
		switch ($id)
		{
			case 1:
				return 'Normal';
			case 2:
				return 'Hardcore';
			case 4:
				return 'Infantry Only';
			case 8:
				return 'Custom';
			default:
				return 'Unknown';
		}
	}
}
