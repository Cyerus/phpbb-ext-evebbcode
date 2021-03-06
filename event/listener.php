<?php
/**
*
* EveBBcode - An phpBB extension adding EVE Online based BBcodes to your forum.
*
* @copyright (c) 2015 Jordy Wille (http://github.com/cyerus)
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace cyerus\evebbcode\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	protected $db;
	protected $user;
	protected $template;
	protected $config;
	protected $helper;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver $db Database object
	* @param \phpbb\controller\helper    $helper        Controller helper object
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\template\template $template, \phpbb\config\config $config, \phpbb\controller\helper $helper)
	{
		$this->db = $db;
		$this->user = $user;
		$this->template = $template;
		$this->config = $config;
		$this->helper = $helper;
	}

	/**
	* Load common files during user setup
	*
	* @param object $event The event object
	* @access public
	* @return array Events
	* @static function
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'								=> 'evebbcode_user_setup',

			'core.modify_text_for_display_after'			=> 'evebbcode_modify_text_for_display_after',
			'core.modify_format_display_text_after'			=> 'evebbcode_modify_format_display_text_after'
		);
	}

	/**
	* Load common files during user setup
	*
	* @param object $event The event object
	* @access public
	*/
	public function evebbcode_user_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'cyerus/evebbcode',
			'lang_set' => 'evebbcode',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	* Change the BBcodes to something webbrowsers can understand
	*
	* @param string $message The original message containing the BBcodes
	* @return string Proper HTML
	*/
	protected function evebbcode_do_magic($message)
	{
		// Change the <br /> back to \n
		$message = preg_replace('#<br />#si', "\n", $message);
		
		// Change the 
		$message = preg_replace_callback("#\[eveitem\]([0-9a-z \-\'\:]{1,80})\[/eveitem\]#is", array('cyerus\evebbcode\core\Item', 'getItem'), $message);
		$message = preg_replace_callback("#\[evesystem\]([0-9a-z \-]{1,30})\[/evesystem\]#is", array('cyerus\evebbcode\core\System', 'getSystem'), $message);
		$message = preg_replace_callback("#\[evefit\]([0-9a-z \-\'\n\[\]\,\#\:\(\)]{1,2000})\[/evefit\]#is", array('cyerus\evebbcode\core\Fitting', 'getFitting'), $message);
		
		// Change the \n back to <br />
		$message = preg_replace('#\n#si', "<br />", $message);
		
		return $message;
	}

	/**
	* Alter the EVE BBCodes when viewing a topic
	*
	* @param object $event The event object
	*/
	public function evebbcode_modify_text_for_display_after($event)
	{
		// Flags seems to let us know what the type of 'Text' is.
		// I haven't found a list defining them yet, so its guesswork for now.
		// 
		// 2 - Topicpost or pm without any BBcodes.
		// 3 - Topicpost or pm with one or more BBcodes.
		// 7 - Subtitle of forum ("Description of your first forum.")
		if ($event['flags'] == 3)
		{
			$event['text'] = $this->evebbcode_do_magic($event['text']);
		}
	}

	/**
	* Alter the EVE BBCodes when previewing a topic reply
	*
	* @param object $event The event object
	*/
	public function evebbcode_modify_format_display_text_after($event)
	{
		$event['text'] = $this->evebbcode_do_magic($event['text']);
	}
}
