<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\CMSVersion;

defined('_AKEEBA') or die();

/**
 * An interface to a utility object which detects the CMS version, if applicable.
 *
 * @since  10.0
 */
interface DetectionInterface
{
	/**
	 * Detect the CMS version, and store it into the session.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function detectVersion(): void;
}