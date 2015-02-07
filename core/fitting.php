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

class Fitting
{
	public static function getFitting($fittingText)
	{
		// Check if the variable is actually an array,
		// as this would mean its probably send by preg_match_callback()
		if(is_array($fittingText))
		{
			$fittingText = $fittingText[1];
		}

		// Read the fitting and put it into an array
		$fitting = self::readLines($fittingText);
		
		// If ship isn't found, return unchanged fitting
		if(!$fitting)
		{
			return $fittingText;
		}
		
		// Limit the amount of available slots by the slots the ship has
		$fitting = self::limitSlots($fitting);

		// Create HTML for fitting panel (includes shipDNA)
		$fittingHTML = self::getFittingHTML($fitting);

		return self::returnHTML($fitting['shipInfo'], $fittingText, $fittingHTML['html'], $fittingHTML['dna']);
	}
	
	private static function readLines($fittingText)
	{
		// Declare variables
		$fittingSlotType = array(
			"Low",
			"Medium",
			"High",
			"Rig",
			"Subsystem",
			"Drone"
		);
		
		$defaultSlotCount = array(
			"Low"		=> 8,
			"Medium"	=> 8,
			"High"		=> 8,
			"Rig"		=> 3,
			"Subsystem"	=> 5,
			"Drone"		=> 4
		);
		
		$fittingArray = array();
		$slotType = 0;
		$slotCounter = 0;

		// Create an array from the fitting and loop each row
		$fittingLines = preg_split("#\r\n|\r|\n#", $fittingText);
		
		foreach($fittingLines as $key => $line)
		{
			// Fitting should start with [Shipname, Fitting Name]
			// at line 0, basically false
			if(!$key)
			{
				// Cut line into pieces to grab Ship name and fitting name
				preg_match("#^[ ]*\[([A-Za-z ]+), ([\s\S]+)\][ ]*$#", $line, $matches);
				
				// Grab information about ship
				// And return EFT output if ship isn't found
				$fittingArray['shipInfo'] = self::getShipInfo($matches[1]);
				if(!$fittingArray['shipInfo'])
				{
					return false;
				}
				
				// Might aswell declare the fitting name
				$fittingArray['shipInfo']['fittingName'] = $matches[2];
				
				continue;
			}
			
            // Empty lines means we need to switch to different slot type (from lowslot to midslot for example).
            if(empty($line))
            {
				// Check if we still have empty slots of this slot type
				if($slotCounter < $defaultSlotCount[$fittingSlotType[$slotType]])
				{
					// Fill up all the slots the empty slots with false
					for($slotCounter; $slotCounter < $defaultSlotCount[$fittingSlotType[$slotType]]; $slotCounter++)
					{
						$fittingArray[$fittingSlotType[$slotType]][] = false;
					}
				}                
				
				$slotType++;
                $slotCounter = 0;
				
                continue;
            }
			
			// Skip if we are higher than the default number of allowed slots
			if($slotCounter >= $defaultSlotCount[$fittingSlotType[$slotType]])
			{
				continue;
			}
			
			// Set slot to false when if it's an empty slot
			if($line == "[empty " . strtolower($fittingSlotType[$slotType]) . " slot]")
			{
				$fittingArray[$fittingSlotType[$slotType]][] = false;
				$slotCounter++;
				
				continue;
			}
			else
			{
				// Add item to the array we'll be looping below.
				// All the junk should be weeded out by now (at least I hope so).
				$fittingArray[$fittingSlotType[$slotType]][] = trim($line);

				$slotCounter++;
			}
		}
		
		return $fittingArray;
	}
	
	private static function limitSlots($fitting)
	{
		// Get the amount of fitting slots the Subsystems give
		if(!empty($fitting["Subsystem"]))
		{
			$fitting['shipInfo'] = self::getSubsystemInfo($fitting['Subsystem'], $fitting['shipInfo']);
		}
		
		foreach($fitting as $key => $slot)
		{
			// Ignore shipInfo
			if($key == "shipInfo")
			{
				continue;
			}
			
			$slotCounter = 0;
			foreach($slot as $slotKey => $slotValue)
			{
				// More slots are set than the ship has, so remove them
				if($slotKey >= $fitting['shipInfo'][$key])
				{
					unset($fitting[$key][$slotKey]);
				}
				
				$slotCounter++;
			}
		}
		
		return $fitting;
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
	
	private static function getShipInfo($shipName)
	{
        global $db, $table_prefix;
		
		$sql = 'SELECT *
				FROM ' . $table_prefix . 'evebbcode_ships
				WHERE LOWER(typeName) = "' . $db->sql_escape(strtolower($shipName)) . '"';
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

		return $row;
	}
	
	private static function getSubsystemInfo($subSystems, $shipInfo)
	{
        global $db, $table_prefix;
		
		// Create sql query from subsystems
		$subSystemString = "";
		foreach($subSystems as $subSystem)
		{
			$subSystemString .= "'" . $db->sql_escape(strtolower($subSystem)) . "',";
		}
		$subSystemString = substr($subSystemString, 0, -1);
		
		// SUM the amount of slots the subsystems provide
		$sql = 'SELECT SUM(Low) as Low, SUM(Medium) as Medium, SUM(High) as High, SUM(Drone) as Drone
				FROM ' . $table_prefix . 'evebbcode_subsystems
				WHERE LOWER(typeName) IN (' . $subSystemString . ')';
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
		
		// Check if items are really subsystems
		if(isset($row['Low']) && isset($row['Medium']) && isset($row['High']) && isset($row['Drone']))
		{
			// Items are subsystems, now change the ship slots to what the subsystems allow for
			$shipInfo['Low'] = $row['Low'];
			$shipInfo['Medium'] = $row['Medium'];
			$shipInfo['High'] = $row['High'];

			// Reset drone to 4 position (all the fitting panel can handle)
			if($shipInfo['Drone'] > 0)
			{
				$shipInfo['Drone'] = 4;
			}
		}
		
		return $shipInfo;
	}
	
	private static function getItemHTML($itemName, $slot, $position, $isCharge = false)
	{
		// Search for item in database
		$itemInfo = self::getItemInfo($itemName);
		
		// Set correct div id is item is a charge
		$charge = ($isCharge) ? "charge" : "";
		
		// No result? Item not found.
		if(!$itemInfo)
		{
			return array(
				'itemID'	=> false,
				'html'		=> '<div id="' . $slot . $charge . ($position + 1) . '"><img border="0" title="Unrecognized item" src="cyerus/evebbcode/images/questionmark.png"></div>'
			);
		}
		else
		{
			// Item found, so add the correct clickable icon.
			return array(
				'itemID'	=> $itemInfo['itemID'],
				'html'		=> '<div id="' . $slot . $charge . ($position + 1) . '"><img border="0" title="' . htmlentities($itemInfo['itemName']) . '" src="http://image.eveonline.com/Type/' . $itemInfo['itemID'] . '_32.png" onclick="CCPEVE.showInfo(' . $itemInfo['itemID'] . ')"  onmouseover="this.style.cursor=\'pointer\'"></div>'
			);
		}
	}
	
	private static function getEmptySlotHTML($slot, $position)
	{
		return array(
			'itemID'	=> false,
			'html'		=> '<div id="' . $slot . ($position + 1) . '"><img border="0" title="Empty ' . ucfirst($slot) . ' Slot" src="cyerus/evebbcode/images/' . $slot . '_32.png"></div>'
		);
	}
	
	private static function getFittingHTML($fitting)
	{
		$fittingHTML = "";
		$shipDNA = array();
		
		// Loop each slotType
		foreach($fitting as $slot => $positions)
		{
			// Skip shipInfo subarray as it isn't part of the fitting
			if($slot == 'shipInfo')
			{
				continue;
			}

			// Loop each slot position
			foreach($positions as $position => $row)
			{
				$itemHTML = array();
				
				if(!$row)
				{
                    // Empty slot, so add the empty slot icon for this type of slot.
                    $itemHTML = self::getEmptySlotHTML($slot, $position);
					$fittingHTML .= $itemHTML['html'];
				}
				else
				{
                    // Check if the lines has a comma, meaning it probably holds ammo/script
                    if(strpos($row, ','))
                    {
                        // Split the line, creating an array with [0] holding weapon/module and [1] holding ammo/script
                        $splitRow = explode(',', $row);
                        $itemName = trim($splitRow[0]);
                        $ammoName = trim($splitRow[1]);

                        // Even though a comma might be used, there might not be anything after it.
                        if(!empty($ammoName))
                        {
                            // Search the item in the database; mainly to receive it's itemID needed for it's icon.
							$itemHTML = self::getItemHTML($ammoName, $slot, $position, true);
							$fittingHTML .= $itemHTML['html'];
                        }
                    }
                    else
                    {
                        // No comma found, so the itemname consists of the whole line.
                        $itemName = $row;

						// Drones can sometimes have the amount of them added behind them, like 'Ogre II x5'.
						if($slot == 'Drone')
						{
							// Remove the x5 to get the correct itemname back.
							$itemName = trim(preg_replace('/x[\d]+/', '', $itemName));
						}
					}
					
					// Get the HTMl for the requested item
					$itemHTML = self::getItemHTML($itemName, $slot, $position);
					$fittingHTML .= $itemHTML['html'];
						
					// Check if item is valid
					if($itemHTML['itemID'])
					{
						// Check if item isn't already part of shipDNA array
						if(!isset($shipDNA[$slot][$itemHTML['itemID']]))
						{
							$shipDNA[$slot][$itemHTML['itemID']] = 0;
						}
						
						// Increase quantity of this item by one
						$shipDNA[$slot][$itemHTML['itemID']]++;
					}
				}
			}
		}
		
		// Create HTML from shipDNA array
		$shipDNAHTML = self::getShipDNAHTML($fitting['shipInfo']['typeID'], $shipDNA);
		
		return array(
			'html'	=> $fittingHTML,
			'dna'	=> $shipDNAHTML
		);
	}
	
	private static function getShipDNAHTML($shipID, $shipDNA)
	{
		// Create HTML from shipDNA array
		return	(string)$shipID . 
				self::getShipDNASlot($shipDNA, 'Subsystem') .
				self::getShipDNASlot($shipDNA, 'High') .
				self::getShipDNASlot($shipDNA, 'Medium') .
				self::getShipDNASlot($shipDNA, 'Low') .
				self::getShipDNASlot($shipDNA, 'Rig') .
				self::getShipDNASlot($shipDNA, 'Drone') .
				"::";
	}
	
	private static function getShipDNASlot($shipDNA, $slotType)
	{
		$partOfLink = "";

		// Check if the shipDNA array holds anything to avoid errors
		if(isset($shipDNA[$slotType]) && !empty($shipDNA[$slotType]))
		{
			foreach($shipDNA[$slotType] as $item => $amount)
			{
				// Subsystems don't require quantity
				if($slotType != 'Subsystem')
				{
					$partOfLink .= ":" . $item . ";" . $amount;
				}
				else
				{
					$partOfLink .= ":" . $item;
				}
			}
		}

		return $partOfLink;
	}
	
	private static function returnHTML($shipInfo, $fittingText, $fittingOutput, $shipDNA)
	{
        // Start fitting
        return	'<div id="fittitle"><h4>' . htmlentities($shipInfo['typeName']) . ' - ' . htmlentities($shipInfo['fittingName']) . '</h4></div>' .
				'<div id="fitting_container">' .
					'<div class="fitting_tabs">' .
						'<ul class="fit-tabs">' . 
							'<li class="fit-tab" onclick="chooseTab(this,\'loadout\');" onmouseover="this.style.cursor=\'pointer\'">Loadout</li>' . 
							'<li class="fit-tab" onclick="chooseTab(this,\'export\');" onmouseover="this.style.cursor=\'pointer\'">Export</li>' . 
							'<li class="fit-tab" onclick="CCPEVE.showFitting(\'' . $shipDNA . '\');" onmouseover="this.style.cursor=\'pointer\'">Ingame Fitting</li>' . 
						'</ul>' . 
						'<div style="clear:both;"></div>' . 
					'</div>' .
        
					'<div id="fittext" style="display:none;">' . 
						'<textarea readonly="readonly">' . htmlentities(str_replace("\n", "\r", $fittingText)) . '</textarea>' . 
					'</div>' .
		
					'<div title="fitting" id="fitting">' .
						'<div id="fittingwindow"><img border="0" alt="" src="cyerus/evebbcode/images/fitting_panel.png"></div>' . 
						'<div id="shiprace"><img border="0" alt="" title="' . $shipInfo['Icon'] . '" src="cyerus/evebbcode/images/races/' . $shipInfo['Icon'] . '.png"></div>' . 
						'<div id="shipicon"><img border="0" alt="" title="' . $shipInfo['Tech'] . ' - ' . $shipInfo['groupName'] . ' - ' . htmlentities($shipInfo['typeName']) . '" src="http://image.eveonline.com/Render/' . $shipInfo['typeID'] . '_64.png" onclick="CCPEVE.showInfo(' . $shipInfo['typeID'] . ')" onmouseover="this.style.cursor=\'pointer\'"></div>' .
						$fittingOutput .
					'</div>' . 
				'</div>';
	}
}

?>