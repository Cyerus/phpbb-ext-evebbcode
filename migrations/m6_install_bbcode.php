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
* Migration stage 6: BBcodes
*/
class m6_install_bbcode extends \phpbb\db\migration\migration
{
	/**
	* Check if the current version of the extension is already installed
	*
	* @return boolean Is the same or newer version of this extension already installed?
	* @access public
	*/
	public function effectively_installed()
	{
		return isset($this->config['evebbcode_version']) && version_compare($this->config['evebbcode_version'], '1.0.0-a1', '>=');
	}

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
	* Installation; Add our BBcodes to phpBB
	* 
	* @access public
	*/
	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'evebbcode_install_bbcodes'))),

			array('config.add', array('evebbcode_version', '1.0.0-a1')),
		);
	}
	
	/**
	* Deletion; Remove our BBcodes from phpBB
	* 
	* @access public
	*/
	public function revert_data()
	{
		return array(
			array('custom', array(array($this, 'evebbcode_remove_bbcodes'))),

			array('config.remove', array('evebbcode_version')),
		);
	}
	
	/**
	* Install our BBcodes to phpBB
	* 
	* @access public
	*/
	public function evebbcode_install_bbcodes()
	{
		$bbcode_data = array(
			'eveitem' => array(
				'bbcode_helpline'	=> 'EVEBBCODE_EVEITEM_HELPLINE',
				'bbcode_match'		=> '[eveitem]{TEXT}[/eveitem]',
				'bbcode_tpl'		=> '[eveitem]{TEXT}[/eveitem]',
			),
			'evesystem' => array(
				'bbcode_helpline'	=> 'EVEBBCODE_EVESYSTEM_HELPLINE',
				'bbcode_match'		=> '[evesystem]{TEXT}[/evesystem]',
				'bbcode_tpl'		=> '[evesystem]{TEXT}[/evesystem]',
			),
			'evefit' => array(
				'bbcode_helpline'	=> 'EVEBBCODE_EVEFIT_HELPLINE',
				'bbcode_match'		=> '[evefit]{TEXT}[/evefit]',
				'bbcode_tpl'		=> '[evefit]{TEXT}[/evefit]',
			),
		);

		$this->evebbcode_bbcode_logic($bbcode_data);
	}

	/**
	* Remove our BBcodes from phpBB
	* 
	* @access public
	*/
	public function evebbcode_remove_bbcodes()
	{
		$sql = 'DELETE FROM ' . BBCODES_TABLE . "
			WHERE LOWER(bbcode_tag) IN ('eveitem', 'evesystem', 'evefit')";
		$this->db->sql_query($sql);
	}
	
	/**
	* Use this function to 'learn' phpBB about the new BBcodes,
	* so that the mod doesn't break when an admin manually adds
	* more BBcodes himself.
	* 
	* @access public
	*/
	public function evebbcode_bbcode_logic($bbcode_data)
	{
		// Load the acp_bbcode class
		if (!class_exists('acp_bbcodes'))
		{
			include($this->phpbb_root_path . 'includes/acp/acp_bbcodes.' . $this->php_ext);
		}
		$bbcode_tool = new \acp_bbcodes();

		foreach ($bbcode_data as $bbcode_name => $bbcode_array)
		{
			// Build the BBCodes
			$data = $bbcode_tool->build_regexp($bbcode_array['bbcode_match'], $bbcode_array['bbcode_tpl']);

			$bbcode_array += array(
				'bbcode_tag'			=> $data['bbcode_tag'],
				'first_pass_match'		=> $data['first_pass_match'],
				'first_pass_replace'	=> $data['first_pass_replace'],
				'second_pass_match'		=> $data['second_pass_match'],
				'second_pass_replace'	=> $data['second_pass_replace']
			);

			$sql = 'SELECT bbcode_id
				FROM ' . BBCODES_TABLE . "
				WHERE LOWER(bbcode_tag) = '" . strtolower($bbcode_name) . "'
				OR LOWER(bbcode_tag) = '" . strtolower($bbcode_array['bbcode_tag']) . "'";
			$result = $this->db->sql_query($sql);
			$row_exists = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row_exists)
			{
				// Update existing BBCode
				$bbcode_id = $row_exists['bbcode_id'];

				$sql = 'UPDATE ' . BBCODES_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $bbcode_array) . '
					WHERE bbcode_id = ' . $bbcode_id;
				$this->db->sql_query($sql);
			}
			else
			{
				// Create new BBCode
				$sql = 'SELECT MAX(bbcode_id) AS max_bbcode_id
					FROM ' . BBCODES_TABLE;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row)
				{
					$bbcode_id = $row['max_bbcode_id'] + 1;

					// Make sure it is greater than the core BBCode ids...
					if ($bbcode_id <= NUM_CORE_BBCODES)
					{
						$bbcode_id = NUM_CORE_BBCODES + 1;
					}
				}
				else
				{
					$bbcode_id = NUM_CORE_BBCODES + 1;
				}

				if ($bbcode_id <= BBCODE_LIMIT)
				{
					$bbcode_array['bbcode_id'] = (int) $bbcode_id;
					$bbcode_array['display_on_posting'] = 1;

					$this->db->sql_query('INSERT INTO ' . BBCODES_TABLE . ' ' . $this->db->sql_build_array('INSERT', $bbcode_array));
				}
			}
		}
	}
}
