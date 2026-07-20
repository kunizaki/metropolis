<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Platform\Parser;

defined('_AKEEBA') or die();

use Akeeba\BRS\Platform\Parser\AbstractParser;
use Akeeba\BRS\Platform\Parser\Tokenizer\Parser;
use Akeeba\BRS\Platform\Parser\Tokenizer\Tokens;

/**
 * A configuration parser class which uses the PHP Tokenizer extension.
 *
 * @since  10.0
 */
class TokenParser extends AbstractParser
{
	/** @inheritDoc */
	protected $priority = 500;

	/**
	 * @inheritDoc
	 */
	public function isSupported(): bool
	{
		return function_exists('token_get_all');
	}

	/**
	 * @inheritDoc
	 */
	public function parseFile(string $file, string $className): array
	{
		$ret          = [];
		$fileContents = $this->cleanComments(file_get_contents($file));
		$fileContents = str_replace("\r\n", "\n", $fileContents);
		$fileContents = str_replace("\r", "\n", $fileContents);
		$fileLines    = explode("\n", $fileContents);

		$inLine = false;

		foreach ($fileLines as $line)
		{
			$line = trim($line);

			if (!$inLine)
			{
				if ((strpos($line, 'public') !== 0) && (strpos($line, 'var') !== 0))
				{
					continue;
				}

				$inLine = true;

				if (strpos($line, 'public') === 0)
				{
					$code = substr($line, 6);
				}
				else
				{
					$code = substr($line, 3);
				}
			}

			if (substr($line, -1) != ';')
			{
				$code .= substr($line, 0, -1);

				continue;
			}

			$code = trim($code);

			if (version_compare(PHP_VERSION, '7.1.0', 'lt'))
			{
				[$key, $value] = explode('=', $code, 2);
			}
			else
			{
				[$key, $value] = explode('=', $code, 2);
			}

			$key    = ltrim(trim($key), '$');
			$value  = trim(ltrim($value, ';'));
			$parser = new Parser(new Tokens($value));

			try
			{
				if (strpos($value, 'array') === 0 || strpos($value, '[') === 0)
				{
					$value = $parser->parseArray();
				}
				else
				{
					$value = $parser->parseValue();
				}
			}
			catch (\Exception $e)
			{
				$inLine = false;

				continue;
			}

			$ret[$key] = $value;

			$inLine = false;
		}

		return $ret;
	}

	/**
	 * Remove all comments from the PHP code
	 *
	 * @param   string  $phpCode
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function cleanComments(string $phpCode): string
	{
		$tokens        = token_get_all($phpCode);
		$commentTokens = [T_COMMENT];

		if (defined('T_DOC_COMMENT'))
		{
			$commentTokens[] = T_DOC_COMMENT;
		}

		if (defined('T_ML_COMMENT'))
		{
			$commentTokens[] = T_ML_COMMENT;
		}

		$newStr = '';

		foreach ($tokens as $token)
		{
			if (is_array($token))
			{
				if (in_array($token[0], $commentTokens))
				{
					/**
					 * If the comment ended in a newline we need to output the newline. Otherwise we will have
					 * run-together lines which won't be parsed correctly by parseWithoutTokenizer.
					 */
					if (substr($token[1], -1) == "\n")
					{
						$newStr .= "\n";
					}

					continue;
				}

				$token = $token[1];
			}

			$newStr .= $token;
		}

		return $newStr;
	}
}