<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\View\Database;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Database\FixMySQLTrait;
use Akeeba\BRS\Framework\Document\Html as HtmlDocument;
use Akeeba\BRS\Framework\Helper\Select;
use Akeeba\BRS\Framework\Mvc\View;
use Akeeba\BRS\Model\Database;
use Akeeba\BRS\View\ShowOnTrait;
use Akeeba\BRS\View\StepsTrait;

/**
 * View controller for the database view.
 *
 * @since  10.0
 */
class Html extends View
{
	use FixMySQLTrait;
	use StepsTrait;
	use ShowOnTrait;

	/**
	 * The database key.
	 *
	 * @var   string
	 * @since 10.0
	 */
	public $key;

	/**
	 * The current step's database configuration.
	 *
	 * @var   \Akeeba\BRS\Framework\Configuration\Database|null
	 * @since 10.0
	 */
	public $db;

	/**
	 * Do we have a flag for large tables?
	 *
	 * @var int
	 * @since 10.0
	 */
	public $large_tables = 0;

	/**
	 * Maximum packet size, from the database connection.
	 *
	 * @var   int
	 * @since 10.0
	 */
	public $maxPacketSize = 0;

	/**
	 * Recommended packet size, when we have queries over 1MiB big.
	 *
	 * @var   int
	 * @since 10.0
	 */
	public $recommendedPacketSize = 0;

	/**
	 * Database server hostname with the port or socket included into it.
	 *
	 * @var   string
	 * @since 10.0
	 */
	public $combinedHost;

	/**
	 * SELECT element with the tables included in the dump, allowing the user to select which ones to restore.
	 *
	 * @var   string
	 * @since 10.0
	 */
	public $table_list = '';

	/**
	 * The Select helper object.
	 *
	 * @var   Select
	 * @since 10.0
	 */
	public $selectHelper;

	public function onBeforeMain(): bool
	{
		/**
		 * @var Database     $model
		 * @var HtmlDocument $doc
		 */
		$model              = $this->getModel();
		$doc                = $this->getContainer()->get('application')->getDocument();
		$this->selectHelper = new Select($this->getContainer());
		$text               = $this->getContainer()->get('language');

		$this->db = $model->getDatabaseInfo($this->key);

		if ($this->db->dbtype === 'none')
		{
			$this->setLayout('nodb');

			$this->addButtonPreviousStep();
			$this->addButtonNextStep();

			return true;
		}

		if (!$model->hasAllNecessaryConnectors())
		{
			$this->setLayout('noconnectors');

			return true;
		}

		// Buttons
		$doc->appendButton('GENERAL_BTN_INLINE_HELP', 'btn-outline-info', 'fa-info-circle', 'show-help');
		$this->addButtonPreviousStep();
		$this->addButtonNextStep(true);
		$this->addButtonSubmitStep();

		// JavaScript
		$doc->addMediaScript('database.js');

		// Help URL
		$doc->setHelpURL('https://www.akeeba.com/documentation/brs/database.html');

		// Text strings
		$doc->addScriptLanguage('DATABASE_ERR_COMPLEXPASSWORD');
		$doc->addScriptLanguage('DATABASE_ERR_UPPERCASEPREFIX');
		$doc->addScriptLanguage('DATABASE_ERR_EMPTY_INFO');

		// View data
		$this->combinedHost = $this->createMySQLHostname(
			$this->db->dbhost, $this->db->dbport, $this->db->dbsocket, $this->db->dbencryption
		);
		$this->large_tables = $model->getLargeTablesDetectedValue();
		$maxPacket          = $model->getCurrentMaxPacketSize($this->key);
		$this->large_tables = ($this->large_tables < $maxPacket) ? false : $this->large_tables;

		// Do we have a list of tables? If so let's display them to the user
		if ($this->db->tables ?: [])
		{
			$table_data = [];

			foreach ($this->flattenedTableList() as $table)
			{
				$table_data[] = $this->selectHelper->option($table, $table);
			}

			$select_attribs   = [
				'list.attr' => [
					'data-placeholder' => $text->text('DATABASE_LBL_SPECIFICTABLES_LBL'),
					'multiple'         => 'true',
					'size'             => 10,
					'class'            => 'form-select',
				],
			];
			$this->table_list = $this->selectHelper->genericlist(
				$table_data, 'specific_tables', $select_attribs
			);
		}

		// Do I have large tables?
		if ($this->large_tables)
		{
			$this->maxPacketSize         = round($maxPacket / (1024 * 1024), 2);
			$this->recommendedPacketSize = ceil($this->large_tables / (1024 * 1024));
			$this->large_tables          = round($this->large_tables / (1024 * 1024), 2);
		}

		// Joomla-specific configuration
		if ($this->getContainer()->get('configuration')->type == 'joomla')
		{
			$jVersion = $this->getContainer()->get('session')->get('jversion', '2.5.0');

			// Joomla 4 and later versions: Supports MySQL SSL/TLS connections
			if (version_compare($jVersion, '4.0.0', 'ge'))
			{
				define('ANGIE_DB_ALLOW_SSL', 1);
			}
		}

		// All installer scripts: The port/socket is included in the hostname string.
		if ($this->db->dbport || $this->db->dbsocket)
		{
			$this->db->setDbhost($this->createMySQLHostname($this->db->dbhost, $this->db->dbport, $this->db->dbsocket));
			$this->db->setDbport(null);
			$this->db->setDbsocket(null);
		}

		// Script options – MUST BE LAST
		$doc->addScriptOptions(
			'brs.database', [
				'dbuser' => $this->db->dbuser,
				'dbpass' => $this->db->dbpass,
				'key'    => $this->key,
			]
		);

		return true;
	}

	private function flattenedTableList(): array
	{
		$tables = $this->db->tables;

		if (empty($tables))
		{
			return [];
		}

		$isPlainArray = array_reduce(
			array_keys($tables),
			function ($carry, $item) {
				return $carry && is_int($item);
			},
			true
		);

		if ($isPlainArray)
		{
			return $tables;
		}

		$ret  = [];
		$text = $this->getContainer()->get('language');

		foreach ($tables as $section => $data)
		{
			foreach ($data as $item)
			{
				$ret[$section . '.' . $item] = sprintf(
					'%s&emsp;(%s)',
					$item,
					$text->text('DATABASE_LBL_ENTITY_TYPE_' . strtoupper($section))
				);
			}
		}

		return $ret;
	}
}