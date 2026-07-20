<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

class SppropertyRouterRulesLegacy implements JComponentRouterRulesInterface
{
    public function __construct($router)
    {
        $this->router = $router;
    }

    public function preprocess(&$query)
    {
    }

    private function predictAliasIdCombination($id, $tbl, $pk = 'id')
    {
        $db = JFactory::getDbo();
        $dbq = $db->getQuery(true);
        $dbq->select('*')
            ->from($db->qn($tbl))
            ->where($db->qn($pk) . ' = ' . $db->q($id));
        $db->setQuery($dbq);
        $result = $db->loadObject();
        return $result;
    }

    public function build(&$query, &$segments)
    {
        $params = JComponentHelper::getParams('com_spproperty');
        $advanced = $params->get('sef_advanced_link', 0);

        if (empty($query['Itemid'])) {
            $menuItem = $this->router->menu->getActive();
            $menuItemGiven = false;
        } else {
            $menuItem = $this->router->menu->getItem($query['Itemid']);
            $menuItemGiven = true;
        }

        // Check again
        if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_spproperty') {
            $menuItemGiven = false;
            unset($query['Itemid']);
        }



        if (isset($query['view'])) {
            $view = $query['view'];
        } else {
            // We need to have a view in the query or it is an invalid URL
            return;
        }


        if (
            $menuItem !== null
            && isset($menuItem->query['view'], $query['view'], $menuItem->query['id'], $query['id'])
            && $menuItem->query['view'] == $query['view']
            && $menuItem->query['id'] == (int) $query['id']
        ) {
            unset($query['view']);

            if (isset($query['catid'])) {
                unset($query['catid']);
            }

            if (isset($query['layout'])) {
                unset($query['layout']);
            }

            unset($query['id']);

            return;
        }
        if ($view == 'properties') {
            unset($query['view']);
            if (isset($query['catid'])) {
                if (!strpos($query['catid'], ':')) {
                    $db = JFactory::getDbo();
                    $dbq = $db->getQuery(true);
                    $dbq->select($db->qn('alias'))
                    ->from($db->qn('#__spproperty_categories'))
                    ->where($db->qn('id') . ' = ' . $db->q($query['catid']));
                    $db->setQuery($dbq);
                    $alias = $db->loadResult();
                    $query['catid'] .= ':' . $alias;
                }
            } else {
                return;
            }

            $segments[] = $query['catid'];
            unset($query['catid']);
        }

        if ($view == 'maps') {
            unset($query['view']);
        }

        if ($view == 'galleries') {
            unset($query['view']);
        }


        if ($view == 'property') {
            unset($query['view']);

            if (!$menuItemGiven) {
                $segments[] = $view;
            }
            if (isset($query['id'])) {
                if (!strpos($query['id'], ':')) {
                    $db = JFactory::getDbo();
                    $dbq = $db->getQuery(true);
                    $dbq->select($db->qn('alias'))
                    ->from($db->qn('#__spproperty_properties'))
                    ->where($db->qn('id') . ' = ' . $db->q($query['id']));
                    $db->setQuery($dbq);
                    $alias = $db->loadResult();
                    $query['id'] .= ':' . $alias;
                }
            } else {
                return;
            }

            $segments[] = $query['id'];
            unset($query['id']);
        } elseif ($view == 'agent') {
            if (!$menuItemGiven) {
                $segments[] = $view;
            }

            if (isset($query['id'])) {
                if (!strpos($query['id'], ':')) {
                    $db = JFactory::getDbo();
                    $dbq = $db->getQuery(true);
                    $dbq->select($db->qn('alias'))
                    ->from($db->qn('#__spproperty_agents'))
                    ->where($db->qn('id') . ' = ' . $db->q($query['id']));
                    $db->setQuery($dbq);
                    $alias = $db->loadResult();
                    $query['id'] .= ':' . $alias;
                } else {
                    $id = explode(':', $query['id']);
                    if (count($id) === 3) {
                        $query['id'] = $id[0] . ':' . $id[1] . ':' . $id[2];
                    }
                }
            } else {
                return;
            }

            $segments[] = $query['id'];
            unset($query['view'], $query['id']);
        } elseif ($view == 'gallery') {
            if (!$menuItemGiven) {
                $segments[] = $view;
            }

            if (isset($query['id'])) {
                if (!strpos($query['id'], ':')) {
                    $db = JFactory::getDbo();
                    $dbq = $db->getQuery(true);
                    $dbq->select($db->qn('alias'))
                    ->from($db->qn('#__spproperty_properties'))
                    ->where($db->qn('id') . ' = ' . $db->q($query['id']));
                    $db->setQuery($dbq);
                    $alias = $db->loadResult();
                    $query['id'] .= ':' . $alias;
                }
            }
            $segments[] = $view;
            $segments[] = $query['id'];
            unset($query['view'], $query['id']);
        }
        foreach ($segments as $i => &$segment) {
            $segment = str_replace(':', '-', $segment);
        }
    }


    public function parse(&$segments, &$vars)
    {
        $total = count($segments);

        for ($i = 0; $i < $total; $i++) {
            $segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
        }

        // Get the active menu item.
        $item = $this->router->menu->getActive();
        $params = JComponentHelper::getParams('com_spproperty');
        $advanced = $params->get('sef_advanced_link', 0);
        $db = JFactory::getDbo();

        // Count route segments
        $count = count($segments);

        if (!isset($item)) {
            $vars['view']   = $segments[0];
            $vars['id']     = $segments[$count - 1];
            return;
        }

        if ($count > 1) {
            $vars['view'] = $segments[0];
            $vars['id'] = (int) $segments[1];
        } else {
            if (strpos($segments[0], ':')) {
                list($id, $alias) = explode(':', $segments[0], 2);
                $category = $this->predictAliasIdCombination($id, '#__spproperty_categories', 'id');
                if (isset($category) && $category->alias == $alias) {
                    //This is a category
                    $vars['view'] = 'properties';
                    $vars['catid'] = $id;
                } else {
                    $agent = $this->predictAliasIdCombination($id, '#__spproperty_agents', 'id');
                    if (isset($agent) && $agent->alias == $alias) {
                        $vars['view'] = 'agent';
                    } else {
                        $vars['view'] = 'property';
                    }
                    $aid = explode('-', $segments[0]);
                    if ($aid[count($aid) - 1] == 'aid') {
                        $vars['view'] = 'agent';
                        $vars['id'] = '-' . $id;
                    } else {
                        $vars['id'] = $id;
                    }
                }
            }
        }
        return $vars;
    }
}
