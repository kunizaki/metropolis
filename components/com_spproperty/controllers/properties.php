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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\FormController;

class SppropertyControllerProperties extends FormController
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function display($cachable = false, $urlparams = false, $tpl = null)
    {
        $cachable = true;
        if (!is_array($urlparams)) {
            $urlparams = [];
        }
        $additionalParams = array(
            'catid' => 'INT',
            'id' => 'INT',
            'cid' => 'ARRAY',
            'title' => 'STRING',
            'city' => 'STRING',
            'limit' => 'UINT',
            'limitstart' => 'UINT',
            'showall' => 'INT',
            'return' => 'BASE64',
            'filter' => 'STRING',
            'filter_order' => 'CMD',
            'filter_order_Dir' => 'CMD',
            'filter-search' => 'STRING',
            'print' => 'BOOLEAN',
            'lang' => 'CMD',
            'Itemid' => 'INT');

        $urlparams = array_merge($additionalParams, $urlparams);
        parent::display($cachable, $urlparams, $tpl);
    }

    public function getModel($name = 'Properties', $prefix = 'SppropertyModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function booking()
    {

        $model      = $this->getModel();
        $user       = Factory::getUser();
        $user_id    = $user->id;

        $input      = Factory::getApplication()->input;
        $mail       = Factory::getMailer();

        $name       = $input->post->get('name', null, 'STRING');
        $phone      = $input->post->get('phone', null, 'STRING');
        $recipient  = $input->post->get('email', null, 'STRING');
        $message    = $input->post->get('message', null, 'STRING');
        $sender     = base64_decode($input->post->get('sender', null, 'STRING'));
        $pid        = $input->post->get('pid', null, 'INT');
        $pname      = $input->post->get('pname', null, 'STRING');
        $showcapt   = $input->post->get('showcaptcha', false, 'INT');
        $visitor_ip = $input->post->get('visitor_ip', null, 'STRING');
        $request_type = $input->post->get('request_type', null, 'STRING');


        $subject    = Text::_('COM_SPPROPERTY_EMAIL_SUBJECT_TEXT') . $pname;

        $output = array();
        $output['status'] = false;

        if ($showcapt) {
            $captcha_plg  = PluginHelper::importPlugin('captcha');
            $res = Factory::getApplication()->triggerEvent('onCheckAnswer');

            if (!$captcha_plg) {
                $output['content'] = Text::_('COM_SPMEDICAL_CAPTCHA_NOT_INSTALLED');
                echo json_encode($output);
                die();
            }

            if (!$res[0]) {
                $output['content'] = Text::_('COM_SPPROPERTY_RECAPTCHA_INVALID_CAPTCHA');
                echo json_encode($output);
                die();
            }
        }

        if ($model->insertBooking($pid, $name, $phone, $recipient, $message, $user_id, $visitor_ip, $request_type)) {
            $output['status'] = true;

            if ($request_type == 'price') {
                $agent_email    = base64_decode($input->post->get('agent_email'));
                $subject        = Text::sprintf('COM_SPPROPERTY_EMAIL_SUBJECT_TEXT_FOR_PRICE_REQ', $pname);
                $property_id    = $input->post->get('property_id', null, 'STRING');
                $property_id    = !is_null($property_id) ? '(' . $property_id . ')' : '';

                $msg  = '';
                $msg .= '<p><strong>Request for: </strong>' . $pname . $property_id . '</p>';
                $msg .= '<p><strong>Customer Name: </strong>' . $name .  '</p>';
                $msg .= '<p><strong>Phone: </strong>' . $phone . '</p>';
                $msg .= '<p><strong>Email: </strong>' . $recipient . '</p>';
                $msg .= '<p><strong>Message:<br /> </strong>' . nl2br($message) . '</p>';

                $senderinfo = array($sender, $name);
                $mail->setSender($senderinfo);
                $mail->addRecipient(trim($agent_email));
                $mail->setSubject($subject);
                $mail->isHTML(true);
                $mail->Encoding = 'base64';
                $mail->setBody($msg);

                if ($mail->Send()) {
                    $output['content'] = Text::_('COM_SPPROPERTY_PREQUEST_SUCCESS');
                } else {
                    $output['content'] = Text::_('COM_SPPROPERTY_PREQUEST_SENT_ERORR');
                }
            }

            $msg  = '';
            $msg .= '<p><strong>Request for: </strong>' . $pname . '</p>';
            $msg .= '<p><strong>Name : </strong>' . $name . '</p>';
            $msg .= '<p><strong>Phone : </strong>' . $phone . '</p>';
            $msg .= '<p><strong>Email : </strong>' . $recipient . '</p>';
            $msg .= '<p><strong>message :</strong><br /> ' . nl2br($message) . '</p>';

            // Sent email
            $senderinfo = array($sender, $name);
            $mail->setSender($senderinfo);
            $mail->addRecipient($recipient);
            $mail->addCC($sender);
            $mail->setSubject($subject);
            $mail->isHTML(true);
            $mail->Encoding = 'base64';
            $mail->setBody($msg);

            if ($mail->Send()) {
                $output['content'] = Text::_('COM_SPPROPERTY_PREQUEST_SUCCESS');
            } else {
                $output['content'] = Text::_('COM_SPPROPERTY_PREQUEST_SENT_ERORR');
            }
        } else {
            $output['status']  = false;
            $output['content'] = Text::_('COM_SPPROPERTY_PREQUEST_ERROR');
        }

        echo json_encode($output);
        die();
    }

    public function getSelectedProperties()
    {

        $model      = $this->getModel();
        $input      = Factory::getApplication()->input;
        $properties = $input->post->get('properties', []);
        $gridView   = $input->post->get('gridView', 'true');
        $columns    = $input->post->get('columns', 2);
        $data       = $model->getSelectedProperties($properties);
        $response   = array();
        $params     = ComponentHelper::getParams('com_spproperty');


        if (!empty($data)) {
            $html = [];
            if ($gridView == "true") {
                $html[] = "<div id='sp-property-properties' class='spproperty-view-properties spproperty' data-simplebar>";
                $html[] = LayoutHelper::render('properties.properties', array('properties' => $data, 'columns' => $columns));
                $html[] = "</div>";
            } elseif ($gridView == "false") {
                $html[] = "<div id='sp-property-properties' class='spproperty-view-properties spproperty' data-simplebar>";
                $html[] = LayoutHelper::render('properties.properties_list', array('properties' => $data, 'columns' => 1));
                $html[] = "</div>";
            }
            $response = implode("\n", $html);
        } else {
            $response = "<div class='has-error alert sp-no-item-found'><p>" . Text::_('COM_SPPROPERTY_NO_DATA_FOUND') . "</p></div>";
        }
        echo json_encode($response);
        die();
    }

    public function addToFavourite()
    {
        $app    = Factory::getApplication();
        $input  = $app->input;
        $user   = Factory::getUser();
        $model  = $this->getModel();
        $data   = new \stdClass();
        $data->user_id      = (int)$user->get('id');
        $data->property_id  = $input->getInt('property_id');
        $flag               = $input->getInt('property_fav_flag', 1);

        $token = Session::checkToken();
        if ($token) {
            //2nd parameter flag = 1 means add to favourite
            $model->handleFavourite($data, $flag);
        } else {
            throw new Exception('CSRF Token missmatch occured! Login and try again.', 403);
            die();
        }
        echo json_encode($data);
        die();
    }
}
