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
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Component\Router\RouterViewConfiguration;

class SppropertyRouter extends RouterView
{
    protected $noIDs = false;

    /**
     * The DB Object
     *
     * @var     DatabaseDriver
     * @sine    3.2.0
     */
    private $db = null;

    /**
     * The query string generator.
     *
     * @var     object
     * @since   3.2.0
     */
    private $queryBuilder = null;


    /**
     * SP Property Component router constructor
     *
     * @param   JApplicationCms  $app   The application object
     * @param   JMenu            $menu  The menu object to work with
     */
    public function __construct($app = null, $menu = null)
    {
        $params = ComponentHelper::getParams('com_spproperty', true);

        $this->noIDs = (bool) $params->get('sef_ids');

        $this->db = Factory::getDbo();
        $this->queryBuilder = $this->db->getQuery(true);

        //property
        $properties = new RouterViewConfiguration("properties");
        $properties->setKey('category_id');
        $this->registerView($properties);
        $property   = new RouterViewConfiguration("property");
        $property->setKey('id')->setParent($properties);
        $this->registerView($property);

        //myproperty
        $myproperties = new RouterViewConfiguration("myproperties");
        $this->registerView($myproperties);

        //form
        $form = new RouterViewConfiguration("form");
        $this->registerView($form);

        //form
        $categories = new RouterViewConfiguration("categories");
        $this->registerView($categories);


        //agent
        $agents     = new RouterViewConfiguration("agents");
        $this->registerView($agents);
        $agent      = new RouterViewConfiguration("agent");
        $agent->setKey('id')->setParent($agents);
        $this->registerView($agent);

        //galleries
        $galleries  = new RouterViewConfiguration("galleries");
        $this->registerView($galleries);
        $gallery    = new RouterViewConfiguration("gallery");
        $gallery->setKey('id')->setParent($galleries);
        $this->registerView($gallery);

        $favorites  = new RouterViewConfiguration("favorites");
        $this->registerView($favorites);

        //maps
        $maps = new RouterViewConfiguration("maps");
        $this->registerView($maps);

        parent::__construct($app, $menu);

        $this->attachRule(new MenuRules($this));

        $this->attachRule(new NomenuRules($this));
        $this->attachRule(new StandardRules($this));
    }


    /**
     * Get missing alias from the provided ID.
     *
     * @param   string      $id     The ID with or without the alias.
     * @param   string      $table  The table name.
     *
     * @return  string      The alias string.
     * @since   4.0.0
     */
    private function getAlias(string $id, string $table): string
    {
        try {
            $this->queryBuilder->clear();
            $this->queryBuilder->select('alias')
                ->from($this->db->quoteName($table))
                ->where($this->db->quoteName('id') . ' = ' . (int) $id);
            $this->db->setQuery($this->queryBuilder);

            return (string) $this->db->loadResult();
        } catch (Exception $e) {
            echo $e->getMessage();

            return '';
        }
    }

    /**
     * Get id from the alias.
     *
     * @param   string      $alias      The alias string.
     * @param   string      $table      The table name.
     *
     * @return  int         The id.
     * @since   4.0.0
     */
    private function getId(string $alias, string $table): int
    {
        try {
            $this->queryBuilder->clear();
            $this->queryBuilder->select('id')
                ->from($this->db->quoteName($table))
                ->where($this->db->quoteName('alias') . ' = ' . $this->db->quote($alias));
            $this->db->setQuery($this->queryBuilder);

            return (int) $this->db->loadResult();
        } catch (Exception $e) {
            echo $e->getMessage();

            return 0;
        }
    }

    /**
     * Get the view segment for the common views.
     *
     * @param   string  $id     The ID with or without alias.
     * @param   string  $table  The table name.
     *
     * @return  array   The segment array.
     * @since   4.0.0
     */
    private function getViewSegment(string $id, string $table): array
    {
        if (strpos($id, ':') === false) {
            $id .= ':' . $this->getAlias($id, $table);
        }

        if ($this->noIDs) {
            list ($key, $alias) = explode(':', $id, 2);

            return [$key => $alias];
        }

        return [(int) $id => $id];
    }

    /**
     * get the view ID for the common pattern view.
     *
     * @param   string  $segment    The segment string.
     * @param   string  $table      The table name.
     *
     * @return  int     The id.
     * @since   4.0.0
     */
    private function getViewId(string $segment, string $table): int
    {
        return $this->noIDs
            ? $this->getId($segment, $table)
            : (int) $segment;
    }


    public function getMyPropertiesSegment($id, $query)
    {
        $category = Categories::getInstance($this->getName())->get($id);

        if ($category) {
            $path = array_reverse($category->getPath(), true);
            $path[0] = '1:root';

            if ($this->noIDs) {
                foreach ($path as &$segment) {
                    list($id, $segment) = explode(':', $segment, 2);
                }
            }

            return $path;
        }

        return array();
    }

    public function getMyPropertiesId($segment, $query)
    {
        if (isset($query['id'])) {
            $category = Categories::getInstance($this->getName())->get($query['id']);

            foreach ($category->getChildren() as $child) {
                if ($this->noIDs) {
                    if ($child->alias == $segment) {
                        return $child->id;
                    }
                } else {
                    if ($child->id == (int) $segment) {
                        return $child->id;
                    }
                }
            }
        }

        return false;
    }

    public function getMyPropertySegment($id, $query)
    {

        return $this->getViewSegment($id, '#__spproperty_properties');
    }

    public function getMyPropertyId($segment, $query)
    {
        return $this->getViewId($segment, '#__spproperty_properties');
    }

    public function getPropertiesSegment($id, $query)
    {

        $category = ($id) ? Categories::getInstance($this->getName())->get($id) : Categories::getInstance($this->getName());

        if ($category) {
            $path = array_reverse($category->getPath(), true);
            $path[0] = '1:root';

            if ($this->noIDs) {
                foreach ($path as &$segment) {
                    list($id, $segment) = explode(':', $segment, 2);
                }
            }

            return $path;
        }

        return array();
    }

    public function getPropertiesId($segment, $query)
    {
        if (isset($query['id'])) {
            $category = Categories::getInstance($this->getName())->get($query['id']);
            foreach ($category->getChildren() as $child) {
                if ($this->noIDs) {
                    if ($child->alias == $segment) {
                        return $child->id;
                    }
                } else {
                    if ($child->id == (int) $segment) {
                        return $child->id;
                    }
                }
            }
        }

        return false;
    }

    public function getPropertySegment($id, $query)
    {

        return $this->getViewSegment($id, '#__spproperty_properties');
    }
    public function getPropertyId($segment, $query)
    {
        return $this->getViewId($segment, '#__spproperty_properties');
    }
    public function getAgentsSegment($id, $query)
    {
        $category = Categories::getInstance($this->getName())->get($id);

        if ($category) {
            $path = array_reverse($category->getPath(), true);
            $path[0] = '1:root';

            if ($this->noIDs) {
                foreach ($path as &$segment) {
                    list($id, $segment) = explode(':', $segment, 2);
                }
            }

            return $path;
        }

        return array();
    }

    public function getAgentsId($segment, $query)
    {
        if (isset($query['id'])) {
            $category = Categories::getInstance($this->getName())->get($query['id']);

            foreach ($category->getChildren() as $child) {
                if ($this->noIDs) {
                    if ($child->alias == $segment) {
                        return $child->id;
                    }
                } else {
                    if ($child->id == (int) $segment) {
                        return $child->id;
                    }
                }
            }
        }

        return false;
    }

    public function getAgentSegment($id, $query)
    {
        return $this->getViewSegment($id, '#__spproperty_agents');
    }
    public function getAgentId($segment, $query)
    {
        return $this->getViewId($segment, '#__spproperty_agents');
    }

    public function getGalleriesSegment($id, $query)
    {
        $category = Categories::getInstance($this->getName())->get($id);

        if ($category) {
            $path = array_reverse($category->getPath(), true);
            $path[0] = '1:root';

            if ($this->noIDs) {
                foreach ($path as &$segment) {
                    list($id, $segment) = explode(':', $segment, 2);
                }
            }

            return $path;
        }

        return array();
    }

    public function getGalleriesId($segment, $query)
    {
        if (isset($query['id'])) {
            $category = Categories::getInstance($this->getName())->get($query['id']);

            foreach ($category->getChildren() as $child) {
                if ($this->noIDs) {
                    if ($child->alias == $segment) {
                        return $child->id;
                    }
                } else {
                    if ($child->id == (int) $segment) {
                        return $child->id;
                    }
                }
            }
        }

        return false;
    }

    public function getGallerySegment($id, $query)
    {
        return $this->getViewSegment($id, '#__spproperty_properties');
    }
    public function getGalleryId($segment, $query)
    {
        return $this->getViewId($segment, '#__spproperty_properties');
    }
}

function sppropertyBuildRoute(&$query)
{
    $app = Factory::getApplication();
    $router = new SppropertyRouter($app, $app->getMenu());

    return $router->build($query);
}

function sppropertyParseRoute($segments)
{
    $app = Factory::getApplication();
    $router = new SppropertyRouter($app, $app->getMenu());

    return $router->parse($segments);
}
