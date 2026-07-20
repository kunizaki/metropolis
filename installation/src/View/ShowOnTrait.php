<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\View;

defined('_AKEEBA') or die();

/**
 * View trait to handle conditionally displayed (‘show on’) fields.
 *
 * @since  10.0
 */
trait ShowOnTrait
{
	/**
	 * Indicates whether the "Show On Javascript" feature has been included.
	 *
	 * @since  10.0
	 */
	private $hasIncludedShowOnJavascript = false;

	/**
	 * Generate data-showon attributes from ShowOn conditions
	 *
	 * @param   string|null  $showOn     The ShowOn expression e.g. `foo:1[AND]bar!:2[OR]baz:bat`
	 * @param   string|null  $arrayName  If the fields are wrapped in an array element, what's the array name
	 *
	 * @return  string
	 * @since   10.0
	 */
	protected function showOn(?string $showOn, ?string $arrayName = null): string
	{
		$this->conditionalIncludeShowOnJavascript();

		$conditions = $this->parseShowOnConditions($showOn, $arrayName);

		return empty($conditions)
			? ''
			: sprintf(
				'data-showon="%s"',
				$this->escape(json_encode($this->parseShowOnConditions($showOn, $arrayName)))
			);
	}

	/**
	 * Converts ShowOn expressions to the internal data required by showon.js
	 *
	 * @param   string|null  $showOn     The ShowOn expression e.g. `foo:1[AND]bar!:2[OR]baz:bat`
	 * @param   string|null  $arrayName  If the fields are wrapped in an array element, what's the array name
	 *
	 * @return  array
	 * @since   10.0
	 */
	private function parseShowOnConditions(?string $showOn, ?string $arrayName = null): array
	{
		if (empty($showOn))
		{
			return [];
		}

		$showOnData  = [];
		$showOnParts = preg_split('#(\[AND\]|\[OR\])#', $showOn, -1, PREG_SPLIT_DELIM_CAPTURE);
		$op          = '';

		foreach ($showOnParts as $showOnPart)
		{
			if (in_array($showOnPart, ['[AND]', '[OR]']))
			{
				$op = trim($showOnPart, '[]');

				continue;
			}

			$compareEqual     = strpos($showOnPart, '!:') === false;
			$showOnPartBlocks = explode(($compareEqual ? ':' : '!:'), $showOnPart, 2);

			$field = $arrayName
				? sprintf("%s[%s]", $arrayName, $showOnPartBlocks[0])
				: $showOnPartBlocks[0];

			$showOnData[] = [
				'field'  => $field,
				'values' => explode(',', $showOnPartBlocks[1]),
				'sign'   => $compareEqual === true ? '=' : '!=',
				'op'     => $op,
			];

			$op = '';
		}

		return $showOnData;
	}

	/**
	 * Includes the ShowOn JavaScript file if it has not been included already.
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function conditionalIncludeShowOnJavascript(): void
	{
		if ($this->hasIncludedShowOnJavascript)
		{
			return;
		}

		$this->hasIncludedShowOnJavascript = true;

		$this->getContainer()->get('application')->getDocument()->addMediaScript('showon.js');
	}
}