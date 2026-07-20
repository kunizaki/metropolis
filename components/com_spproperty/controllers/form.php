<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\FormController;

class SppropertyControllerForm extends FormController
{
    protected $view_item = 'agent';

    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    protected function allowAdd($data = array())
    {
        return parent::allowAdd($data);
    }

    protected function allowEdit($data = array(), $key = 'id')
    {

        $recordId = (int) isset($data[$key]) ? $data[$key] : 0;
        $user = Factory::getUser();

        if (!$recordId) {
            return parent::allowEdit($data, $key);
        }

        if ($user->authorise('core.edit', 'com_spproperty.agent.' . $recordId)) {
            return true;
        }

        if ($user->authorise('core.edit.own', 'com_spproperty.agent.' . $recordId)) {
            $record = $this->getModel()->getItem($recordId);

            if (empty($record)) {
                return false;
            }

            return $user->get('id') == $record->created_by;
        }

        return false;
    }


    public function add()
    {
        if (!parent::add()) {
            $this->setRedirect($this->getReturnPage());
            return;
        }
        $this->setRedirect(
            Route::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=0'
                . $this->getRedirectToItemAppend(),
                false
            )
        );

        return true;
    }

    public function cancel($key = 'id')
    {

        parent::cancel($key);
        $user = Factory::getUser();
        $app = Factory::getApplication();

        // Load Properties
        BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_spproperty/models');
        $properties_model = BaseDatabaseModel::getInstance('properties', 'SppropertyModel');

        $input = Factory::getApplication()->input;
        $agentid = $input->get('id', 0, 'INT');

        if ($agentid != 0) {
            $agent_info = $properties_model->getAgntInfo($agentid);
            $return =  Route::_('index.php?option=com_spproperty&view=agent&id=' . $agentid . ':' . $agent_info->alias . SppropertyHelper::getItemid(
                'agents'
            ));
        } else {
            $return =  Route::_('index.php?option=com_spproperty&view=agents') . SppropertyHelper::getItemid(
                'agents'
            );
        }

        // echo '<pre>';
        // print_r($agent_info);
        // //print_r($agentid);
        // echo '</pre>';
        // die();


        // $app->enqueueMessage('Redirect to another page was successful', 'success');
        // $app->redirect($return);

        // echo $return;
        // die();
        $this->setRedirect($return);
        $this->redirect();
    }

    public function edit($key = null, $urlVar = 'id')
    {
        $result = parent::edit($key, $urlVar);

        if (!$result) {
            $this->setRedirect(Route::_($this->getReturnPage()));
        }

        return $result;
    }

    public function getModel($name = 'Form', $prefix = 'SppropertyModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function save($key = null, $urlVar = 'id')
    {

        $user = Factory::getUser();
        $result = parent::save($key, $urlVar);
        $app = Factory::getApplication();
        $params     = ComponentHelper::getParams('com_spproperty');
        $itemId = '&Itemid=' . (int) $params->get('itemid', 0, 'INT');
        $model = $this->getModel('Agent');
        $config = Factory::getConfig();
        $admin_email = $config->get('mailfrom');
        //get sender UP
        $senderip       = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        // Subject Structure
        $site_name      = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
        $mail_subject   = 'A user has applied to be an agent on ' . $site_name;

        $message = 'A user is interested in being an agent on your site "' . $site_name . '". The user details are given bellow.
					<br/>Username: ' . $user->username . '<br/>User ID: ' . $user->id . '<br/>User Email: ' . $user->email;

        $mail = Factory::getMailer();
        $sender = array($user->email, $user->username);
        $mail->setSender($sender);
        $mail->addRecipient($admin_email);
        $mail->setSubject($mail_subject);
        $mail->isHTML(true);
        $mail->Encoding = 'base64';
        $mail->setBody($message);
        $mail->Send();

        if ($result) {
            $return = 'index.php?option=com_spproperty&view=agents&layout=thankyou' . SppropertyHelper::getItemid('agents');
            if (isset($return)) {
                $this->setRedirect(Route::_($return));
            }
        } else {
            $return = 'index.php?option=com_spproperty&view=agents' . SppropertyHelper::getItemid('agents');
            if (isset($return)) {
                $this->setRedirect(Route::_($return));
            }
        }

        return $result;
    }

    // public function postSaveHook(BaseDatabaseModel $model, $validData = array()) {
    //  $productId = (int) $model->getState('product.id');
    //  $userId = (int) Factory::getUser()->id;
    //  $tmp_path = JPATH_ROOT . '/tmp/com_spproperty/' . $userId;

    //  if($productId) {

    //      $updated = false;

    //      // Update database
    //      $profileModel = $this->getModel('Agent');
    //      //$product = $profileModel->getProduct($productId);

    //      // Update Message
    //      if(isset($validData['message']) && $validData['message']) {
    //          $model->addMessage($productId, $validData['message']);
    //      }

    //      // Update state
    //      if(count((array)$product) && $product->published == 2) {
    //          $model->updateProductSate($productId, 3);
    //      }
    //  }
    // }
}
