
<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

class SppropertyTableVisitrequest extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__spproperty_visitrequests', 'id', $db);
    }

    public function bind($src, $ignore = array())
    {
        return parent::bind($src, $ignore);
    }

    public function store($updateNulls = false)
    {
        $user = Factory::getUser();
        $app  = Factory::getApplication();
        $date = new Date('now', $app->getCfg('offset'));

        if ($this->id) {
            $this->modified = (string)$date;
            $this->modified_by = $user->get('id');
        }

        if (empty($this->created)) {
            $this->created = (string)$date;
        }

        if (empty($this->created_by)) {
            $this->created_by = $user->get('id');
        }

        $table = Table::getInstance('Visitrequest', 'SppropertyTable');

        return parent::store($updateNulls);
    }

    public function check()
    {
        if (trim($this->customer_name) == '') {
            throw new UnexpectedValueException(sprintf('The customer name field is empty'));
        }

        $userid = Factory::getUser()->get('id');

        if (empty($userid)) {
            $this->userid = 0;
        } else {
            $this->userid = $userid;
        }
        return true;
    }


    public function publish($pks = null, $published = 1, $userId = 0)
    {
        $k = $this->_tbl_key;

        ArrayHelper::toInteger($pks);
        $publilshed = (int) $published;

        if (empty($pks)) {
            if ($this->$k) {
                $pks = array($this->$k);
            } else {
                $this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }

        $where = $k . '=' . implode(' OR ' . $k . ' = ', $pks);

        $query = $this->_db->getQuery(true)
            ->update($this->_db->quoteName($this->_tbl))
            ->set($this->_db->quoteName('published') . ' = ' . $published)
            ->where($where);

        $this->_db->setQuery($query);

        try {
            $this->_db->execute();
        } catch (RuntimeException $e) {
            $this->setError($e->getMessage());
            return false;
        }

        if (in_array($this->$k, $pks)) {
            $this->published = $published;
        }

        $this->setError('');
        return true;
    }
}
