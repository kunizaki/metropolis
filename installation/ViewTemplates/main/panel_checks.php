<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/**
 * @var \Akeeba\BRS\View\Main\Html                                              $this
 * @var array<\Akeeba\BRS\Framework\RestorationCheck\RestorationCheckInterface> $checks
 */
$text = $this->getContainer()->get('language');
?>
<table class="table table-striped">
	<thead>
	<tr>
		<th scope="col" class="w-100">
			<?= $text->text('MAIN_LBL_CHECK_NAME') ?>
		</th>
		<th scope="col">
			<?= $text->text('MAIN_LBL_CHECK_RECOMMENDED') ?>
		</th>
		<th scope="col">
			<?= $text->text('MAIN_LBL_CHECK_ACTUAL') ?>
		</th>
		<th scope="col">
			<?= $text->text('MAIN_LBL_CHECK_STATUS') ?>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($checks as $check): ?>
		<tr>
			<th scope="row">
				<div class="<?= $check->isValid() ? '' : 'text-danger' ?>">
					<?= strpos($check->getName(), ' ') === false ? $text->text($check->getName()) : $check->getName() ?>
				</div>
				<?php if ($check->getNotice()): ?>
				<div class="fw-light small text-muted">
					<?= $check->getNotice() ?>
				</div>
				<?php endif; ?>
			</th>
			<td class="text-center">
				<?php if($check->getExpected()): ?>
					<span class="fa fa-check" aria-hidden="true"></span>
					<span class="visually-hidden"><?= $text->text('GENERAL_LBL_YES') ?></span>
				<?php else: ?>
					<span class="fa fa-xmark" aria-hidden="true"></span>
					<span class="visually-hidden"><?= $text->text('GENERAL_LBL_NO') ?></span>
				<?php endif; ?>
			</td>
			<td class="text-center">
				<?php if($check->getCurrentValue()): ?>
					<span class="fa fa-check" aria-hidden="true"></span>
					<span class="visually-hidden"><?= $text->text('GENERAL_LBL_YES') ?></span>
				<?php else: ?>
					<span class="fa fa-xmark" aria-hidden="true"></span>
					<span class="visually-hidden"><?= $text->text('GENERAL_LBL_NO') ?></span>
				<?php endif; ?>
			</td>
			<td class="text-center">
				<?php $class = $check->isValid() ? 'text-success' : 'text-danger' ?>
				<?php if($check->isValid()): ?>
					<span class="fa fa-check-circle <?= $class ?>" aria-hidden="true"></span>
					<span class="visually-hidden"><?= $text->text('MAIN_LBL_CHECK_VALID') ?></span>
				<?php else: ?>
					<span class="fa fa-xmark-circle <?= $class ?>" aria-hidden="true"></span>
					<span class="visually-hidden"><?= $text->text('MAIN_LBL_CHECK_INVALID') ?></span>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>