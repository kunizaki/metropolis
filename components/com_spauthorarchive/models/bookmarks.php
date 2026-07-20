<?php
/**
* @package com_spauthorarchive
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2019 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\CMS\MVC\Model\ListModel;

class SpauthorarchiveModelBookmarks extends ListModel
{

    /**
     * @return JDatabaseQuery
     * @throws Exception
     */
    protected function getListQuery() {
		// Initialize variables.
		$app = Factory::getApplication();
		$user = Factory::getUser();
		$bookmark_ids = array();
		// Get Params
		$params 	= $app->getParams();
		$menu 		= Factory::getApplication()->getMenu()->getActive();
		if($menu) {
			$params->merge($menu->getParams());
		}

		$existing_bookmarks = $this->getUserExistingBookmark($user->id);
		if(isset($existing_bookmarks->item_ids) && !empty($existing_bookmarks->item_ids)) {
            $bookmark_ids = implode(',', json_decode($existing_bookmarks->item_ids) );
        }

        // start
		$authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));
		$app = Factory::getApplication();
		$db = Factory::getDbo();
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(Factory::getDate()->toSql());

		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from($db->quoteName('#__content', 'a'));
		$query->select($db->quoteName('b.alias', 'category_alias'));
		$query->select($db->quoteName('b.title', 'category'));
		$query->join('LEFT', $db->quoteName('#__categories', 'b') . ' ON (' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('b.id') . ')');
		$query->where($db->quoteName('b.extension') . ' = ' . $db->quote('com_content'));

		$query->where($db->quoteName('a.state') . ' = ' . $db->quote(1));
		$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up IS NULL OR a.publish_up <= ' . $nowDate . ')');
		$query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down IS NULL OR a.publish_down >= ' . $nowDate . ')');

		// query by ID
        $query->where($db->quoteName('a.id')." IN (".$bookmark_ids . ")");

		// Language filter
		if ($app->getLanguageFilter())
		{
			$query->where('a.language IN (' . $db->Quote(Factory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
		}

		$query->where($db->quoteName('a.access')." IN (" . implode( ',', $authorised ) . ")");

		return $query;
	}

    /**
     * @param null $ordering
     * @param null $direction
     * @throws Exception
     */
    protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication('site');
		$params = $app->getParams();
		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
		$limit = $params->get('art_limit', 6);

		$this->setState('list.limit', $limit);
	}

    /**
     * @param int $item_id
     * @param int $userid
     * @return array
     */
    // Bookmark(s) insert/update/remove
    public function bookmarksCrud($item_id = 0, $userid = 0)
	{
		$response                   = array();
		$item_ids                   = array();
		$remove_row                 = false;
		$response['status'] 		= false;
		$response['action_type'] 	= '';

		// get user's exisiting bookmarks
		$existing_bookmarks = $this->getUserExistingBookmark($userid);

		// if user has exisiting bookmarks 
		if (!empty($existing_bookmarks->item_ids) && $existing_bookmarks->item_ids)
		{
			$ebookmarks = json_decode($existing_bookmarks->item_ids);
			$ebookmarks = count($ebookmarks) > 0 ? $ebookmarks : array();
			// insert new item id if not exist
            if (!in_array($item_id, $ebookmarks))
			{
                $response['action_type'] = 'update';
                array_push($ebookmarks, $item_id);
            }
			else
			{
                if (($item_key = array_search($item_id, $ebookmarks)) !== false)
				{
                    $ebookmarks = array_values(array_diff($ebookmarks, [$item_id]));
                    $response['action_type'] = 'remove';

                    if (empty($ebookmarks) && count($ebookmarks) === 0)
					{
                        $remove_row = true;
                    }
                }
            }
            $item_ids = $ebookmarks;
		}
		else
		{
		    $response['action_type'] = 'add';
			$item_ids = array($item_id);
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		// remove row if bookmarks is empty
		if ($remove_row === true)
		{
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote($existing_bookmarks->id),
                $db->quoteName('userid') . ' = ' . $db->quote($userid)
            );
            $query->where($conditions);
            $query->delete($db->quoteName('#__authorarchive_bookmarks'));
        }
		elseif (!empty($existing_bookmarks->id) && $existing_bookmarks->id)
		{ // update bookmarks
			$fields = array( $db->quoteName('item_ids') . ' = ' . $db->quote(json_encode($item_ids)) );
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote($existing_bookmarks->id),
                $db->quoteName('userid') . ' = ' . $db->quote($userid)
            );
			$query->update($db->quoteName('#__authorarchive_bookmarks'))->set($fields)->where($conditions);	
		}
		else
		{ // if bookmarks
			$columns = array('item_ids', 'userid');
			$values = array($db->quote(json_encode($item_ids)), $db->quote($userid));
			$query->insert($db->quoteName('#__authorarchive_bookmarks'))->columns($db->quoteName($columns))->values(implode(',', $values));
		}
		
		$db->setQuery($query);

		if ($db->execute())
		{
			$response['status'] = true;
			return $response;
		}

		return $response;
	}

	// get user's existing bookmarks by user id

    /**
     * @param int $user_id
     * @return array|mixed
     */
    public function getUserExistingBookmark($user_id = 0)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'userid', 'item_ids')));
		$query->from($db->quoteName('#__authorarchive_bookmarks'));
		$query->where($db->quoteName('userid')." = ".$db->quote($user_id));
		$db->setQuery($query);
		$result = $db->loadObject();

		if(!empty($result))
		{
			return $result;
		}
		// if nothing get then return an array
		return array();
	}

}
