<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2009-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') or die();

/**
 * @var \Akeeba\BRS\View\Dbrestore\Html $this
 */

$text = $this->getContainer()->get('language');
?>

<h4><?= $text->text('DATABASE_RESTORE_DBNAME_HEAD') ?></h4>

<p>
	<?= $text->text('DATABASE_RESTORE_DBNAME_LBL_EXPLAIN') ?>
</p>
<p>
	<?= $text->text('DATABASE_RESTORE_COMMON_LBL_ERROR_RECEIVED') ?>
</p>

<div class="alert alert-danger">
	<div>
		<?php $exc = $this->exception;
		do {
		?>
			<p>
				<?= $exc->getMessage() ?>
			</p>
		<?php
		} while ($exc = $exc->getPrevious());
		?>
	</div>
</div>

<h4 class="d-flex flex-row justify-content-between align-items-center">
	<span>
		<?= $text->text('DATABASE_RESTORE_COMMON_LBL_HOWTOFIX') ?>
	</span>
	<button type="button"
			class="btn btn-sm btn-outline-secondary"
			data-bs-toggle="collapse" data-bs-target="#dbrestore-how-to-fix"
			aria-expanded="false" aria-controls="dbrestore-how-to-fix"
	>
		<?= $text->text('DATABASE_RESTORE_COMMON_LBL_SHOWHIDE') ?>
	</button>
</h4>

<div id="dbrestore-how-to-fix" class="collapse">
	<ul>
		<li>
			<?= $text->text('DATABASE_RESTORE_DBUSER_SUGGESTION_2') ?>
		</li>
		<li>
			<?= $text->text('DATABASE_RESTORE_DBUSER_SUGGESTION_4') ?>
		</li>
		<li>
			<?= $text->text('DATABASE_RESTORE_DBUSER_SUGGESTION_5') ?>
		</li>
		<li>
			<?= $text->text('DATABASE_RESTORE_DBUSER_SUGGESTION_6') ?>
		</li>
	</ul>
</div>

<h4 class="d-flex flex-row justify-content-between align-items-center">
	<span>
		<?= $text->text('DATABASE_RESTORE_COMMON_LBL_DEBUG') ?>
	</span>
	<button type="button"
			class="btn btn-sm btn-outline-secondary"
			data-bs-toggle="collapse" data-bs-target="#dbrestore-debug"
			aria-expanded="false" aria-controls="dbrestore-debug"
	>
		<?= $text->text('DATABASE_RESTORE_COMMON_LBL_SHOWHIDE') ?>
	</button>
</h4>

<div id="dbrestore-debug" class="collapse">
	<p>
		<?= $text->text('DATABASE_RESTORE_COMMON_LBL_PLSINCLUDE') ?>
	</p>

	<?php
	$exc = $this->exception;
	do {
		?>
		<h5>
			<?= $exc->getMessage() ?>
		</h5>
		<p>
			<?= $exc->getFile() ?>:<?= $exc->getLine() ?>
		</p>
		<pre><?= $exc->getTraceAsString() ?></pre>
		<?php
	} while ($exc = $exc->getPrevious()) ?>
</div>
