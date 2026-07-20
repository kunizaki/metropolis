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
 * Navigation button definition.
 *
 * @property-read  string $buttonClass  The CSS class for the button.
 * @property-read  string $id           The ID attribute of the button.
 * @property-read  string $icon         The CSS icon class for the button.
 * @property-read  string $title        The language string for the button's text.
 *
 * @since  10.0
 */
final class Button
{
	/**
	 * The CSS class for the button.
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $buttonClass;

	/**
	 * The ID attribute of the button.
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $id;

	/**
	 * The CSS icon class for the button.
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $icon;

	/**
	 * The language string for the button's text.
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $title;

	/**
	 * Constructor
	 *
	 * @param   string  $buttonClass  The CSS class for the button.
	 * @param   string  $id           The ID attribute of the button.
	 * @param   string  $icon         The CSS icon class for the button.
	 * @param   string  $title        The language string for the button's text.
	 *
	 * @since   10.0
	 */
	public function __construct(
		string $buttonClass = 'btn-primary', string $id = '', string $icon = 'fa-info-circle', string $title = ''
	)
	{
		$this->buttonClass = $buttonClass;
		$this->id          = $id;
		$this->icon        = $icon;
		$this->title       = $title;
	}

	/**
	 * Magic method for retrieving the value of an inaccessible property.
	 *
	 * @param   string  $name  The name of the property to retrieve.
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	public function __get($name)
	{
		if (isset($this->{$name}))
		{
			return $this->{$name};
		}

		return null;
	}
}