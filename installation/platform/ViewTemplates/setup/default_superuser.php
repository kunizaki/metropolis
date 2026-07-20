<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var \Akeeba\BRS\Platform\View\Setup\Html $this */

use Akeeba\BRS\Framework\Helper\Select;

$text     = $this->getContainer()->get('language');
$select   = new Select($this->getContainer());
$jVersion = $this->getContainer()->get('session')->get('jversion');
?>
<div class="card border-secondary mb-3">
	<h3 class="card-header bg-secondary text-white">
		<?= $text->text('SETUP_HEADER_SUPERUSERPARAMS') ?>
	</h3>

	<div class="card-body">
		<div class="row mb-3">
			<label for="superuserid" class="col-form-label col-sm-3">
				<?= $text->text('SETUP_LABEL_SUPERUSER') ?>
			</label>
			<div class="col-sm-9">
				<?= $select->superusers() ?>
				<div class="form-text d-none">
					<?= $text->text('SETUP_LABEL_SUPERUSER_HELP') ?>
				</div>
			</div>
		</div>

		<div class="row mb-3" id="superuseremail_container">
			<label for="superuseremail" class="col-form-label col-sm-3">
				<?= $text->text('SETUP_LABEL_SUPERUSEREMAIL') ?>
			</label>

			<div class="col-sm-9">
				<input type="text" id="superuseremail" name="superuseremail" class="form-control"
				       value="" />
				<div class="form-text d-none">
					<?= $text->text('SETUP_LABEL_SUPERUSEREMAIL_HELP') ?>
				</div>
			</div>
		</div>

		<div class="row mb-3" id="superuserpassword_container">
			<label for="superuserpassword" class="col-form-label col-sm-3">
				<?= $text->text('SETUP_LABEL_SUPERUSERPASSWORD') ?>
			</label>
			<div class="col-sm-9">
				<input type="password" id="superuserpassword" name="superuserpassword" class="form-control"
				       value="" />
				<div class="form-text d-none">
					<?= $text->text('SETUP_LABEL_SUPERUSERPASSWORD_HELP2') ?>
				</div>
			</div>
		</div>

		<div class="row mb-3" id="superuserpasswordrepeat_container">
			<label for="superuserpasswordrepeat" class="col-form-label col-sm-3">
				<?= $text->text('SETUP_LABEL_SUPERUSERPASSWORDREPEAT') ?>
			</label>
			<div class="col-sm-9">
				<input type="password" id="superuserpasswordrepeat" name="superuserpasswordrepeat"
				       class="form-control" value="" />
				<div class="form-text d-none">
					<?= $text->text('SETUP_LABEL_SUPERUSERPASSWORDREPEAT_HELP') ?>
				</div>
			</div>
		</div>
	</div>
</div>