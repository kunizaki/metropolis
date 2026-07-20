<?php
/**
 * @package     SP Author Archive
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldSbfile extends FormField
{

	protected $type = 'Sbfile';
	protected $accept;

	public function __get($name) {
		switch ($name) {
			case 'accept':
			return $this->$name;
		}
		return parent::__get($name);
	}

	public function __set($name, $value) {
			switch ($name) {
			case 'accept':
				$this->$accept = (string) $value;
			break;

			default:
			parent::__set($name, $value);
		}
	}
	
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->accept = (string) $this->element['accept'];
		}

		return $return;
	}
	protected function getInput(){
		// Initialize some field attributes.
		$accept    = !empty($this->accept) ? ' accept="' . $this->accept . '"' : '';
		$size      = !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$class     = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$disabled  = $this->disabled ? ' disabled' : '';
		$required  = $this->required ? ' required aria-required="true"' : '';
		$autofocus = $this->autofocus ? ' autofocus' : '';
		$multiple  = $this->multiple ? ' multiple' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

		// Including fallback code for HTML5 non supported browsers.
		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'system/html5fallback.js', [], true);
		$app = Factory::getApplication();

		$file_preview = '';
		// Get the input.
		$input = Factory::getApplication()->input;
		$Itemid = $input->get('id',0,'INT');

		if ($app->isClient('administrator')) {
			// avatar
			$avatar='';
			$jinput = Factory::getApplication()->input;
			
			if ( $jinput->get('id') != '' && self::getUserProfileById($jinput->get('id')) !='' && self::getUserProfileById($jinput->get('id')) ) {
				foreach(self::getUserProfileById($jinput->get('id')) as $data)
				{
					if ($data->profile_key == "profilespaarchive.avatar")
					{
						$avatar = json_decode($data->profile_value)->avatar;
					}
				}
			}

			
			

			if ( isset($avatar) && $avatar ) {
				$file_preview = '<div class="authorarchive-profile-image-preview">';
				$file_preview .= '<h4>user profile picture</h4>';
				$file_preview .= '<img src="' . Uri::root(true). $avatar . '" width="150" />';
				$file_preview .= '</div>';
			}

	
			// has attachment
			if ($this->value && $this->fieldname =='attachment') {
				$file_preview  = '<div class="spauthorarchive-attached-file">';
				$file_preview .= '<a id="spauthorarchive-attachment-file" href="' . Uri::root(true). '/'. $this->value . '">' . $this->value . '</a> ';
				if (isset($this->value) && $this->value) {
					$file_preview .= ' <a id="spauthorarchive-remove-attachment" class="spauthorarchive-remove-attachment" href="#" data-file="' . JPATH_ROOT . '/' . $this->value . '" data-id="'. $Itemid .'">('. Text::_('COM_SPAUTHORARCHIVE_REMOVE_ATTACHMENT') . ')</a>';
				}

				$file_preview .= '</div>';
			}
		}

		//$jinput = JFactory::getApplication()->input;
		if ($app->isClient('site')) {
			$jinput = Factory::getApplication()->input;
			$user = Factory::getUser();
			$avatar='';

			if (self::getUserProfileById($user->get('id')) !='' && self::getUserProfileById($user->get('id'))) {
				foreach(self::getUserProfileById($user->get('id')) as $data)
				{
					if ($data->profile_key == "profilespaarchive.avatar")
					{
						$avatar = json_decode($data->profile_value)->avatar;
					}
				}
			}
			
			if ( isset($avatar) && $avatar ) {
				$file_preview = '<div class="authorarchive-profile-image-preview">';
				$file_preview .= '<h4>Your profile picture</h4>';
				$file_preview .= '<img src="' . Uri::root(true). $avatar . '" width="150" />';
				$file_preview .= '</div>';
			}
		}


		return '<input type="file" name="' . $this->name . '" id="' . $this->id . '" ' . $accept
			. $disabled . $class . $size . $onchange . $required . $autofocus . $multiple . ' />' . $file_preview;
	}

	protected static function getUserProfileById( $user_id = NULL ){
		// Get a database object.
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$sql = "SELECT * FROM `#__user_profiles` WHERE `user_id` = $user_id";
		$db->setQuery($sql);

		return $db->loadObjectList();
	}

}
