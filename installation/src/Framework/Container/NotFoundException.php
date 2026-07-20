<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Container;

defined('_AKEEBA') or die();

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class NotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
	public function __construct($service = "", $code = 500, ?\Throwable $previous = null)
	{
		parent::__construct(
			sprintf('Service "%s" not found in the application container.', $service),
			$code,
			$previous
		);
	}

}