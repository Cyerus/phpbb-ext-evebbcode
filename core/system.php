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

class System
{
	public static function getSystem($systemName)
	{
		// Check if the variable is actually an array,
		// as this would mean its probably send by preg_match_callback()
		if(is_array($systemName))
		{
			$systemName = $systemName[1];
		}
		
		$row = self::queryDatabase($systemName);
		
		if($row && self::isSystem($systemName))
		{
			if(isset($_SERVER['HTTP_EVE_TRUSTED']))
			{
				return self::returnHTML($row['systemID'], $row['systemName']);
			}
			else
			{
				return $row['systemName'];
			}
		}
		else
		{
			return $systemName;
		}
	}
	
	private static function queryDatabase($systemName)
	{
        global $db, $table_prefix;
		
		$sql = 'SELECT systemID, systemName
				FROM ' . $table_prefix . 'evebbcode_systems
				WHERE LOWER(systemName) = "' . $db->sql_escape(strtolower($systemName)) . '"';
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
		
		return $row;
	}
	
	private static function returnHTML($systemID, $systemName)
	{
		global $phpbb_root_path;
		
		if(!self::isWormhole($systemID))
		{
			$html =	'<a class="postlink">' . $systemName . '</a>&nbsp;&nbsp;' .
					'<img src="'.$phpbb_root_path.'/cyerus/evebbcode/images/information.png" onmouseover="this.style.cursor=\'pointer\'" onclick="CCPEVE.showInfo(5, '.$systemID.')" title="Information" />&nbsp;' .
					'<img src="'.$phpbb_root_path.'/cyerus/evebbcode/images/map.png" onmouseover="this.style.cursor=\'pointer\'" onclick="CCPEVE.showMap('.$systemID.')" title="Show on map" />&nbsp;';
			
			if($_SERVER['HTTP_EVE_TRUSTED'] == "Yes")
			{
				$html .=	'<img src="'.$phpbb_root_path.'/cyerus/evebbcode/images/destination.png" onmouseover="this.style.cursor=\'pointer\'" onclick="CCPEVE.setDestination('.$systemID.')" title="Set as destination" />&nbsp;' . 
							'<img src="'.$phpbb_root_path.'/cyerus/evebbcode/images/waypoint.png" onmouseover="this.style.cursor=\'pointer\'" onclick="CCPEVE.addWaypoint('.$systemID.')" title="Add waypoint" />&nbsp;';
			}
		}
		else
		{
			$html =	'<a class="postlink">' . $systemName . '</a>&nbsp;&nbsp;';
		}
		
		return $html;
	}
	
	private static function isSystem($systemName)
	{
		if(preg_match("#([A-Za-z\- ]{3,25})#", $systemName) || preg_match("#(J[0-9]{6}[\-1]{0,2})#", $systemName) || preg_match("#([A-Za-z0-9\-]{6})#", $systemName))
		{
			return true;
		}
		
		return false;
	}
	
	private static function isWormhole($systemID)
	{
		if($systemID >= 31000000 && $systemID < 32000000)
		{
			return true;
		}
		
		return false;
	}
}

?>