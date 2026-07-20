<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Platform\Parser\Tokenizer;

use Exception;

defined('_AKEEBA') or die();

final class Parser
{
	/**
	 * Converts the strings for certain PHP primitive types (NULL, TRUE, FALSE) into their respective values.
	 *
	 * @var   array
	 * @since 10.0
	 */
	private static $CONSTANTS = [
		"null"  => null,
		"true"  => true,
		"false" => false,
	];

	/**
	 * The parsed tokens
	 *
	 * @var   Tokens
	 * @since 10.0
	 */
	private $tokens;

	/**
	 * Constructor.
	 *
	 * @param   Tokens  $tokens  The Tokens object for a piece of PHP code.
	 *
	 * @since   10.0
	 */
	public function __construct(Tokens $tokens)
	{
		$this->tokens = $tokens;
	}

	/**
	 * Parse the next tokens assuming they are an array.
	 *
	 * @return  array
	 * @throws Exception
	 * @since   10.0
	 */
	public function parseArray(): array
	{
		$found  = 0;
		$result = [];

		$this->tokens->forceMatch(T_ARRAY);
		$this->tokens->forceMatch("(");

		while (true)
		{
			if ($this->tokens->doesMatch(")"))
			{
				// Reached the end of the array
				$this->tokens->forceMatch(")");
				break;
			}

			if ($found > 0)
			{
				// We must see a comma following the first element
				$this->tokens->forceMatch(",");
			}

			if ($this->tokens->doesMatch(T_ARRAY))
			{
				// Nested array
				$result[] = $this->parseArray();
			}
			elseif ($this->tokens->doesMatch(T_CONSTANT_ENCAPSED_STRING))
			{
				// string
				$string = $this->parseValue();

				if ($this->tokens->doesMatch(T_DOUBLE_ARROW))
				{
					// Array key (key => value)
					$this->tokens->pop();
					$result[$string] = $this->parseValue();
				}
				else
				{
					// Simple string
					$result[] = $string;
				}
			}
			else
			{
				$result[] = $this->parseValue();
			}

			++$found;
		}

		return $result;
	}

	/**
	 * Parse the next tokens assuming they are a scalar.
	 *
	 * @return  mixed
	 * @throws Exception
	 * @since   10.0
	 */
	public function parseValue()
	{
		if ($this->tokens->doesMatch(T_CONSTANT_ENCAPSED_STRING))
		{
			// Strings
			$token = $this->tokens->pop();

			return stripslashes(substr($token[1], 1, -1));
		}

		if ($this->tokens->doesMatch(T_STRING))
		{
			// Built-in string literals: null, false, true
			$token = $this->tokens->pop();
			$value = strtolower($token[1]);

			if (array_key_exists($value, self::$CONSTANTS))
			{
				return self::$CONSTANTS[$value];
			}

			throw new Exception("unexpected string literal " . $token[1]);
		}

		// We expect a number here
		$uminus = 1;

		if ($this->tokens->doesMatch("-"))
		{
			// Unary minus
			$this->tokens->forceMatch("-");
			$uminus = -1;
		}

		if ($this->tokens->doesMatch(T_LNUMBER))
		{
			// Long
			$value = $this->tokens->pop();

			return $uminus * (int) $value[1];
		}
		if ($this->tokens->doesMatch(T_DNUMBER))
		{
			// Double
			$value = $this->tokens->pop();

			return $uminus * (double) $value[1];
		}

		throw new Exception("Unexpected value token");
	}
}