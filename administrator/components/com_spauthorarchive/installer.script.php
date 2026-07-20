<?php
/**
 * @package com_spauthorarchive
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2021 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;

class com_spauthorarchiveInstallerScript {

    public function uninstall($parent) {

        $extensions = array(
            array('type'=>'plugin', 'name'=>'profilespaarchive')
        );

        foreach ($extensions as $key => $extension) {

            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select($db->quoteName(array('extension_id')));
            $query->from($db->quoteName('#__extensions'));
            $query->where($db->quoteName('type') . ' = '. $db->quote($extension['type']));
            $query->where($db->quoteName('element') . ' = '. $db->quote($extension['name']));
            $db->setQuery($query);
            $id = $db->loadResult();

            if(isset($id) && $id) {
                $installer = new Installer;
                $result = $installer->uninstall($extension['type'], $id);
            }
        }
    }

    function postflight($type, $parent)
    {
        $extensions = array(
            array('type'=>'plugin', 'name'=>'profilespaarchive', 'group'=>'user')
        );

        foreach ($extensions as $key => $extension) {
            $ext = $parent->getParent()->getPath('source') . '/' . $extension['type'] . 's/user/' . $extension['name'];

            $installer = new Installer;
            $installer->install($ext);

            if($extension['type'] == 'plugin') {
                $db = Factory::getDbo();
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
