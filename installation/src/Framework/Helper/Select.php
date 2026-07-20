<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Helper;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Database\AbstractDriver;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * Helper object to create SELECT fields in the HTML output.
 *
 * @since  10.0
 */
final class Select implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Default formatting options
	 *
	 * @var   array
	 * @since 10.0
	 */
	public $formatOptions = ['format.depth' => 0, 'format.eol' => "\n", 'format.indent' => "\t"];

	/**
	 * Default values for options. Organized by option group.
	 *
	 * @var     array
	 */
	protected $optionDefaults = [
		'option' => [
			'option.attr'         => null,
			'option.disable'      => 'disable',
			'option.id'           => null,
			'option.key'          => 'value',
			'option.key.toHtml'   => true,
			'option.label'        => null,
			'option.label.toHtml' => true,
			'option.text'         => 'text',
			'option.text.toHtml'  => true,
		],
	];

	/**
	 * Constructor
	 *
	 * @param   ContainerInterface  $container
	 *
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Create an object that represents an option in an option list.
	 *
	 * The available options are:
	 * * `attr`. String|array. Additional attributes for this option. Defaults to none.
	 * * `disable`. Boolean. If set, this option is disabled.
	 * * `label`: String. The value for the option label.
	 * * `option.attr`: The property in each option array to use for additional selection attributes. Defaults to none.
	 * * `option.disable`: The property that will hold the disabled state. Defaults to "disable".
	 * * `option.key`: The property that will hold the selection value. Defaults to "value".
	 * * `option.label`: The property in each option array to use as the selection label attribute. If a "label" option
	 *    is provided, defaults to "label", if no label is given, defaults to null (none).
	 * * `option.text`: The property that will hold the the displayed text. Defaults to "text". If set to null, the
	 *    option array is assumed to be a list of displayable scalars.
	 *
	 * @param   string  $value    The value of the option.
	 * @param   string  $text     The text for the option.
	 * @param   array   $options  An array of options.
	 *
	 * @return  object
	 */
	public function option(string $value, string $text = '', array $options = []): object
	{
		$options = array_merge(
			[
				'attr'           => null,
				'disable'        => false,
				'option.attr'    => null,
				'option.disable' => 'disable',
				'option.key'     => 'value',
				'option.label'   => null,
				'option.text'    => 'text',
			], $options
		);

		$obj                            = new stdClass();
		$obj->{$options['option.key']}  = $value;
		$obj->{$options['option.text']} = trim($text) ? $text : $value;

		/*
		 * If a label is provided, save it. If no label is provided and there is a label name, initialise to an empty
		 *  string.
		 */
		$hasProperty = $options['option.label'] !== null;

		if (isset($options['label']))
		{
			$labelProperty       = $hasProperty ? $options['option.label'] : 'label';
			$obj->$labelProperty = $options['label'];
		}
		elseif ($hasProperty)
		{
			$obj->{$options['option.label']} = '';
		}

		// Set attributes only if there is a property and a value
		if ($options['attr'] !== null)
		{
			$obj->{$options['option.attr']} = $options['attr'];
		}

		// Set disable only if it has a property and a value
		if ($options['disable'] !== null)
		{
			$obj->{$options['option.disable']} = $options['disable'];
		}

		return $obj;
	}

	/**
	 * Generates the option tags for an HTML select list (with no select tag surrounding the options).
	 *
	 * The options are:
	 *
	 * -Format options
	 * * `list.select`. Either the value of one selected option, or an array of selected options. Default: none.
	 * * `list.translate`. Boolean. If set, text and labels get translated. Default is false.
	 * * `option.id`: The property in each option array to use as the selection ID attribute. Defaults to none.
	 * * `option.key`: The property in each option array to use as the selection value. Defaults to "value". If set
	 *    to null, the index of the option array is used.
	 * * `option.label`. The property in each option array to use as the selection label attribute. Defaults to null
	 *   (none).
	 * * `option.text`. The property in each option array to use as the displayed text. Defaults to "text". If set to
	 *   null, the option array is assumed to be a list of displayable scalars.
	 * * `option.attr`. The property in each option array to use for additional selection attributes. Defaults to none.
	 * * `option.disable`: The property that will hold the disabled state. Defaults to "disable".
	 * * `option.key`: The property that will hold the selection value. Defaults to "value".
	 * * `option.text`: The property that will hold the displayed text. Defaults to "text". If set to null, the option
	 *   array is assumed to be a list of displayable scalars.
	 *
	 * @param   array  $arr      An array of objects, arrays, or values.
	 * @param   array  $options  As per the above notes
	 *
	 * @return  string  HTML for the select list
	 */
	public function options(array $arr, array $options = []): string
	{
		$options = array_merge(
			$this->formatOptions,
			$this->optionDefaults['option'],
			[
				'format.depth'   => 0,
				'list.select'    => null,
				'list.translate' => false,
			],
			$options
		);

		$html       = '';
		$baseIndent = str_repeat($options['format.indent'], $options['format.depth']);
		$lang       = $this->getContainer()->get('language');

		foreach ($arr as $elementKey => &$element)
		{
			$attr  = '';
			$extra = '';
			$label = '';
			$id    = '';

			if (is_scalar($element))
			{
				$key  = $elementKey;
				$text = $element;
			}
			else
			{
				$element = (array) $element;
				$key     = isset($options['option.key']) ? $element[$options['option.key']] : $elementKey;
				$text    = isset($options['option.text']) ? $element[$options['option.text']] : '';
				$attr    = isset($options['option.attr']) ? ($element[$options['option.attr']] ?? '') : '';
				$id      = isset($options['option.id']) ? ($element[$options['option.id']] ?? '') : '';
				$label   = isset($options['option.label']) ? ($element[$options['option.label']] ?? '') : '';

				if ($element[$options['option.disable']] ?? false)
				{
					$extra .= ' disabled="disabled"';
				}
			}

			$key = (string) $key;

			if ($key === '<OPTGROUP>')
			{
				$html       .= $baseIndent . '<optgroup label="' . ($options['list.translate'] ? $lang->text($text)
						: $text) . '">' . $options['format.eol'];
				$baseIndent = str_repeat($options['format.indent'], ++$options['format.depth']);

				continue;
			}

			if ($key == '</OPTGROUP>')
			{
				$baseIndent = str_repeat($options['format.indent'], --$options['format.depth']);
				$html       .= $baseIndent . '</optgroup>' . $options['format.eol'];

				continue;
			}

			// If no string after hyphen - take hyphen out
			$splitText = explode(' - ', $text, 2);
			$text      = $splitText[0];

			if (isset($splitText[1]))
			{
				$text .= ' - ' . $splitText[1];
			}

			if ($options['list.translate'] && !empty($label))
			{
				$label = $lang->text($label);
			}

			if ($options['option.label.toHtml'])
			{
				$label = htmlentities($label);
			}

			$attr  = is_array($attr) ? $this->toString($attr) : trim($attr);
			$extra = ($id ? ' id="' . $id . '"' : '') . ($label ? ' label="' . $label . '"' : '') . ($attr ? ' ' . $attr
					: '') . $extra;

			if (is_array($options['list.select']))
			{
				foreach ($options['list.select'] as $val)
				{
					$key2 = is_object($val) ? $val->{$options['option.key']} : $val;

					if ($key == $key2)
					{
						$extra .= ' selected="selected"';
						break;
					}
				}
			}
			elseif ($key == (string) $options['list.select'])
			{
				$extra .= ' selected="selected"';
			}

			if ($options['list.translate'])
			{
				$text = $lang->text($text);
			}

			// Generate the option, encoding as required
			$html .= $baseIndent . '<option value="' . ($options['option.key.toHtml'] ? htmlspecialchars(
					$key, ENT_COMPAT, 'UTF-8'
				) : $key) . '"'
			         . $extra . '>';

			$html .= $options['option.text.toHtml']
				? htmlentities(html_entity_decode($text, ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 'UTF-8')
				: $text;
			$html .= '</option>' . $options['format.eol'];
		}

		return $html;
	}

	/**
	 * Generates an HTML selection list.
	 *
	 * @param   array    $data       An array of objects, arrays, or scalars.
	 * @param   string   $name       The value of the HTML name attribute.
	 * @param   mixed    $attribs    Additional HTML attributes for the <select> tag. This
	 *                               can be an array of attributes, or an array of options. Treated as options
	 *                               if it is the last argument passed. Valid options are:
	 *                               Format options, see $formatOptions.
	 *                               Selection options.
	 *                               list.attr, string|array: Additional attributes for the select
	 *                               element.
	 *                               id, string: Value to use as the select element id attribute.
	 *                               Defaults to the same as the name.
	 *                               list.select, string|array: Identifies one or more option elements
	 *                               to be selected, based on the option key values.
	 * @param   string   $optKey     The name of the object variable for the option value. If
	 *                               set to null, the index of the value array is used.
	 * @param   string   $optText    The name of the object variable for the option text.
	 * @param   mixed    $selected   The key that is selected (accepts an array or a string).
	 * @param   mixed    $idtag      Value of the field id or null by default
	 * @param   boolean  $translate  True to translate
	 *
	 * @return  string  HTML for the select list.
	 */
	public function genericlist(array $data, string $name, array $attribs = [])
	{
		// Set default options
		$options = array_merge(
			$this->formatOptions,
			['format.depth' => 0, 'id' => false],
			$attribs
		);

		$attribs = '';

		if (isset($options['list.attr']))
		{
			if (is_array($options['list.attr']))
			{
				$attribs = $this->toString($options['list.attr']);
			}
			else
			{
				$attribs = $options['list.attr'];
			}

			if ($attribs != '')
			{
				$attribs = ' ' . $attribs;
			}
		}

		$id = $options['id'] !== false ? $options['id'] : $name;
		$id = str_replace(['[', ']'], '', $id);

		$baseIndent = str_repeat($options['format.indent'], $options['format.depth']++);
		$html       = $baseIndent . '<select' . ($id !== '' ? ' id="' . $id . '"' : '') . ' name="' . $name . '"'
		              . $attribs . '>' . $options['format.eol']
		              . $this->options($data, $options) . $baseIndent . '</select>' . $options['format.eol'];

		return $html;
	}

	/**
	 * Utility function to map an array to a string.
	 *
	 * @param   array|null  $array         $array         The array to map.
	 * @param   string      $inner_glue    The glue (optional, defaults to '=') between the key and the value.
	 * @param   string      $outer_glue    The glue (optional, defaults to ' ') between array elements.
	 * @param   boolean     $keepOuterKey  True if final key should be kept.
	 *
	 * @return  string   The string mapped from the given array
	 */
	public function toString(
		?array $array = null, string $inner_glue = '=', string $outer_glue = ' ', bool $keepOuterKey = false
	): string
	{
		$output = [];

		if (!is_array($array))
		{
			return '';
		}

		foreach ($array as $key => $item)
		{
			if (!is_array($item))
			{
				$output[] = $key . $inner_glue . '"' . $item . '"';

				continue;
			}

			if ($keepOuterKey)
			{
				$output[] = $key;
			}

			// This is value is an array, go and do it again!
			$output[] = $this->toString($item, $inner_glue, $outer_glue, $keepOuterKey);
		}

		return implode($outer_glue, $output);
	}

	public function dbtype($selected = 'mysqli', $technology = null)
	{
		$lang            = $this->getContainer()->get('language');
		$connectors      = AbstractDriver::getConnectors($technology);
		$limitConnectors = $this->getContainer()->get('configuration')->limitDrivers;

		// If platform.json defines a non-empty list of limitDrivers, filter our connectors by it.
		if (!empty($limitConnectors))
		{
			$connectors = array_intersect($limitConnectors, $connectors);
		}

		return $this->genericlist(
			array_map(
				function ($connector) use ($lang) {
					return $this->option($connector, $lang->text('DATABASE_LBL_TYPE_' . $connector));
				},
				$connectors
			),
			'dbtype', [
				'list.select' => $selected,
				'list.attr'   => [
					'class' => 'form-select',
				],
			]
		);
	}

	public function superusers($selected = null, $name = 'superuserid', $id = 'superuserid')
	{
		$options = [];

		$params = $this->getContainer()->get('mvcFactory')->tempModel('Setup')->getStateVariables();

		if (isset($params))
		{
			$superusers = $params->superusers;

			foreach ($superusers as $sa)
			{
				$options[] = $this->option($sa->id, $sa->username);
			}
		}

		return $this->genericlist(
			$options, $name, [
				'list.select' => $selected,
				'list.attr' => [
					'class' => 'form-select'
				],
				'option.id'   => $id,
				'onchange'    => 'setupSuperUserChange()',
			]
		);
	}

	public function forceSSL($selected = '0')
	{
		$lang = $this->getContainer()->get('language');

		$options[] = $this->option(0, $lang->text('SETUP_LABEL_FORCESSL_NONE'));
		$options[] = $this->option(1, $lang->text('SETUP_LABEL_FORCESSL_ADMINONLY'));
		$options[] = $this->option(2, $lang->text('SETUP_LABEL_FORCESSL_ENTIRESITE'));

		return $this->genericlist($options, 'force_ssl', [
			'list.select' => $selected,
			'list.attr' => [
				'class' => 'form-select'
			]
		]);
	}

}