<?php

/**
 * @package com_spproperty
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Form\Form;
use Joomla\Filesystem\File;
use Joomla\CMS\Access\Access;
use Joomla\Registry\Registry;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Application\ApplicationHelper;


/**
 * An example custom profile plugin.
 *
 * @since  1.6
 */
class PlgUserSppropertyprofile extends CMSPlugin
{
    private $date = '';
    protected $autoloadLanguage = true;

    private function debug($data, $die = true)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        if ($die) {
            die;
        }
    }

    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        FormHelper::addFieldPath(__DIR__ . '/field');
    }


    public function onContentPrepareData($context, $data)
    {
        // Check we are manipulating a valid form.
        if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile'))) {
            return true;
        }

        if (is_object($data)) {
            $userId = isset($data->id) ? $data->id : 0;

            if (!isset($data->profile) && $userId > 0) {
                // Load the profile data from the database.
                $db = Factory::getDbo();
                $db->setQuery(
                    'SELECT profile_key, profile_value FROM #__user_profiles'
                        . ' WHERE user_id = ' . (int) $userId . " AND profile_key LIKE 'sppropertyprofile.%'"
                        . ' ORDER BY ordering'
                );

                try {
                    $results = $db->loadRowList();
                } catch (RuntimeException $e) {
                    Factory::getApplication()->enqueueMessage($e->getMessage());
                    return false;
                }

                // Merge the profile data.
                $data->sppropertyprofile = array();

                foreach ($results as $v) {
                    $k = str_replace('sppropertyprofile.', '', $v[0]);
                    $data->sppropertyprofile[$k] = json_decode($v[1], true);

                    if ($data->sppropertyprofile[$k] === null) {
                        $data->sppropertyprofile[$k] = $v[1];
                    }
                }
            }
        }

        return true;
    }


    public function onContentPrepareForm(Form $form, $data)
    {

        // Check we are manipulating a valid form.
        $name = $form->getName();


        if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration'))) {
            return true;
        }

        // Add the registration fields to the form.
        Form::addFormPath(__DIR__ . '/profile');
        $form->loadFile('profile');

        $fields = array(
            'designation',
            'phone',
            'mobile',
            'skype',
            'image',
            'description',
            'facebook',
            'twitter',
            'linkedin',
            'gplus',
            'user_group',
        );

        // Change fields description when displayed in frontend or backend profile editing
        $app = Factory::getApplication();

        foreach ($fields as $field) {
            // Case using the users manager in admin
            if ($name === 'com_users.user') {
                // Toggle whether the field is required.
                if ($this->params->get('profile-require_' . $field, 1) > 0) {
                    $form->setFieldAttribute($field, 'required', ($this->params->get('profile-require_' . $field) == 2) ? 'required' : '', 'sppropertyprofile');
                }
                // Remove the field if it is disabled in registration and profile
                elseif (
                    $this->params->get('register-require_' . $field, 1) == 0
                    && $this->params->get('profile-require_' . $field, 1) == 0
                ) {
                    $form->removeField($field, 'sppropertyprofile');
                }
            }
            // Case registration
            elseif ($name === 'com_users.registration') {
                // Toggle whether the field is required.
                if ($this->params->get('register-require_' . $field, 1) > 0) {
                    $form->setFieldAttribute($field, 'required', ($this->params->get('register-require_' . $field) == 2) ? 'required' : '', 'sppropertyprofile');
                } else {
                    $form->removeField($field, 'sppropertyprofile');
                }
            }
            // Case profile in site or admin
            elseif ($name === 'com_users.profile' || $name === 'com_admin.profile') {
                // Toggle whether the field is required.
                if ($this->params->get('profile-require_' . $field, 1) > 0) {
                    $form->setFieldAttribute($field, 'required', ($this->params->get('profile-require_' . $field) == 2) ? 'required' : '', 'sppropertyprofile');
                } else {
                    $form->removeField($field, 'sppropertyprofile');
                }
            }
        }

        return true;
    }

    public function onUserBeforeSave($user, $isnew, $data)
    {
        // Check that the tos is checked if required ie only in registration from frontend.
        $task       = Factory::getApplication()->input->getCmd('task');
        $option     = Factory::getApplication()->input->getCmd('option');
        return true;
    }

    private function handleAlias($title, $data)
    {
        $alias = ApplicationHelper::stringURLSafe($title) . '-' . $data['id'];
        if (trim(str_replace('-', '', $alias)) == '') {
            $alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }
        return $alias;
    }

    /**
     * Saves user profile data
     *
     * @param   array    $data    entered user data
     * @param   boolean  $isNew   true if this is a new user
     * @param   boolean  $result  true if saving the user worked
     * @param   string   $error   error message
     *
     * @return  boolean
     */
    public function onUserAfterSave($data, $isNew, $result, $error)
    {
        $userId = ArrayHelper::getValue($data, 'id', 0, 'int');
        $group  = isset($data['sppropertyprofile']) ? ArrayHelper::getValue($data['sppropertyprofile'], 'group', 0, 'int') : null;

        if ($userId && $result && isset($data['sppropertyprofile']) && count($data['sppropertyprofile'])) {
            try {
                $db = Factory::getDbo();
                $keys = array_keys($data['sppropertyprofile']);
                $keys[] = 'alias';


                $data['sppropertyprofile']['alias'] = $this->handleAlias($data['name'], $data);

                foreach ($keys as &$key) {
                    $key = 'sppropertyprofile.' . $key;
                    $key = $db->quote($key);
                }

                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__user_profiles'))
                    ->where($db->quoteName('user_id') . ' = ' . (int) $userId)
                    ->where($db->quoteName('profile_key') . ' IN (' . implode(',', $keys) . ')');
                $db->setQuery($query);
                $db->execute();

                $query = $db->getQuery(true)
                    ->select($db->quoteName('ordering'))
                    ->from($db->quoteName('#__user_profiles'))
                    ->where($db->quoteName('user_id') . ' = ' . (int) $userId);
                $db->setQuery($query);
                $usedOrdering = $db->loadColumn();
                $usedOrdering[] = 'alias';

                $tuples = array();
                $order = 1;

                foreach ($data['sppropertyprofile'] as $k => $v) {
                    while (in_array($order, $usedOrdering)) {
                        $order++;
                    }

                    $tuples[] = '(' . $userId . ', ' . $db->quote('sppropertyprofile.' . $k) . ', ' . $db->quote(json_encode($v)) . ', ' . ($order++) . ')';
                }
                $db->setQuery('INSERT INTO' . $db->quoteName('#__user_profiles') . 'VALUES ' . implode(', ', $tuples));
                $db->execute();


                if ((isset($group) && count((array) $group) && $userId )) {
                    //set user group
                    $this->setJoomlaUserGroup($userId, $group);
                }

                if (!is_null($group)) {
                    $groupTitle = Access::getGroupTitle($group);
                    $agentGroup = ComponentHelper::getParams('com_spproperty')->get('agent_group_name', 'Agent');

                    if ($isNew && $groupTitle === $agentGroup) {

                        $image = Factory::getApplication()->input->files->get('jform', [], 'array');
                        $image = $image['sppropertyprofile']['image'] ?? null;
                        $date = new Date('now', Factory::getApplication()->get('offset'));

                        $entry = new \stdClass();
                        $entry->title = $data['name'];
                        $entry->designation = $data['sppropertyprofile']['designation'];
                        $entry->mobile = $data['sppropertyprofile']['phone'];
                        $entry->phone = $data['sppropertyprofile']['mobile'];
                        $entry->skype = $data['sppropertyprofile']['skype'];
                        $entry->email = $data['email'];
                        $entry->description = $data['sppropertyprofile']['description'];
                        $entry->facebook = $data['sppropertyprofile']['facebook'];
                        $entry->twitter = $data['sppropertyprofile']['twitter'];
                        $entry->linkedin = $data['sppropertyprofile']['linkedin'];
                        $entry->gplus = $data['sppropertyprofile']['gplus'];
                        $entry->created_by = $userId;
                        $entry->created = (string) $date;

                        if (isset($image['name']) && !empty($image['name'])) {
                            $isImageUploaded = $this->uploadAgentImage($image);
                            $entry->image = $isImageUploaded ? 'images/' . $image['name'] : null;
                        }

                        $db->insertObject('#__spproperty_agents', $entry);
                    }
                }
            } catch (RuntimeException $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                return false;
            }
        }

        return true;
    }

    /**
     * Remove all user profile information for the given user ID
     *
     * Method is called after user data is deleted from the database
     *
     * @param   array    $user     Holds the user data
     * @param   boolean  $success  True if user was succesfully stored in the database
     * @param   string   $msg      Message
     *
     * @return  boolean
     */
    public function onUserAfterDelete($user, $success, $msg)
    {
        if (!$success) {
            return false;
        }

        $userId = ArrayHelper::getValue($user, 'id', 0, 'int');

        if ($userId) {
            try {
                $db = Factory::getDbo();
                $db->setQuery(
                    'DELETE FROM #__user_profiles WHERE user_id = ' . $userId
                        . " AND profile_key LIKE 'sppropertyprofile.%'"
                );

                $db->execute();
            } catch (Exception $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage());
                return false;
            }
        }

        return true;
    }

    private function setJoomlaUserGroup($userid, $group)
    {

        // get allowed user groups
        $plugin = PluginHelper::getPlugin('user', 'sppropertyprofile');
        $params = new Registry($plugin->params);
        $groups = $params->get('user_groups', array());

        if($group)
        {
            array_push($groups,$group);
        }
        
        $this->removeUserGroups($groups, $userid);

        if ($group) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $columns = array('user_id', 'group_id');
            $query->insert('#__user_usergroup_map');
            $query->columns($db->quoteName($columns));
            $query->values($userid . ',' . $group);
            $db->setQuery($query);
            $db->execute();

            $last_user_Id = $db->insertid();
        }
    }

    private function removeUserGroups($groups = array(), $userid = 0, $group = null)
    {
        if (!empty($groups)) {
            // access check
            $user = Factory::getUser($userid);
            $isRoot = $user->get('isRoot');

            // implode allowed user groups
            $groupids = implode(',', $groups);

            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->delete('#__user_usergroup_map');
            $query->where($db->quoteName('user_id') . ' = ' . $db->quote($userid));
            if ($groupids) {
                $query->where($db->quoteName('group_id') . " IN (" . $groupids . ")");
            }
            $db->setQuery($query);
            $db->execute();
            $query->clear();
        }   
    }

    /**
     * Handles the uploading of an image for an agent.
     *
     * @param  array        $image The image array from the $_FILES superglobal, containing information about the 
     *                              uploaded file.
     * @return bool         Returns true if the image passes validation and is successfully uploaded, false otherwise.
     * @throws Throwable    If any error occurs during the upload process, the error will be caught and rethrown.
     * @since  4.1.2
     */
    private function uploadAgentImage($image)
    {    
        try {
            $canUpload      = (new MediaHelper())->canUpload($image);
            $destination    = JPATH_ROOT . '/images/' . $image['name'];
            $src            = $image['tmp_name'];

            if ($canUpload && File::upload($src, $destination)) {
                return true;
            }

            return false;
        } catch (Throwable $error) {
            throw $error;
        }        
    }
}
