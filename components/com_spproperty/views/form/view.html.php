<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * HTML Article View class for the Content component
 *
 * @since  1.5
 */
class SppropertyViewForm extends HtmlView
{
    protected $form;
    protected $item;
    protected $return_page;
    protected $state;

    /**
     * Should we show a captcha form for the submission of the article?
     *
     * @var   bool
     * @since 3.7.0
     */
    protected $captchaEnabled = false;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        $user   = Factory::getUser();
        $app    = Factory::getApplication();
        $model  = $this->getModel();
        $input  = Factory::getApplication()->input;

        // Get model data.
        $this->state       = $this->get('State');
        $this->item        = $this->get('Item');
        $this->form        = $this->get('Form');
        $this->return_page = $this->get('ReturnPage');

        $params = &$this->state->params;
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors), 500);
        }

        // Load Properties
        BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_spproperty/models');
        $properties_model = BaseDatabaseModel::getInstance('properties', 'SppropertyModel');

        $agentid    = $input->get('id', 0, 'INT');
        $user_info  = $model->agentInfo($user->id);
        if ($agentid) {
            $agent_info = $properties_model->getAgntInfo($agentid);
        }


        if ($user->guest) {
            $uri            = Uri::getInstance();
            $current_url    = $uri->toString();
            $login_url = 'index.php?option=com_users&view=login';
            $login_url .= '&return=' . base64_encode($current_url);

            $html = '<div class="alert alert-danger">';
                $html .= '<h4 class="alert-heading">' . Text::_('ERROR') . '</h4>';
                $html .= '<div>';
                    $html .= '<p>' . Text::_('JERROR_ALERTNOAUTHOR') . ' ' . Text::sprintf('COM_SPPROPERTY_LOGIN_FOR_AGENT_APPLY', '<a href="' . Route::_($login_url) . '">' . Text::_('JLOGIN') . '</a>') . '</p>';
                $html .= '</div>';
            $html .= '</div>';
            echo $html;
            $app->setHeader('status', 403, true);
            return false;
        } elseif (is_countable($user_info) && count($user_info)  && $agentid == '') {
            $redirect_url =  Route::_('index.php?option=com_spproperty&view=form&layout=edit&id=' . $user_info->id . SppropertyHelper::getItemid('forms'));
            // echo $redirect_url;
            // die();
            $app->redirect($redirect_url, $html);

            // if not redirect
            $html = '<div class="alert alert-danger">';
                $html .= '<h4 class="alert-heading">' . Text::_('ERROR') . '</h4>';
                $html .= '<div>';
                    $html .= '<p>' . Text::_('JERROR_ALERTNOAUTHOR') . ' ' . Text::_('COM_SPPROPERTY_ALREADY_AGENT') . '</p>';
                $html .= '</div>';
            $html .= '</div>';
            echo $html;
            $app->setHeader('status', 403, true);
            return false;
        } elseif (isset($agent_info) && ($agent_info->created_by != $user->id) || ($agentid && !count($agent_info))) {
            $html = '<div class="alert alert-danger">';
                $html .= '<h4 class="alert-heading">' . Text::_('ERROR') . '</h4>';
                $html .= '<div>';
                    $html .= '<p>' . Text::_('JERROR_ALERTNOAUTHOR') . '</p>';
                $html .= '</div>';
            $html .= '</div>';
            echo $html;
            $app->setHeader('status', 403, true);
            return false;
        }

        if (empty($this->item->email)) {
            $this->form->setValue('email', null, $user->get('email', ''));
        }

        if (empty($this->item->title)) {
            $this->form->setValue('title', null, $user->get('name', ''));
        }

        // Create a shortcut to the parameters.
        $params = &$this->state->params;
        parent::display($tpl);
    }

    /**
     * Prepares the document
     *
     * @return  void
     */
    protected function _prepareDocument()
    {
        $app   = Factory::getApplication();
        $menus = $app->getMenu();
        $title = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('COM_CONTENT_FORM_EDIT_ARTICLE'));
        }

        $title = $this->params->def('page_title', Text::_('COM_CONTENT_FORM_EDIT_ARTICLE'));

        if ($app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        $pathway = $app->getPathWay();
        $pathway->addItem($title, '');

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }
}
