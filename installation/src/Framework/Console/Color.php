<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Console;

defined('_AKEEBA') or die();

final class Color
{
	/**
	 * Standard colors.
	 *
	 * Add to 30 for foreground colors. Add to 40 for background colors.
	 *
	 * @link  https://en.wikipedia.org/wiki/ANSI_escape_code#3-bit_and_4-bit
	 * @since 10.0
	 */
	private const COLORS = [
		'black'   => 0,
		'red'     => 1,
		'green'   => 2,
		'yellow'  => 3,
		'blue'    => 4,
		'magenta' => 5,
		'cyan'    => 6,
		'white'   => 7,
		'default' => 9,
	];

	/**
	 * Intense (bright) colors.
	 *
	 * Add to 90 for foreground colors. Add to 100 for background colors.
	 *
	 * @link  https://en.wikipedia.org/wiki/ANSI_escape_code#3-bit_and_4-bit
	 * @since 10.0
	 */
	private const INTENSE_COLORS = [
		'gray'           => 0,
		'bright-red'     => 1,
		'bright-green'   => 2,
		'bright-yellow'  => 3,
		'bright-blue'    => 4,
		'bright-magenta' => 5,
		'bright-cyan'    => 6,
		'bright-white'   => 7,
		'light-red'      => 1,
		'light-green'    => 2,
		'light-yellow'   => 3,
		'light-blue'     => 4,
		'light-magenta'  => 5,
		'light-cyan'     => 6,
		'light-white'    => 7,
	];

	/**
	 * Terminal SGR options.
	 *
	 * @link  https://en.wikipedia.org/wiki/ANSI_escape_code#Select_Graphic_Rendition_parameters
	 * @since 10.0
	 */
	private const AVAILABLE_OPTIONS = [
		'bold'            => ['set' => 1, 'unset' => 22],
		'faint'           => ['set' => 2, 'unset' => 22],
		'italic'          => ['set' => 3, 'unset' => 23],
		'underscore'      => ['set' => 4, 'unset' => 24],
		'underline'       => ['set' => 4, 'unset' => 24],
		'doubleunderline' => ['set' => 21, 'unset' => 24],
		'blink'           => ['set' => 5, 'unset' => 25],
		'fastblink'       => ['set' => 6, 'unset' => 25],
		'reverse'         => ['set' => 7, 'unset' => 27],
		'conceal'         => ['set' => 8, 'unset' => 28],
		'strike'          => ['set' => 9, 'unset' => 29],
		'font1'           => ['set' => 11, 'unset' => 10],
		'font2'           => ['set' => 12, 'unset' => 10],
		'font3'           => ['set' => 13, 'unset' => 10],
		'font4'           => ['set' => 14, 'unset' => 10],
		'font5'           => ['set' => 15, 'unset' => 10],
		'font6'           => ['set' => 16, 'unset' => 10],
		'font7'           => ['set' => 17, 'unset' => 10],
		'font8'           => ['set' => 18, 'unset' => 10],
		'font9'           => ['set' => 19, 'unset' => 10],
		'fraktur'         => ['set' => 20, 'unset' => 10],
		'proportional'    => ['set' => 26, 'unset' => 50],
		'framed'          => ['set' => 51, 'unset' => 54],
		'encircled'       => ['set' => 52, 'unset' => 54],
		'overlined'       => ['set' => 53, 'unset' => 55],
		'superscript'     => ['set' => 73, 'unset' => 75],
		'subscript'       => ['set' => 74, 'unset' => 75],
	];

	/**
	 * Foreground escape code
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $foreground;

	/**
	 * Background escape code
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $background;

	/**
	 * Options to set: bold, underscore, blink, reverse, conceal
	 *
	 * @var
	 * @since 10.0
	 */
	private $options;

	/**
	 * Creates a new color formatter
	 *
	 * @param   string  $foreground  Foreground color
	 * @param   string  $background  Background color
	 * @param   array   $options     Formatting options
	 */
	public function __construct(string $foreground = '', string $background = '', array $options = [])
	{
		$this->foreground = $this->parseColor($foreground);
		$this->background = $this->parseColor($background, true);
		$this->options    = $this->parseOptions($options);
	}

	/**
	 * Format the given text with the colors and options specified in the object.
	 *
	 * @param   string  $text  The text to be formatted.
	 *
	 * @return  string  The formatted text with applied settings.
	 * @since   10.0
	 */
	public function __invoke(string $text): string
	{
		return $this->set() . $text . $this->unset();
	}

	/**
	 * Returns the escape string to apply the colors and options.
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function set(): string
	{
		$setCodes = array_filter(
			[
				$this->foreground,
				$this->background,
			]
		);

		foreach ($this->options as $option)
		{
			$setCodes[] = $option['set'];
		}

		if (!count($setCodes))
		{
			return '';
		}

		return sprintf("\e[%sm", implode(';', $setCodes));
	}

	/**
	 * Returns the escape string to unset the colors and options.
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function unset(): string
	{
		$unsetCodes = array_filter(
			[
				$this->foreground ? 39 : '',
				$this->background ? 49 : '',
			]
		);

		foreach ($this->options as $option)
		{
			$unsetCodes[] = $option['unset'];
		}

		if (!count($unsetCodes))
		{
			return '';
		}

		return sprintf("\e[%sm", implode(';', $unsetCodes));
	}

	/**
	 * Parses the provided color string and returns the corresponding escape string.
	 *
	 * @param   string  $color       The name of the color to be parsed.
	 * @param   bool    $background  Optional. Is it a background color?
	 *
	 * @return  string  The corresponding color escape string.
	 */
	private function parseColor(string $color, bool $background = false): string
	{
		if (empty($color))
		{
			return '';
		}

		if (isset(self::COLORS[$color]))
		{
			return ($background ? '4' : '3') . self::COLORS[$color];
		}

		if (isset(self::INTENSE_COLORS[$color]))
		{
			return ($background ? '10' : '9') . self::INTENSE_COLORS[$color];
		}

		return '';
	}

	/**
	 * Parse the options, returning an array with the set and unset escape codes for each one.
	 *
	 * @param   array  $options  The options to parse
	 *
	 * @return  array
	 * @since   10.0
	 */
	private function parseOptions(array $options): array
	{
		if (empty($options))
		{
			return [];
		}

		return array_map(
			function (string $key): array {
				return self::AVAILABLE_OPTIONS[$key] ?? [];
			},
			array_intersect($options, array_keys(self::AVAILABLE_OPTIONS))
		);
	}

}