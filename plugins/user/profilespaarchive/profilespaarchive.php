<?php
/**
* @package com_profilespaarchive
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2021 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Filesystem\Folder;
use Joomla\Utilities\ArrayHelper;

class plgUserProfilespaarchive extends CMSPlugin
{
	protected static $avatar = array();

	protected static $last_avatar = '';

	function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile','com_users.registration','com_users.user','com_admin.profile')))
		{
			return true;
		}
 
		$userId = isset($data->id) ? $data->id : 0;
 
		// Load the profile data from the database.
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('profile_key, profile_value');
		$query->from($db->quoteName('#__user_profiles'));
		$query->where($db->quoteName('user_id') . '=' . (int) $userId, 'AND');
		$query->where($db->quoteName('profile_key') . ' LIKE ' . '\'profilespaarchive.%\'');
		$query->order($db->quoteName('ordering'));

		$db->setQuery($query);

		// Check for a database error.
		try
		{
			$results = $db->loadRowList();
		} catch (\Exception $e) {
			$this->_subject->setError($db->getErrorMsg());
			return false;
		}

		// Merge the profile data.
		if (isset($results) && !empty($results))
		{
			$data->profilespaarchive = array();

			foreach ($results as $v)
			{
				$k = str_replace('profilespaarchive.', '', $v[0]);
				$data->profilespaarchive[$k] = json_decode($v[1], true);
			}
		}

		return true;
	}
 
	/**
	 * @param	JForm	The form to be altered.
	 * @param	array	The associated data for the form.
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareForm($form, $data)
	{
		$doc = Factory::getDocument();
		$plg_path = Uri::root(true).'/plugins/user/profilespaarchive';
		$doc->addStyleSheet($plg_path.'/assets/css/style.css');

		// Load user_profile plugin language
		$lang = Factory::getLanguage();
		$lang->load('plg_user_profilespaarchive', JPATH_ADMINISTRATOR);
 
		if (!($form instanceof Form))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}
		// Check we are manipulating a valid form.
		if (!in_array($form->getName(), array('com_users.profile', 'com_users.registration','com_users.user','com_admin.profile')))
		{
			return true;
		}

		if ($form->getName()=='com_users.profile')
		{
			// Add the profile fields to the form.
			Form::addFormPath(dirname(__FILE__).'/profiles');
			$form->loadFile('profile', false);
 
			// Toggle whether the designation field is required.
			if ($this->params->get('profile-require_designation', 1) > 0)
			{
				$form->setFieldAttribute('designation', 'required', $this->params->get('profile-require_designation') == 2, 'profilespaarchive');
			}
			else
			{
				$form->removeField('designation', 'profilespaarchive');
			}

			// Toggle whether the description field is required.
			if ($this->params->get('profile-require_description', 1) > 0)
			{
				$form->setFieldAttribute('description', 'required', $this->params->get('profile-require_description') == 2, 'profilespaarchive');
			} 
			else
			{
				$form->removeField('description', 'profilespaarchive');
			}

			// Toggle whether the socials field is required.
			if ($this->params->get('profile-require_socials', 1) > 0)
			{
				$form->setFieldAttribute('socials', 'required', $this->params->get('profile-require_socials') == 2, 'profilespaarchive');
			}
			else
			{
				$form->removeField('socials', 'profilespaarchive');
			}
		}
 
		//In this example, we treat the frontend registration and the back end user create or edit as the same. 
		elseif ($form->getName()=='com_users.registration' || $form->getName()=='com_users.user')
		{		
			// Add the registration fields to the form.
			Form::addFormPath(dirname(__FILE__).'/profiles');
			$form->loadFile('profile', false);

			// Toggle whether the designation field is required.
			if ($this->params->get('register-require_designation', 1) > 0)
			{
				$form->setFieldAttribute('designation', 'required', $this->params->get('register-require_designation') == 2, 'profilespaarchive');
			}
			else
			{
				$form->removeField('designation', 'profilespaarchive');
			}

			// Toggle whether the description field is required.
			if ($this->params->get('register-require_description', 1) > 0)
			{
				$form->setFieldAttribute('description', 'required', $this->params->get('register-require_description') == 2, 'profilespaarchive');
			}
			else
			{
				$form->removeField('description', 'profilespaarchive');
			}

			// Toggle whether the socials field is required.
			if ($this->params->get('register-require_socials', 1) > 0) {
				$form->setFieldAttribute('socials', 'required', $this->params->get('register-require_socials') == 2, 'profilespaarchive');
			} else {
				$form->removeField('socials', 'profilespaarchive');
			}
		}			
	}

	// check file type
	protected static function fileExtensionCheck($file, $allowed)
	{
		$ext = pathinfo($file['profilespaarchive']['author_avatar']['name'], PATHINFO_EXTENSION);

		if(in_array( strtolower($ext), $allowed) )
		{
			return true;
		}
		
		return false;
	}

	function onUserBeforeSave($user, $isnew, $new)
	{
		//Import filesystem libraries. Perhaps not necessary, but does not hurt
		$input = Factory::getApplication()->input;
		self::$avatar = $input->files->get('jform', null);

		//get last uploaded avatar
		self::$last_avatar = json_decode(self::existAvatar($new['id'])->profile_value)->avatar;
		
		// New avatar
		$avatar_name 	= self::$avatar['profilespaarchive']['author_avatar']['name'];
		$avatar_ext 	= strtolower(File::getExt($avatar_name));

		// allow types
        $allowed_types = array('image' => array('jpg', 'jpeg', 'png'));

		if ( isset($avatar_name) && $avatar_name !='' )
		{
			if ((!self::fileExtensionCheck(self::$avatar, array('png', 'jpg', 'jpeg'))) && !in_array($avatar_ext, $allowed_types))
			{
				throw new RuntimeException(Text::_('INVALID_FILE_TYPE'), 1);
			}
			elseif (self::$avatar['profilespaarchive']['author_avatar']['size'] > 800000)
			{
				throw new RuntimeException(Text::_('INVALID_FILE_SIZE'), 1);
			}
		}	
	}

 
	function onUserAfterSave($data, $isNew, $result, $error){

		$userId	= ArrayHelper::getValue($data, 'id', 0, 'int');
		$avatar_name = self::$avatar['profilespaarchive']['author_avatar']['name'];

		// has avatar
		if (isset($avatar_name) && $avatar_name != '')
		{
			$folder_path = JPATH_ROOT . '/media/com_spauthorarchive/users/';

			if (!Folder::exists($folder_path))
			{
				Folder::create($folder_path);
			}
			// Cleans the name of teh file by removing weird characters
			$filename = File::makeSafe($avatar_name); 

			$src = self::$avatar['profilespaarchive']['author_avatar']['tmp_name'];
		}
 
		if (($userId && $result && isset($data['profilespaarchive']) && (count($data['profilespaarchive']))) || $src)
		{
			try
			{
				if (isset($avatar_name) && $avatar_name != '')
				{
					$exist_avatar = json_decode(self::existAvatar($userId)->profile_value)->avatar;

					if (isset($exist_avatar) && $exist_avatar != '')
					{
						File::delete(JPATH_ROOT.$exist_avatar);
					}	
				}

				$db = Factory::getDbo();
				
				$query = $db->getQuery(true);
				$query->select('profile_value');
				$query->from($db->quoteName('#__user_profiles'));
				$query->where($db->quoteName('profile_key') . ' = '. $db->quote('profilespaarchive.avatar'));
				
				$db->setQuery($query);
				$result = $db->loadObject();

				$db->setQuery('DELETE FROM #__user_profiles WHERE user_id = '.$userId.' AND profile_key LIKE \'profilespaarchive.%\'');
				$db->execute();
				// if (!$db->query()) {
				// 	throw new Exception($db->getErrorMsg());
				// }
 				
 				if ((isset($avatar_name) && $avatar_name != ''))
				{
	 				$file_path = '/media/com_spauthorarchive/users/' . $userId . '-' . $filename;

					// if file upload then insert into DB
					if (File::upload($src, $folder_path. '/' . $userId . '-' . $filename))
					{
						$data['profilespaarchive']['avatar'] = array('avatar'=> $file_path);
					}
				}

				// already has avatar and not uploaded
				if (self::$last_avatar != '' && $avatar_name =='')
				{
					$data['profilespaarchive']['avatar'] = array('avatar' => self::$last_avatar);
				}
				elseif (self::$last_avatar == '' && $avatar_name =='')
				{
					$data['profilespaarchive']['avatar'] = array('avatar'=> '');
				}

				$tuples = array();
				$order	= 1;
				foreach ($data['profilespaarchive'] as $k => $v)
				{
					$tuples[] = '('.$userId.', '.$db->quote('profilespaarchive.'.$k).', '.$db->quote(json_encode($v)).', '.$order++.')';
				}
				$query->clear();
				// Insert columns.
				$columns = array('user_id', 'profile_key', 'profile_value', 'ordering');

				$query->insert($db->quoteName('#__user_profiles'));
				$query->columns($db->quoteName($columns));
				$query->values(implode(',', $tuples));

				$query = (string) $query;
				$query = preg_replace("@\(\(@", "(", $query);
				$query = preg_replace("@\)\)@", ")", $query);
				
				$db->setQuery($query);
				$db->execute();
				


				// if (!$db->query()) {
				// 	throw new Exception($db->getErrorMsg());
				// }
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
				
				return false;
			}
		}
 
		return true;
	}

	protected static function existAvatar($user_id){
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('profile_value');
		$query->from($db->quoteName('#__user_profiles'));
		$query->where($db->quoteName('user_id')." = ".$user_id);
		$query->where($db->quoteName('profile_key') . ' = '. $db->quote('profilespaarchive.avatar'));
		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}
 
		$userId	= ArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__user_profiles'))
				->where($db->quoteName('user_id') . ' = ' . $db->quote($userId))
				->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('profilespaarchive.%'));

			$db->setQuery($query);
			$db->execute();
		}
 
		return true;
	}
 
 
 }