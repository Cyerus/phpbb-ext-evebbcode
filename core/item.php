<?php
/**
*
* EveBBcode - An phpBB extension adding EVE Online based BBcodes to your forum.
*
* @copyright (c) 2015 Jordy Wille (http://github.com/cyerus)
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace cyerus\evebbcode\core;

class Item
{
	public static function getItem($itemName)
	{
		// Check if the variable is actually an array,
		// as this would mean its probably send by preg_match_callback()
		if(is_array($itemName))
		{
			$itemName = $itemName[1];
		}
		
		$row = self::getItemInfo($itemName);
		
		if($row)
		{
			if(isset($_SERVER['HTTP_EVE_TRUSTED']))
			{
				return self::returnHTML($row['itemID'], $row['itemName']);
			}
			else
			{
				return $row['itemName'];
			}
		}
		else
		{
			return $itemName;
		}
	}
	
	private static function getItemInfo($itemName)
	{
        global $db, $table_prefix;
		
		$sql = 'SELECT itemID, itemName
				FROM ' . $table_prefix . 'evebbcode_items
				WHERE LOWER(itemName) = "' . $db->sql_escape(strtolower($itemName)) . '"';
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
		
		return $row;
	}
	
	private static function returnHTML($itemID, $itemName)
	{
		global $phpbb_root_path;
		
		return	'<a class="postlink">'.$itemName.'</a>&nbsp;&nbsp;' .
				'<img src="'.$phpbb_root_path.'/cyerus/evebbcode/images/information.png" onmouseover="this.style.cursor=\'pointer\'" onclick="CCPEVE.showInfo('.$itemID.')" title="Information" />&nbsp;' .
				'<img src="'.$phpbb_root_path.'/cyerus/evebbcode/images/market.png" onmouseover="this.style.cursor=\'pointer\'" onclick="CCPEVE.showMarketDetails('.$itemID.')" title="Market details" />&nbsp;' .
				'<img src="'.$phpbb_root_path.'/cyerus/evebbcode/images/preview.png" onmouseover="this.style.cursor=\'pointer\'" onclick="CCPEVE.showPreview('.$itemID.')" title="Preview" />&nbsp;';
	}
}

?>