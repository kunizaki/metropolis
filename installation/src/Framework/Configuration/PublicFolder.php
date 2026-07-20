<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Configuration;

defined('_AKEEBA') or die();

/**
 * Handles the configuration of the Joomla! custom public folder.
 *
 * This is necessary because Joomla 5.0 and later might set up a custom public folder inside the web root, with Joomla
 * itself being installed very likely completely outside the web root.
 *
 * @property-read  bool        $custom     Do we have a custom public folder?.
 * @property-read  string|null $oldPublic  The old custom public folder (JPATH_PUBLIC).
 * @property-read  string|null $oldRoot    The old site's root folder (JPATH_ROOT).
 * @property-read  string|null $source     Absolute path to the extracted folder with public files.
 *
 * @since  10.0
 */
class PublicFolder extends AbstractConfiguration
{
	/**
	 * Do we have a custom public folder?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	protected $custom = false;

	/**
	 * The absolute filesystem path of the site at backup time.
	 *
	 * @var   null
	 * @since 10.0
	 */
	protected $oldPublic = null;

	/**
	 * The absolute filesystem path of the site at backup time.
	 *
	 * @var   null
	 * @since 10.0
	 */
	protected $oldRoot = null;

	/**
	 * Absolute path to the extracted folder which contains the public directory's files.
	 *
	 * @var   string|null
	 * @since 10.0
	 */
	protected $source;

	public static function loadFromExtrainfo(array $extraInfo, string $siteRoot): array
	{
		$customPublic = $extraInfo['custom_public'] ?? false;
		$JPATH_PUBLIC = $extraInfo['JPATH_PUBLIC'] ?? null;
		$JPATH_ROOT   = $extraInfo['JPATH_ROOT'] ?? null;

		$hasCustomPublic = $customPublic && !empty($JPATH_PUBLIC);
		$hasOldRoot      = !empty($JPATH_ROOT);
		$publicFolder    = $hasCustomPublic ? $JPATH_PUBLIC : $siteRoot;
		$oldRoot         = $hasOldRoot ? $JPATH_ROOT : $extraInfo['root'] ?? $siteRoot;

		return [
			'custom'    => $hasCustomPublic,
			'oldPublic' => $hasCustomPublic ? $publicFolder : $siteRoot,
			'oldRoot'   => $hasCustomPublic ? $oldRoot : null,
		];
	}

	public function setCustom(bool $custom): void
	{
		$this->custom = $custom;
	}

	public function setOldPublic(?string $oldPublic): void
	{
		$this->oldPublic = $oldPublic;
	}

	public function setOldRoot(?string $oldPublic): void
	{
		$this->oldRoot = $oldPublic;
	}

	public function setSource(?string $source): void
	{
		$this->source = $source;
	}
}