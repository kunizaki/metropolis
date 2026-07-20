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
use Joomla\CMS\Image\Image;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Application\ApplicationHelper;

class SppropertyTableProperty extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__spproperty_properties', 'id', $db);
    }

    public function bind($src, $ignore = array())
    {
        if (isset($src['gallery']) && is_array($src['gallery'])) {
            $registry = new Registry();
            $registry->loadArray($src['gallery']);
            $src['gallery'] = (string) $registry;
        }


        if (isset($src['features']) && is_array($src['features'])) {
            $src['features'] = json_encode($src['features']);
        }

        if (isset($src['latitude']) && isset($src['longitude'])) {
            $src['map'] = $src['latitude'] . ',' . $src['longitude'];
        }

        if (isset($src['floor_plans']) && is_array($src['floor_plans'])) {
            $registry = new Registry();
            $registry->loadArray($src['floor_plans']);
            $src['floor_plans'] = (string) $registry;
        }

        return parent::bind($src, $ignore);
    }

    private function isUnique($value, $pk, $table = '#__spproperty_properties', $column = 'property_id')
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from($db->qn($table))
            ->where($db->qn($column) . ' = ' . $db->q($value));
        if ($pk) {
            $query->where($db->qn('id') . ' != ' . $db->q($pk));
        }
        $db->setQuery($query);
        $result = $db->loadResult();
        if (!empty($result)) {
            return false;
        }
        return true;
    }

    public function store($updateNulls = true)
    {
        $user = Factory::getUser();
        $app  = Factory::getApplication();
        $date = new Date('now', $app->getCfg('offset'));
        if ($this->id) {
            $this->modified = (string)$date;
            $this->modified_by = $user->get('id');
            if (empty($this->property_id)) {
                $this->property_id = SppropertyHelper::generateID($this->title, $this->created);
                while (!$this->isUnique($this->property_id, $this->id)) {
                    $this->property_id = SppropertyHelper::generateID($this->title, $this->created);
                }
            }
        } else {
            if (empty($this->property_id)) {
                $this->property_id = SppropertyHelper::generateID($this->title, $date);
            }
        }

        if (empty($this->created)) {
            $this->created = (string)$date;
        }

        if (empty($this->created_by)) {
            $this->created_by = $user->get('id');
        }

        $table = Table::getInstance('Property', 'SppropertyTable');

        if ($table->load(['alias' => $this->alias]) && ($table->id != $this->id || $this->id == 0)) {
            $this->setError(Text::_('COM_SPPROPERTY_ERROR_UNIQUE_ALIAS'));
            return false;
        }

        if (!$this->isUnique($this->property_id, $this->id)) {
            $this->setError(Text::_('COM_SPPROPERTY_ERROR_UNIQUE_PROPERTY_ID'));
            return false;
        }

        return parent::store(true);
    }

    public function check()
    {
        $params         = ComponentHelper::getParams('com_spproperty');
        $thumb          = $params->get('property_thumbnail', '360x207');
        $thumb_tower    = $params->get('property_thumbnail_tower', '640x715');
        $thumbs         = array($thumb,$thumb_tower);


        if (trim($this->title) == '') {
            throw new UnexpectedValueException(sprintf('The title is empty'));
        }

        $this->handleAlias();

        if (!empty($thumbs)) {
            $this->makeThumbs($this->image, $thumbs);
        }

        $galleries = json_decode($this->gallery);

        if (isset($galleries) && !empty($galleries)) {
            foreach ($galleries as $key => $g) {
                if (!empty($thumbs)) {
                    $this->makeThumbs($g->photo, $thumbs);
                }
            }
        }

        return true;
    }

    private function makeThumbs($image, array $thumbs)
    {
        if (isset($image) && $image) {
            $image = JPATH_ROOT . '/' . $image;
            $sizes = $thumbs;
            if (file_exists($image)) {
                $image = new Image($image);
                $image->generateThumbs($sizes, 5);
            }
        }
    }

    private function handleAlias()
    {
        if (empty($this->alias)) {
            $this->alias = $this->title;
        }

        $this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

        if (trim(str_replace('-', '', $this->alias)) == '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }
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
