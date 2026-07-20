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

trait StyleAwareDocumentTrait
{
	/**
	 * Stylesheets to be loaded in the HTML output.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $styles = [];

	/**
	 * Inline style declarations.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $styleDeclarations = [];

	public function addMediaStyle(string $mediaRelative, ?string $media = null): DocumentInterface
	{
		$paths   = $this->getScriptFilePath($mediaRelative, 'css');
		$baseUri = $this->getContainer()->get('uri')->base(true);

		return $this->addStyle($baseUri . '/' . $paths['relative'], $media);
	}

	public function addStyle(string $url, ?string $media = null): DocumentInterface
	{
		$this->styles[$url] = [
			'url'   => $url,
			'media' => $media,
		];

		return $this;
	}

	public function addStyleDeclaration(string $content, ?string $media = null): DocumentInterface
	{
		$this->styleDeclarations[hash('md5', $content)] = [
			'content' => $content,
			'media'   => $media,
		];

		return $this;
	}

	public function getStyles(): array
	{
		return $this->styles;
	}

	public function getStyleDeclarations(): array
	{
		return $this->styleDeclarations;
	}
}