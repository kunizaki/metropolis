<?php
/**
* @package com_spauthorarchive
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2021 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Uri\Uri;

class SpauthorarchiveControllerBookmarks extends BaseController
{

	public function getModel($name = 'bookmarks', $prefix = 'SpauthorarchiveModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config); 
		return $model; 
	}

    /**
     * @param bool $cachable
     * @param bool $urlparams
     * @param null $tpl
     * @return JControllerLegacy|void
     */
    public function display($cachable = false, $urlparams = false, $tpl = null)
	{
		$cachable = true;

		if (!is_array($urlparams))
		{
			$urlparams = [];
		}
		
		$additionalParams = array(
			'catid' => 'INT',
			'id' => 'INT',
			'cid' => 'ARRAY',
			'year' => 'INT',
			'month' => 'INT',
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


    /**
     * @throws Exception
     */
    public function addBookmark() {

		$model 	= $this->getModel();

		$user = JFactory::getUser();
		$input 	= JFactory::getApplication()->input;
		$response = array();
		$response['status'] = false;
		$response['loggedin'] = false;
		$response['action_type'] = '';

		$item_id    = $input->post->get('cid', 0, 'INT');

		if (!$item_id)
		{
			$response['action_type'] 	= 'blank';
			$response['message'] 		= Text::_('COM_SPAUTHORARCHIVE_BOOKMARK_BLANK');
			echo json_encode($response);
		    die();
		}

		// if user isn't logged
		 if(!$user->id) {
		     $curl       = $input->post->get('curl', 0, 'RAW');
		     $response['loginurl'] = Route::_ ( Uri::root() . 'index.php?option=com_users&view=login&return=' .  urlencode(base64_encode( $curl )) );
             echo json_encode($response);
             die();
		 }

        $response['loggedin'] = true;
		$inserted_bookmark =  $model->bookmarksCrud($item_id, $user->id);

//		echo json_encode($inserted_bookmark);
//		die();

		if($inserted_bookmark['status']) {
		    $response['status'] = true;
		    $response['action_type'] = $inserted_bookmark['action_type'];
		    switch ($inserted_bookmark['action_type']) {
                case 'add':
                case 'update':
                    $response['message'] = Text::_('COM_SPAUTHORARCHIVE_BOOKMARK_SUCCESSFULLY_ADDED');
                break;

                case 'remove':
                    $response['message'] = Text::_('COM_SPAUTHORARCHIVE_BOOKMARK_SUCCESSFULLY_REMOVED');
                break;

                default:
                    $response['message'] = Text::_('COM_SPAUTHORARCHIVE_BOOKMARK_SUCCESSFULLY_ACTION');
                break;
            }
        } else {
            $response['message'] = Text::_('COM_SPAUTHORARCHIVE_BOOKMARK_SOMETHING_WENT_WRONG');
        }

		
		echo json_encode($response);
		die();
	}
	
}
