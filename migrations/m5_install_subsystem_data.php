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
* Migration stage 5: Subsystems
*/
class m5_install_subsystem_data extends \phpbb\db\migration\migration
{
	/**
	* Assign migration file dependencies for this migration
	*
	* @return array Array of migration files
	* @static
	* @access public
	*/
	static public function depends_on()
	{
		return array('\cyerus\evebbcode\migrations\m1_install_schema');
	}

	/**
	* Add or update data in the database
	*
	* @return array Array of table data
	* @access public
	*/
	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'evebbcode_insert_eve_subsytems_data'))),
		);
	}

	/**
	* Insert the EVE Online Subsystems
	*
	* @access public
	*/
	public function evebbcode_insert_eve_subsystems_data()
	{
		// Load the insert buffer class to perform a buffered multi insert
		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, $this->table_prefix . 'evebbcode_subsystems');

		// Load the ships data array
		$evebbcode_subsystems = \cyerus\evebbcode\core\data\Subsystems::getSubsystems();
		
		// Insert all the EVE Ships data into the database
		$insert_buffer->insert_all($evebbcode_subsystems);

		// Flush the buffer
		$insert_buffer->flush();
	}
}



