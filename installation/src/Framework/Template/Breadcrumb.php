<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Template;

defined('_AKEEBA') or die();

/**
 * A single breadcrumb of the application
 *
 * @property-read  string $view          View name.
 * @property-read  string $name          Translation key for the breadcrumb label.
 * @property-read  bool   $active        True when it is the active view.
 * @property-read  int    $substeps      Number of substeps. 0 if no substeps.
 * @property-read  int    $substepIndex  Current substep index. 0 if n/a, or not active view.
 *
 * @since  10.0
 */
final class Breadcrumb
{
	/**
	 * View name.
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $view;

	/**
	 * Is this the active view?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	private $active;

	/**
	 * Number of substeps. 0 if no substeps.
	 *
	 * @var   int
	 * @since 10.0
	 */
	private $substeps = 0;

	/**
	 * Currently active substep. 0 if n/a, or not active view.
	 *
	 * @var   int
	 * @since 10.0
	 */
	private $substepIndex = 0;

	/**
	 * Constructor method for initializing the class with provided parameters.
	 *
	 * @param   string  $view          The view identifier for the object.
	 * @param   bool    $active        Indicates whether the view is active or not. Defaults to false.
	 * @param   int     $substeps      The number of substeps associated with the view. 0 for no substeps.
	 * @param   int     $substepIndex  The index of the current substep. 0 when n/a or not active.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function __construct(string $view, bool $active = false, int $substeps = 0, int $substepIndex = 0)
	{
		$this->view         = $view;
		$this->active       = $active;
		$this->substeps     = $substeps;
		$this->substepIndex = $substepIndex;
	}

	/**
	 * Magic getter method to access class properties dynamically.
	 *
	 * @param   string  $name  The name of the property being accessed.
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	public function __get($name)
	{
		if ($name === 'name')
		{
			return $this->view . '_LBL_TITLE';
		}

		if (isset($this->{$name}))
		{
			return $this->{$name};
		}

		return null;
	}
}