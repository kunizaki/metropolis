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

$text    = $this->getContainer()->get('language');
$select  = new Select($this->getContainer());
$noSuper = count($this->stateVars->superusers ?? []) === 0;
?>
<form name="setupForm" id="setupForm"
      action="index.php" method="post" class="container">

	<input type="hidden" name="view" value="setup" />
	<input type="hidden" name="task" value="apply" />

	<?php if ($noSuper): ?>
	<?= $this->loadTemplate('nosa') ?>
	<?php else: ?>
	<div class="row row-cols-1 row-cols-md-2">
		<div class="col">
			<!-- Site parameters -->
			<?= $this->loadTemplate('params') ?>
		</div>

		<div class="col">
			<!-- Server config files handling -->
			<?= $this->loadTemplate('serverconfig') ?>

			<!-- Directories fine-tuning -->
			<?= $this->loadTemplate('dirs') ?>
		</div>

		<div class="col">
			<?php if (count($this->stateVars->superusers ?? [])): ?>
			<!-- Super User settings -->
			<?= $this->loadTemplate('superuser') ?>
			<?php endif; ?>
		</div>

		<?php if ($this->hasFTP): ?>
		<!-- FTP options -->
		<div class="col">
			<?= $this->loadTemplate('ftp') ?>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>
</form>