<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Platform\Parser\Tokenizer;

defined('_AKEEBA') or die();

use Exception;

/**
 * Utility class to manage a tokenized piece of PHP code.
 *
 * @since  10.0
 */
final class Tokens
{
	/**
	 * The tokens upon parsing a piece of code.
	 *
	 * @var   array
	 * @since 10.0
	 */
	private $tokens;

	/**
	 * Constructor
	 *
	 * @param   string  $code  The code to parse
	 *
	 * @throws  Exception
	 * @since   10.0
	 */
	public function __construct(string $code)
	{
		// construct PHP code from string and tokenize it
		$tokens = token_get_all("<?php " . $code);

		// kick out whitespace tokens
		$this->tokens = array_filter(
			$tokens,
			function ($token) {
				return (!is_array($token) || $token[0] !== T_WHITESPACE);
			}
		);

		// Remove the start token (<?php)
		$this->pop();
	}

	/**
	 * Does the next token match what we're looking for?
	 *
	 * @param   mixed  $what  A token type constant, or a scalar.
	 *
	 * @return  bool
	 * @throws Exception
	 * @since   10.0
	 */
	public function doesMatch($what): bool
	{
		$token = $this->peek();

		if (is_string($what) && !is_array($token) && $token === $what)
		{
			return true;
		}

		if (is_int($what) && is_array($token) && $token[0] === $what)
		{
			return true;
		}

		return false;
	}

	/**
	 * Have we reached the end of the tokens stream?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function done(): bool
	{
		return count($this->tokens) === 0;
	}

	/**
	 * Make sure the next token matches what we're looking for, and go to the next token.
	 *
	 * @param   mixed  $what  A token type constant, or a scalar.
	 *
	 * @return  void
	 * @throws Exception
	 * @since   10.0
	 */
	public function forceMatch($what)
	{
		if (!$this->doesMatch($what))
		{
			if (is_int($what))
			{
				throw new Exception("Unexpected token - expecting " . token_name($what));
			}

			throw new Exception("Unexpected token - expecting " . $what);
		}

		// Consume the token
		$this->pop();
	}

	/**
	 * Return the next token, without removing it from the stream.
	 *
	 * @return  mixed
	 * @throws Exception
	 * @since   10.0
	 */
	public function peek()
	{
		// return next token, don't consume it
		if ($this->done())
		{
			throw new Exception("already at end of tokens!");
		}

		return $this->tokens[0];
	}

	/**
	 * Return the next token, removing it from the stream.
	 *
	 * @return  mixed
	 * @throws Exception
	 * @since   10.0
	 */
	public function pop()
	{
		// consume the token and return it
		if ($this->done())
		{
			throw new Exception("already at end of tokens!");
		}

		return array_shift($this->tokens);
	}
}