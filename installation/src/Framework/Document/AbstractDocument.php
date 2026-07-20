<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Document;

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

/**
 * Abstract document object class.
 *
 * @since  10.0
 */
abstract class AbstractDocument implements DocumentInterface, ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * The output buffer.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $buffer = '';

	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}


	/** @inheritdoc  */
	public function setBuffer(string $buffer): DocumentInterface
	{
		$this->buffer = $buffer;

		return $this;
	}

	/** @inheritdoc  */
	public function getBuffer(): string
	{
		return $this->buffer;
	}
}