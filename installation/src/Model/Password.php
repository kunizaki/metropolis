<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Model;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Mvc\Model;

/**
 * Model for the password view.
 *
 * @since  10.0
 */
class Password extends Model
{
	/**
	 * Is the provided user password correct?
	 *
	 * @param   string|null  $password
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function isPasswordCorrect(?string $password): bool
	{
		$userPassword = $password ?? '';
		$parts        = $this->getPasswordParts();
		$userHash     = hash('md5', $userPassword . $parts['salt']);

		return hash_equals($parts['hash'], $userHash);
	}

	/**
	 * Retrieves and splits the password definition into its hash and salt components.
	 *
	 * @return array{hash: string, salt: string}
	 * @since  10.0
	 */
	private function getPasswordParts(): array
	{
		$return = [
			'hash' => '',
			'salt' => '',
		];

		$passDef = defined('AKEEBA_PASSHASH') ? constant('AKEEBA_PASSHASH') : ':';

		if (!strpos($passDef, ':'))
		{
			return $return;
		}

		$parts = explode(':', $passDef, 2);

		if (count($parts) != 2)
		{
			return $return;
		}

		$return['hash'] = $parts[0];
		$return['salt'] = $parts[1];

		return $return;
	}
}