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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\FormController;

class SppropertyControllerAgents extends FormController
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function getModel($name = 'Agents', $prefix = 'SppropertyModel', $config = array())
    {
        return parent::getModel($name = 'Agents', $prefix = 'SppropertyModel', $config = array());
    }

    public function contact()
    {
        $model      = $this->getModel();
        $user       = Factory::getUser();
        $user_id    = $user->id;

        $input      = Factory::getApplication()->input;
        $mail       = Factory::getMailer();

        $name       = $input->post->get('name', null, 'STRING');
        $email      = $input->post->get('email', null, 'STRING');
        $phone      = $input->post->get('phone', null, 'STRING');
        $showcapt   = $input->post->get('showcaptcha', false, 'INT');
        $subject    = $input->post->get('subject', null, 'STRING');
        $subject    = $subject . ' | Phone Number: ' . $phone;
        $message    = nl2br($input->post->get('message', null, 'STRING'));
        $recipient  = base64_decode($input->post->get('agnt_email', null, 'STRING'));

        $output['status'] = false;
        $output = array();

        if ($showcapt) {
            $captcha_plg = PluginHelper::importPlugin('captcha');

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

        //message body
        $visitorip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

        $msg  = '';
        $msg .= '<p><span>Name : ' . $name . '</span></p> <br />';
        $msg .= '<p><span>Phone : ' . $phone . '</span></p> <br />';
        if ($user_name = $user->name) {
            $msg .= '<p><span>User name : ' . $user_name . '</span></p> <br />';
        }
        $msg .= '<p><span>Sender IP : ' . $visitorip . '</span></p> <br />';
        $msg .= '<p><span>Email : ' . $email . '</span></p> <br />';
        $msg .= '<p><span>message : ' . nl2br($message) . '</span></p> <br />';

        // Sent email
        $sender = array($email, $name);
        $mail->setSender($sender);
        $mail->addRecipient($recipient);
        $mail->setSubject($subject);
        $mail->isHTML(true);
        $mail->Encoding = 'base64';
        $mail->setBody($msg);

        if ($mail->Send()) {
            $output['status'] = true;
            $output['content'] = Text::_('COM_SPPROPERTY_CONTACT_SUCCESS');
        } else {
            $output['content'] = Text::_('COM_SPPROPERTY_CONTACT_ERROR');
        }

        echo json_encode($output);
        die();
    }
}
