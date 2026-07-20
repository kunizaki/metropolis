<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// Base this model on the backend version.
JLoader::register('SppropertyModelAgent', JPATH_ADMINISTRATOR . '/components/com_spproperty/models/agent.php');

jimport('joomla.filesystem.file');

class SppropertyModelForm extends SppropertyModelAgent
{
    protected function populateState()
    {

        $app = Factory::getApplication('site');

        $agentId = $app->input->getInt('id');
        $this->setState('agent.id', $agentId);

        $return = $app->input->get('return', null, 'base64');
        $this->setState('return_page', base64_decode($return));

        $params = $app->getParams();
        $this->setState('params', $params);

        $user = Factory::getUser();

        if ((!$user->authorise('core.edit.state', 'com_spproperty')) && (!$user->authorise('core.edit', 'com_spproperty'))) {
            $this->setState('filter.published', 1);
        }
    }

    public function getItem($itemId = null)
    {
        $itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('agent.id');

        $table = $this->getTable();
        $return = $table->load($itemId);

        if ($return === false && $table->getError()) {
            throw new Exception($table->getError());
            return false;
        }

        $properties = $table->getProperties(1);
        $value = ArrayHelper::toObject($properties, 'stdClass');

        return $value;
    }

    // Get Agent info by id
    public static function agentInfo($userid = '')
    {

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select(array('a.*'));
        $query->from($db->quoteName('#__spproperty_agents', 'a'));
        $query->where($db->quoteName('a.created_by') . '=' . $userid);
        $query->where($db->quoteName('a.published') . ' = 1');
        $db->setQuery($query);
        $result = $db->loadObject();

        return $result;
    }

    public function save($data)
    {
        $app = Factory::getApplication();
        $table = $this->getTable();

        if ($table->save($data) === true) {
            $this->setState('agent.id', $table->id);
        } else {
            return false;
        }

        return true;
    }
}
