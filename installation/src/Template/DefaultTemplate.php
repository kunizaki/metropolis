<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Template;

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Document\Html as HtmlDocument;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

final class DefaultTemplate implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	public function applyDarkModeJavaScript(): void
	{
		$this->getContainer()->get('application')
			->getDocument()
			->addMediaScript('darkmode.min.js');
	}

	public function getLogo(): string
	{
		try
		{
			$type = $this->getContainer()->get('configuration')->type;
		}
		catch (\Exception $e)
		{
			$type = 'generic';
		}

		switch ($type)
		{
			case 'joomla':
				return <<< HTML
<div class="d-inline-flex flex-row gap-1 align-items-center"
	 data-bs-theme="dark" 
	 title="Akeeba Backup Site Restoration Script for Joomla!">
	<img src="media/images/akeeba.svg" width="80px" aria-hidden="true">
	<span class="fw-light fs-6 text-body-tertiary" aria-hidden="true">for</span>
	<span class="fa-brands fa-joomla" aria-hidden="true"></span>
</div>

HTML;

			case 'wordpress':
				return <<< HTML
<div class="d-inline-flex flex-row gap-1 align-items-center"
	 data-bs-theme="dark" 
	 title="Akeeba Backup Site Restoration Script for Wordpress">
	<img src="media/images/akeeba.svg" width="80px" aria-hidden="true">
	<span class="fw-light fs-6 text-body-tertiary" aria-hidden="true">for</span>
	<span class="fa-brands fa-wordpress" aria-hidden="true"></span>
</div>
HTML;

			default:
				return "<img src=\"media/images/akeeba.svg\" width=\"80px\" class=\"me-2\" alt=\"Akeeba Backup Site Restoration Script\">";
		}
	}
}