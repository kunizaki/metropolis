<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Document;

defined('_AKEEBA') or die();

/**
 * Trait to implement the HelpAwareInterface.
 *
 * @since  10.0
 */
trait HelpAwareTrait
{
	/**
	 * The help (documentation) URL for this document.
	 *
	 * @var   null
	 * @since 10.0
	 */
	private $helpURL = null;

	/**
	 * The video tutorial URL for this document.
	 *
	 * @var   null
	 * @since 10.0
	 */
	private $videoURL = null;

	/** @inheritdoc */
	public function setHelpURL(?string $url): void
	{
		$this->helpURL = $url;
	}

	/** @inheritdoc */
	public function getHelpURL(): ?string
	{
		return $this->helpURL;
	}

	/** @inheritdoc */
	public function setVideoURL(?string $url): void
	{
		$this->videoURL = $url;
	}

	/** @inheritdoc */
	public function getVideoURL(): ?string
	{
		return $this->videoURL;
	}
}