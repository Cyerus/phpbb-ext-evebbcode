<?php
/**
*
* EveBBcode - An phpBB extension adding EVE Online based BBcodes to your forum.
*
* @copyright (c) 2015 Jordy Wille (http://github.com/cyerus)
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

/**
* NOTE: Most of the language items are used in javascript
* If you want to use quotes or other chars that need escaped, be sure you escape them double 
* (Especially for ', you must use \\\' instead of \'. For " you only need to use \".
*/
/**
 * @TODO:Change this to uppercase things.
 */ 
$lang = array_merge($lang, array(
	'EVEBBCODE_EVEITEM_HELPLINE'	=>	'The [eveitem] bbcode allows items to be viewed using the in-game browser.',
	'EVEBBCODE_EVESYSTEM_HELPLINE'	=>	'The [evesystem] bbcode allows systems to be viewed using the in-game browser.',
	'EVEBBCODE_EVEFIT_HELPLINE'		=>	'The [evefit] bbcode allows fittings to be viewed using any browser, and has the ability to import them using the in-game browser.',
));
