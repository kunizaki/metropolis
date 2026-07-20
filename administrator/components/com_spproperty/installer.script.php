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
use Joomla\CMS\Installer\Installer;
use Joomla\Database\DatabaseInterface;

class com_sppropertyInstallerScript
{
    public function uninstall($parent)
    {

        $extensions = array(
            array('type' => 'module', 'name' => 'mod_spproperty_properties'),
            array('type' => 'module', 'name' => 'mod_spproperty_search'),
            array('type' => 'module', 'name' => 'mod_spproperty_emi_calculator'),
            array('type' => 'module', 'name' => 'mod_spproperty_agents'),
            array('type' => 'module', 'name' => 'mod_spproperty_categories'),
            array('type' => 'plugin', 'name' => 'sppropertyupdater'),
            array('type' => 'plugin', 'name' => 'sppropertyprofile'),
            array('type' => 'plugin', 'name' => 'spproperty')
            );

        foreach ($extensions as $key => $extension) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true);
            $query->select($db->quoteName(array('extension_id')));
            $query->from($db->quoteName('#__extensions'));
            $query->where($db->quoteName('type') . ' = ' . $db->quote($extension['type']));
            $query->where($db->quoteName('element') . ' = ' . $db->quote($extension['name']));
            $db->setQuery($query);
            $id = $db->loadResult();

            if (isset($id) && $id) {
                $installer = new Installer();
                $installer->setDatabase($db);
                $result = $installer->uninstall($extension['type'], $id);
            }
        }
    }

    function postflight($type, $parent)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $extensions = array(
            array('type' => 'module', 'name' => 'mod_spproperty_properties'),
            array('type' => 'module', 'name' => 'mod_spproperty_search'),
            array('type' => 'module', 'name' => 'mod_spproperty_emi_calculator'),
            array('type' => 'module', 'name' => 'mod_spproperty_agents'),
            array('type' => 'module', 'name' => 'mod_spproperty_categories'),
            array('type' => 'plugin', 'name' => 'sppropertyupdater', 'group' => 'system'),
            array('type' => 'plugin', 'name' => 'sppropertyprofile', 'group' => 'user'),
            array('type' => 'plugin', 'name' => 'spproperty', 'group' => 'finder')
            );

        foreach ($extensions as $key => $extension) {
            $ext = $parent->getParent()->getPath('source') . '/' . $extension['type'] . 's/' . $extension['name'];
            $installer = new Installer();
            $installer->setDatabase($db);
            $installer->install($ext);

            if ($extension['type'] == 'plugin') {
                
                $query = $db->getQuery(true);
                $fields = array($db->quoteName('enabled') . ' = 1');
                $conditions = array(
                    $db->quoteName('type') . ' = ' . $db->quote($extension['type']),
                    $db->quoteName('element') . ' = ' . $db->quote($extension['name']),
                    $db->quoteName('folder') . ' = ' . $db->quote($extension['group'])
                    );

                $query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
                $db->setQuery($query);
                $db->execute();
            }
        }
    }
}
