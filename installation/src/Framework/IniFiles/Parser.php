<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\IniFiles;

defined('_AKEEBA') or die();

final class Parser
{
	/**
	 * Parse a file containing INI-formatted data.
	 *
	 * @param   string  $file             The absolute path to the file to parse
	 * @param   bool    $processSections  False to flatten sections into the main array
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function parseFile(string $file, bool $processSections = false): array
	{
		$iniLines = file($file);

		if (!is_array($iniLines) || empty($iniLines))
		{
			return [];
		}

		return $this->parseIni($iniLines, $processSections);
	}

	/**
	 * Parse a string containing INI-formatted data.
	 *
	 * @param   string  $data             The INI data to parse
	 * @param   bool    $processSections  False to flatten sections into the main array
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function parseString(string $data, bool $processSections = false): array
	{
		$iniLines = explode("\n", str_replace("\r", "", $data));

		if (!is_array($iniLines) || empty($iniLines))
		{
			return [];
		}

		return $this->parseIni($iniLines, $processSections);
	}

	/**
	 * Parse an array of INI-formatted lines.
	 *
	 * @param   array  $iniLines         The INI-formatted lines to process
	 * @param   bool   $processSections  False to flatten sections into the main array
	 *
	 * @return  array
	 * @since   10.0
	 */
	private function parseIni(array $iniLines, bool $processSections = false): array
	{
		if (empty($iniLines))
		{
			return [];
		}

		$sections = [];
		$values   = [];
		$result   = [];
		$globals  = [];
		$i        = 0;

		foreach ($iniLines as $line)
		{
			$line = trim($line);
			$line = str_replace("\t", " ", $line);

			// Comments
			if (!preg_match('/^[a-zA-Z0-9[]/', $line))
			{
				continue;
			}

			// Sections
			if ($line[0] == '[')
			{
				$tmp        = explode(']', $line);
				$sections[] = trim(substr($tmp[0], 1));
				$i++;
				continue;
			}

			// Key-value pair
			$lineParts = explode('=', $line, 2);

			if (count($lineParts) != 2)
			{
				continue;
			}

			$key   = trim($lineParts[0]);
			$value = trim($lineParts[1]);

			unset($lineParts);

			if (strstr($value, ";"))
			{
				$tmp = explode(';', $value);

				if (count($tmp) == 2)
				{
					if ((($value[0] != '"') && ($value[0] != "'"))
					    || preg_match('/^".*"\s*;/', $value)
					    || preg_match('/^".*;[^"]*$/', $value)
					    || preg_match("/^'.*'\s*;/", $value)
					    || preg_match("/^'.*;[^']*$/", $value)
					)
					{
						$value = $tmp[0];
					}
				}
				else
				{
					if ($value[0] == '"')
					{
						$value = preg_replace('/^"(.*)".*/', '$1', $value);
					}
					elseif ($value[0] == "'")
					{
						$value = preg_replace("/^'(.*)'.*/", '$1', $value);
					}
					else
					{
						$value = $tmp[0];
					}
				}
			}

			$value = trim($value);
			$value = trim($value, "'\"");

			if ($i == 0)
			{
				if (substr($line, -1, 2) == '[]')
				{
					$globals[$key][] = $value;
				}
				else
				{
					$globals[$key] = $value;
				}
			}
			else
			{
				if (substr($line, -1, 2) == '[]')
				{
					$values[$i - 1][$key][] = $value;
				}
				else
				{
					$values[$i - 1][$key] = $value;
				}
			}
		}

		for ($j = 0; $j < $i; $j++)
		{
			if ($processSections === true)
			{
				if (isset($sections[$j]) && isset($values[$j]))
				{
					$result[$sections[$j]] = $values[$j];
				}
			}
			else
			{
				if (!isset($values[$j]))
				{
					continue;
				}

				if (is_array($values[$j]) && !empty($values[$j]))
				{
					$result = array_merge($result, $values[$j]);

					continue;
				}

				$result[] = $values[$j];
			}
		}

		return $result + $globals;
	}
}