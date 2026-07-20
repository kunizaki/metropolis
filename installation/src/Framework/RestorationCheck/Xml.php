<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\RestorationCheck;

defined('_AKEEBA') or die();

use Psr\Container\ContainerInterface;

/**
 * Pre-restoration check: XML support
 *
 * @since      10.0
 */
class Xml extends AbstractRestorationCheck
{
	/** @inheritdoc */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container, 'MAIN_LBL_CHECK_XML_SUPPORT', true, true);
	}

	/** @inheritdoc */
	public function isApplicable(): bool
	{
		return $this->isJoomla();
	}

	/** @inheritdoc */
	protected function returnCurrentValue()
	{
		if (extension_loaded('ini_get'))
		{
			return extension_loaded('xml') && extension_loaded('simplexml');
		}

		return function_exists('xml_parse') && function_exists('simplexml_load_string');
	}

	/** @inheritdoc */
	public function getNotice(): ?string
	{
		if ($this->isValid())
		{
			return null;
		}

		return $this->getContainer()->get('language')->sprintf('MAIN_ERR_CHECK_XML_SUPPORT', PHP_VERSION);
	}
}