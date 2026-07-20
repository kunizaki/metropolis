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

trait ScriptAwareDocumentTrait
{
	/**
	 * Script definitions.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $scripts = [];

	/**
	 * Inline script definitions.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $scriptDeclarations = [];

	/**
	 * Script options.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $scriptOptions = [];

	public function addMediaScript(string $mediaRelative, bool $defer = true): DocumentInterface
	{
		$paths   = $this->getScriptFilePath($mediaRelative);
		$baseUri = $this->getContainer()->get('uri')->base(true);

		return $this->addScript($baseUri . '/' . $paths['relative'], $defer);
	}

	public function addScript(string $url, bool $defer = true): DocumentInterface
	{
		$this->scripts[$url] = [
			'defer' => $defer,
			'url'   => $url,
		];

		return $this;
	}

	public function removeScript(string $url): DocumentInterface
	{
		if (isset($this->scripts[$url]))
		{
			unset ($this->scripts[$url]);
		}

		return $this;
	}

	public function addScriptDeclaration(string $content): DocumentInterface
	{
		$this->scriptDeclarations[] = $content;

		return $this;
	}

	public function addScriptOptions(string $key, $options, bool $overwrite = false): DocumentInterface
	{
		if (empty($this->scriptOptions[$key]))
		{
			$this->scriptOptions[$key] = $options;

			return $this;
		}

		if ($overwrite || !is_array($options))
		{
			$this->scriptOptions[$key] = $options;

			return $this;
		}

		$this->scriptOptions[$key] = array_replace_recursive($this->scriptOptions[$key] ?? [], $options);

		return $this;
	}

	public function addScriptLanguage(string $languageKey): DocumentInterface
	{
		$languageKey = strtoupper(trim($languageKey));

		if (empty($languageKey))
		{
			return $this;
		}

		$this->addScriptOptions(
			'brs',
			[
				'language' => [
					$languageKey => $this->getContainer()->get('language')->text($languageKey),
				],
			]
		);

		return $this;
	}

	public function getScripts(): array
	{
		return $this->scripts;
	}

	public function getScriptDeclarations(): array
	{
		return $this->scriptDeclarations;
	}

	public function getScriptOptions(?string $key = null): array
	{
		if ($key === null)
		{
			return $this->scriptOptions;
		}

		return $this->scriptOptions[$key] ?? [];
	}
}