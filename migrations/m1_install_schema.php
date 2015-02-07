<?php
/**
*
* EveBBcode - An phpBB extension adding EVE Online based BBcodes to your forum.
*
* @copyright (c) 2015 Jordy Wille (http://github.com/cyerus)
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace cyerus\evebbcode\migrations;

/**
* Migration stage 1: Initial schema
*/
class m1_install_schema extends \phpbb\db\migration\migration
{
	/**
	* Add the EVE tables to the database:
	*
	* @return array Array of table schema
	* @access public
	*/
	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'evebbcode_items'	=> array(
					'COLUMNS'		=> array(
						'itemID'		=> array('UINT:6', 0),
						'itemName'		=> array('VCHAR:100', ''),
						'categoryID'	=> array('UINT:6', 0),
					),
					'PRIMARY_KEY'	=> 'itemID',
				),
				$this->table_prefix . 'evebbcode_ships'	=> array(
					'COLUMNS'		=> array(
						'typeID'			=> array('INT:10', 0),
						'typeName'			=> array('VCHAR:100', ''),
						'Low'				=> array('INT:2', 0),
						'Medium'			=> array('INT:2', 0),
						'High'				=> array('INT:2', 0),
						'Drone'				=> array('INT:5', 0),
						'Rig'				=> array('INT:2', 0),
						'Subsystem'			=> array('INT:2', 0),
						'raceID'			=> array('INT:2', 0),
						'raceName'			=> array('VCHAR:20', ''),
						'Tech'				=> array('VCHAR:10', ''),
						'groupName'			=> array('VCHAR:50', ''),
						'marketGroupName'	=> array('VCHAR:50', ''),
						'Icon'				=> array('VCHAR:30', ''),
					),
					'PRIMARY_KEY'	=> 'typeID',
				),
				$this->table_prefix . 'evebbcode_systems'	=> array(
					'COLUMNS'		=> array(
						'systemID'		=> array('INT:11', 0),
						'systemName'	=> array('VCHAR:100', ''),
					),
					'PRIMARY_KEY'	=> 'systemID',
				),
				$this->table_prefix . 'evebbcode_subsystems'	=> array(
					'COLUMNS'		=> array(
						'typeID'			=> array('INT:10', 0),
						'typeName'			=> array('VCHAR:100', ''),
						'Low'				=> array('INT:2', 0),
						'Medium'			=> array('INT:2', 0),
						'High'				=> array('INT:2', 0),
						'Drone'				=> array('INT:5', 0),
					),
					'PRIMARY_KEY'	=> 'typeID',
				),
			),
		);
	}

	/**
	* Drop the EVE tables from the database
	*
	* @return array Array of table schema
	* @access public
	*/
	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'evebbcode_items',
				$this->table_prefix . 'evebbcode_ships',
				$this->table_prefix . 'evebbcode_systems',
				$this->table_prefix . 'evebbcode_subsystems',
			),
		);
	}
}
