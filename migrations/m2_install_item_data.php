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
* Migration stage 2: Items
*/
class m2_install_item_data extends \phpbb\db\migration\migration
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
		return array('\phpbb\pages\migrations\v10x\m1_initial_schema');
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
			array('custom', array(array($this, 'evebbcode_insert_eve_items_data'))),
		);
	}

	/**
	* Insert the EVE Online Items
	*
	* @access public
	*/
	public function evebbcode_insert_eve_items_data()
	{
		// Load the insert buffer class to perform a buffered multi insert
		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, $this->table_prefix . 'evebbcode_items');

		// Load the items data array
		$evebbcode_items = \cyerus\evebbcode\core\data\Item::getItems();
		
		// Insert all the EVE Item data into the database
		$insert_buffer->insert_all($evebbcode_items);

		// Flush the buffer
		$insert_buffer->flush();
	}
}


