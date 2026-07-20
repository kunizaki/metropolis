<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var \Akeeba\BRS\View\Password\Html $this */
$text = $this->getContainer()->get('language');
?>

<form action="index.php?view=password&task=unlock"
      class="d-flex flex-column justify-content-center align-items-center m-0 p-0"
      method="POST" id="loginForm">

	<header class="mb-4 text-center">
		<h3 class="h2 text-center text-primary-emphasis">
			<?= $text->text('PASSWORD_LBL_HEADER') ?>
		</h3>
	</header>

	<div class="w-75 border rounded p-3 bg-light-subtle" id="loginArea">
		<div class="form-text text-center mb-3">
			<?= $text->text('PASSWORD_LBL_SELF_UNLOCK') ?>
		</div>

		<div class="form-floating mb-3">
			<input type="password" id="password" name="password" class="form-control"
				   placeholder="<?= $text->text('PASSWORD_LBL_PASSWORD') ?>" required
				   value="">
			<label for="password"><?= $text->text('PASSWORD_LBL_PASSWORD') ?></label>
		</div>

		<button type="submit" class="w-100 btn btn-primary btn-lg"
				id="btnLoginSubmit">
			<span class="fa fa-user-check me-1" aria-hidden="true"></span>
			<?= $text->text('PASSWORD_LBL_UNLOCK_BTN') ?>
		</button>
	</div>

</form>
