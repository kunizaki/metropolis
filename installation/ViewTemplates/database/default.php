<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var \Akeeba\BRS\View\Database\Html $this */

$text = $this->getContainer()->get('language');
$pageHeading = $this->key === 'site'
	? $text->text('DATABASE_LBL_HEADER_MAINDB')
	: $text->sprintf('DATABASE_LBL_HEADER_OTHERDB', $this->key);
?>

<h2 class="fs-3 mb-4 p-0 border-bottom border-primary text-primary-emphasis">
	<?= $pageHeading ?>
</h2>

<div id="restoration-dialog" class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
	 aria-labelledby="restoration-dialog-head" aria-hidden="false"
>
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 85%">
		<div class="modal-content">
			<div class="modal-header">
				<h3 id="restoration-dialog-head" class="modal-title fs-4">
					<?= $text->text('DATABASE_HEADER_DBRESTORE') ?>
				</h3>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= $text->text('GENERAL_LBL_CLOSE') ?>"></button>
			</div>
			<div class="modal-body">
				<div id="restoration-progress">
					<div id="restoration-progress-wrap" class="progress my-3" role="progressbar"
						 aria-label="<?= $text->text('DATABASE_LBL_RESTORATION_PROGRESS') ?>"
						 aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"
						 style="height: 2em">
						<div class="progress-bar progress-bar-striped progress-bar-animated" id="restoration-progress-bar" style="width: 25%">25%</div>
					</div>

					<table class="table table-striped w-100">
						<tbody>
						<tr>
							<td class="w-50"><?= $text->text('DATABASE_LBL_RESTORED') ?></td>
							<td>
								<span id="restoration-lbl-restored"></span>
							</td>
						</tr>
						<tr>
							<td><?= $text->text('DATABASE_LBL_TOTAL') ?></td>
							<td>
								<span id="restoration-lbl-total"></span>
							</td>
						</tr>
						<tr>
							<td><?= $text->text('DATABASE_LBL_ETA') ?></td>
							<td>
								<span id="restoration-lbl-eta"></span>
							</td>
						</tr>
						</tbody>
					</table>

					<div class="alert alert-warning" id="restoration-warnings">
						<h4 class="alert-heading mb-2"><?= $text->text('DATABASE_HEADER_INPROGRESS_WARNINGS') ?></h4>
						<p class="mb-0">
							<?= $text->text('DATABASE_MSG_INPROGRESS_WARNINGS') ?>
							<br />
							<code id="restoration-inprogress-log"></code>
						</p>
					</div>
				</div>
				<div id="restoration-success">
					<div class="alert alert-success" id="restoration-success-nowarnings">
						<h4 class="alert-heading mb-0"><?= $text->text('DATABASE_HEADER_SUCCESS') ?></h4>
					</div>
					<div class="alert alert-warning" id="restoration-success-warnings">
						<h4 class="alert-heading"><?= $text->text('DATABASE_HEADER_WARNINGS') ?></h4>
						<p class="mb-0">
							<?= $text->text('DATABASE_MSG_WARNINGS') ?>
							<br />
							<code id="restoration-sql-log"></code>
						</p>
					</div>
					<p>
						<?= $text->text('DATABASE_MSG_SUCCESS') ?>
					</p>
					<button type="button" class="btn btn-success btn-lg" id="doneAndNext">
						<span class="fa fa-chevron-right me-2"></span>
						<?= $text->text('DATABASE_BTN_SUCCESS') ?>
					</button>
				</div>
				<div id="restoration-error">
					<div class="alert alert-danger">
						<?= $text->text('DATABASE_HEADER_ERROR') ?>
					</div>
					<div class="border rounded-2 p-2 bg-light-subtle mb-1" id="restoration-lbl-error"></div>
				</div>
				<div id="restoration-retry">
					<div class="alert alert-warning">
						<h4 class="alert-heading">
							<?= $text->text('DATABASE_HEADER_RESTORERETRY')?>
						</h4>
						<div id="retryframe">
							<p><?= $text->text('DATABASE_LBL_RESTOREFAILEDRETRY') ?></p>
							<p>
								<strong>
									<?= $text->text('DATABASE_LBL_WILLRETRY')?>
									<span id="akeeba-retry-timeout">0</span>
									<?= $text->text('DATABASE_LBL_WILLRETRYSECONDS')?>
								</strong>
							</p>
							<p class="d-flex flex-row gap-3">
								<button type="button" class="btn btn-danger" id="restoration-cancel-resume">
									<span class="fa fa-xmark" aria-hidden="true"></span>
									<?= $text->text('DATABASE_LBL_CANCEL')?>
								</button>
								<button type="button" class="btn btn-success" id="restoration-resume">
									<span class="fa fa-retweet" aria-hidden="true"></span>
									<?= $text->text('DATABASE_LBL_BTNRESUME')?>
								</button>
							</p>

							<p><?= $text->text('DATABASE_LBL_LASTERRORMESSAGEWAS')?></p>
							<p id="restoration-error-message-retry"></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<form class="row row-cols-2" id="databaseSettingsContainer">
	<div class="col">
		<div class="card border-primary">
			<h3 class="card-header bg-primary text-white fs-5">
				<?= $text->text('DATABASE_HEADER_CONNECTION') ?>
			</h3>
			<div class="card-body">
				<?php if ($this->large_tables):?>
					<!-- Packet too large warning -->
					<div class="alert alert-warning">
						<?= $text->sprintf('DATABASE_WARN_LARGE_COLUMNS', $this->large_tables, $this->recommendedPacketSize, $this->maxPacketSize)?>
					</div>
				<?php endif;?>

				<div class="row mb-3">
					<label for="dbtype" class="col-sm-3 col-form-label">
						<?= $text->text('DATABASE_LBL_TYPE') ?>
					</label>
					<div class="col-sm-9">
						<?= $this->selectHelper->dbtype($this->db->dbtype, $this->db->dbtech) ?>
					</div>
				</div>

				<div class="row mb-3">
					<label for="dbhost" class="col-sm-3 col-form-label">
						<?= $text->text('DATABASE_LBL_HOSTNAME') ?>
					</label>
					<div class="col-sm-9">
						<input type="text" id="dbhost" name="dbhost"
							   class="form-control"
							   value="<?= htmlentities($this->db->dbhost) ?>" />
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_HOSTNAME_HELP') ?>
						</span>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="dbuser">
						<?= $text->text('DATABASE_LBL_USERNAME') ?>
					</label>
					<div class="col-sm-9">
						<input type="text" id="dbuser" name="dbuser"
							   class="form-control"
							   value="<?= $this->db->dbuser ?>" />
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_USERNAME_HELP') ?>
						</span>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="dbpass">
						<?= $text->text('DATABASE_LBL_PASSWORD') ?>
					</label>
					<div class="col-sm-9">
						<input type="password" id="dbpass" name="dbpass"
							   class="form-control"
							   value="<?= $this->db->dbpass ?>" />
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_PASSWORD_HELP') ?>
						</span>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="dbname">
						<?= $text->text('DATABASE_LBL_DBNAME') ?>
					</label>
					<div class="col-sm-9">
						<input type="text" id="dbname" name="dbname"
							   class="form-control"
							   value="<?= $this->db->dbname ?>" />
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_DBNAME_HELP') ?>
						</span>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="prefix">
						<?= $text->text('DATABASE_LBL_PREFIX') ?>
					</label>
					<div class="col-sm-9">
						<input type="text" id="prefix" name="prefix"
							   class="form-control"
							   value="<?= $this->db->prefix ?>" />
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_PREFIX_HELP') ?>
						</span>
					</div>
				</div>

				<?php if (defined('BRS_DB_ALLOW_PORT_SOCKET') || defined('BRS_DB_ALLOW_SSL')): ?>

					<hr />

					<h3 class="fs-5"><?= $text->text('DATABASE_LBL_UNCOMMON_OPTIONS_HEADER') ?></h3>

					<div class="small text-info">
						<?= $text->text('DATABASE_LBL_UNCOMMON_OPTIONS_INFO') ?>
					</div>

					<?php if (defined('BRS_DB_ALLOW_PORT_SOCKET')): ?>
						<div class="row mb-3">
							<label class="col-sm-3 col-form-label" for="dbport">
								<?= $text->text('DATABASE_LBL_PORT') ?>
							</label>
							<div class="col-sm-9">
								<input type="text" id="dbport" name="dbport"
									   class="form-control"
									   value="<?= $this->db->dbport ?>" />
								<span class="form-text d-none">
								<?= $text->text('DATABASE_LBL_PORT_HELP') ?>
								</span>
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-sm-3 col-form-label" for="dbsocket">
								<?= $text->text('DATABASE_LBL_SOCKET') ?>
							</label>
							<div class="col-sm-9">
								<input type="text" id="dbsocket" name="dbsocket"
									   class="form-control"
									   value="<?= $this->db->dbsocket ?>" />
								<span class="form-text d-none">
								<?= $text->text('DATABASE_LBL_SOCKET_HELP') ?>
								</span>
							</div>
						</div>
					<?php endif ?>

					<?php if (defined('BRS_DB_ALLOW_SSL')): ?>
						<div class="row mb-3">
							<div class="col-sm-9 offset-sm-3">
								<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" role="switch"
										   id="dbencryption" name="dbencryption" value="1"
										<?= $this->db->dbencryption ? 'checked="checked"' : '' ?>>
									<label class="form-check-label" for="dbencryption">
										<?= $text->text('DATABASE_LBL_DBENCRYPTION') ?>
									</label>
								</div>
								<span class="form-text d-none">
									<?= $text->text('DATABASE_LBL_DBENCRYPTION_HELP') ?>
								</span>
							</div>
						</div>

						<div class="row mb-3" <?= $this->showOn('dbencryption:1') ?>>
							<label class="col-sm-3 col-form-label" for="dbsslcipher">
								<?= $text->text('DATABASE_LBL_DBSSLCIPHER') ?>
							</label>

							<div class="col-sm-9">
								<input type="text" id="dbsslcipher" name="dbsslcipher"
									   class="form-control"
									   value="<?= $this->db->dbsslcipher ?>" />
								<span class="form-text d-none">
									<?= $text->text('DATABASE_LBL_DBSSLCIPHER_HELP') ?>
								</span>
							</div>
						</div>

						<div class="row mb-3" <?= $this->showOn('dbencryption:1') ?>>
							<label class="col-sm-3 col-form-label" for="dbsslcert">
								<?= $text->text('DATABASE_LBL_DBSSLCERT') ?>
							</label>
							<div class="col-sm-9">
								<input type="text" id="dbsslcert" name="dbsslcert"
									   class="form-control"
									   value="<?= $this->db->dbsslcert ?>" />
								<span class="form-text d-none">
									<?= $text->text('DATABASE_LBL_DBSSLCERT_HELP') ?>
								</span>
							</div>
						</div>

						<div class="row mb-3" <?= $this->showOn('dbencryption:1') ?>>
							<label class="col-sm-3 col-form-label" for="dbsslkey">
								<?= $text->text('DATABASE_LBL_DBSSLKEY') ?>
							</label>
							<div class="col-sm-9">
								<input type="text" id="dbsslkey" name="dbsslkey"
									   class="form-control"
									   value="<?= $this->db->dbsslkey ?>" />
								<span class="form-text d-none">
									<?= $text->text('DATABASE_LBL_DBSSLKEY_HELP') ?>
								</span>
							</div>
						</div>

						<div class="row mb-3" <?= $this->showOn('dbencryption:1') ?>>
							<label class="col-sm-3 col-form-label" for="dbsslca">
								<?= $text->text('DATABASE_LBL_DBSSLCA') ?>
							</label>
							<div class="col-sm-9">
								<input type="text" id="dbsslca" name="dbsslca"
									   class="form-control"
									   value="<?= $this->db->dbsslca ?>" />
								<span class="form-text d-none">
									<?= $text->text('DATABASE_LBL_DBSSLCA_HELP') ?>
								</span>
							</div>
						</div>

						<div class="row mb-3" <?= $this->showOn('dbencryption:1') ?>>
							<div class="col-sm-9 offset-sm-3">
								<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" role="switch"
										   id="dbsslverifyservercert" name="dbsslverifyservercert" value="1"
										<?= $this->db->dbsslverifyservercert ? 'checked="checked"' : '' ?>>
									<label class="form-check-label" for="dbsslverifyservercert">
										<?= $text->text('DATABASE_LBL_DBSSLVERIFYSERVERCERT') ?>
									</label>
								</div>
								<span class="form-text d-none">
									<?= $text->text('DATABASE_LBL_DBSSLVERIFYSERVERCERT_HELP') ?>
								</span>
							</div>
						</div>
					<?php endif ?>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="col">
		<div class="card border-dark">
			<h3 class="card-header bg-dark text-white fs-5">
				<?= $text->text('DATABASE_HEADER_ADVANCED') ?>
			</h3>
			<div class="card-body">
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="existing">
						<?= $text->text('DATABASE_LBL_EXISTING') ?>
					</label>

					<div class="col-sm-9">
						<div class="akeeba-toggle">
							<select name="existing" id="existing" class="form-select">
								<option id="existing-drop" value="drop" <?= ($this->db->existing == 'drop') ? 'selected="selected"' : '' ?>>
									<?= $text->text('DATABASE_LBL_EXISTING_DROP') ?>
								</option>
								<option id="existing-backup" value="backup" <?= ($this->db->existing == 'backup') ? 'selected="selected"' : '' ?>>
									<?= $text->text('DATABASE_LBL_EXISTING_BACKUP') ?>
								</option>
								<option id="existing-dropprefix" value="dropprefix" <?= ($this->db->existing == 'dropprefix') ? 'selected="selected"' : '' ?>>
									<?= $text->text('DATABASE_LBL_EXISTING_DROPPREFIX') ?>
								</option>
								<option id="existing-dropall" value="dropall" <?= ($this->db->existing == 'dropall') ? 'selected="selected"' : '' ?>>
									<?= $text->text('DATABASE_LBL_EXISTING_DROPALL') ?>
								</option>
							</select>
						</div>
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_EXISTING_HELP') ?>
						</span>
					</div>
				</div>

				<?php if ($this->table_list): ?>
				<div class="row mb-3">
					<div class="col-sm-9 offset-sm-3">
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch"
								   id="specific_tables_cbx" name="specific_tables_cbx" value="1">
							<label class="form-check-label" for="specific_tables_cbx">
								<?= $text->text('DATABASE_LBL_SPECIFICTABLES_LBL') ?>
							</label>
						</div>
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_SPECIFICTABLES_HELP') ?>
						</span>
					</div>
				</div>

				<div class="row mb-3" id="specific_tables_holder" <?= $this->showOn('specific_tables_cbx:1') ?>>
					<div class="col-sm-9 offset-sm-3" id="specific_tables_holder">
						<p class="d-flex flex-row justify-content-evenly gap-2">
							<button type="button" id="specific_tables_addall"
									class="btn btn-sm btn-outline-primary">
								<?= $text->text('DATABASE_LBL_SPECIFICTABLES_ADD_ALL')?>
							</button>

							<button type="button" id="specific_tables_clearall"
									class="btn btn-sm btn-outline-dark">
								<?= $text->text('DATABASE_LBL_SPECIFICTABLES_CLEAR_ALL')?>
							</button>
						</p>
						<?= $this->table_list ?>
					</div>
				</div>
				<?php endif; ?>

				<div class="row mb-3">
					<div class="col-sm-9 offset-sm-3">
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch"
								   id="foreignkey" name="foreignkey" value="1"
								<?= $this->db->foreignkey ? 'checked="checked"' : '' ?>>
							<label class="form-check-label" for="foreignkey">
								<?= $text->text('DATABASE_LBL_FOREIGNKEY') ?>
							</label>
						</div>
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_FOREIGNKEY_HELP') ?>
						</span>
					</div>
				</div>

				<div class="row mb-3">
					<div class="col-sm-9 offset-sm-3">
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch"
								   id="noautovalue" name="noautovalue" value="1"
								<?= $this->db->noautovalue ? 'checked="checked"' : '' ?>>
							<label class="form-check-label" for="noautovalue">
								<?= $text->text('DATABASE_LBL_NOAUTOVALUE') ?>
							</label>
						</div>
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_NOAUTOVALUE_HELP') ?>
						</span>
					</div>
				</div>

				<div class="row mb-3">
					<div class="col-sm-9 offset-sm-3">
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch"
								   id="replace" name="replace" value="1"
								<?= $this->db->replace ? 'checked="checked"' : '' ?>>
							<label class="form-check-label" for="replace">
								<?= $text->text('DATABASE_LBL_REPLACE') ?>
							</label>
						</div>
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_REPLACE_HELP') ?>
						</span>
					</div>
				</div>

				<div class="row mb-3">
					<div class="col-sm-9 offset-sm-3">
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch"
								   id="utf8db" name="utf8db" value="1"
								<?= $this->db->utf8db ? 'checked="checked"' : '' ?>>
							<label class="form-check-label" for="utf8db">
								<?= $text->text('DATABASE_LBL_FORCEUTF8DB') ?>
							</label>
						</div>
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_FORCEUTF8DB_HELP') ?>
						</span>
					</div>
				</div>

				<div class="row mb-3">
					<div class="col-sm-9 offset-sm-3">
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch"
								   id="utf8tables" name="utf8tables" value="1"
								<?= $this->db->utf8tables ? 'checked="checked"' : '' ?>>
							<label class="form-check-label" for="utf8tables">
								<?= $text->text('DATABASE_LBL_FORCEUTF8TABLES') ?>
							</label>
						</div>
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_FORCEUTF8TABLES_HELP') ?>
						</span>
					</div>
				</div>

				<div class="row mb-3">
					<div class="col-sm-9 offset-sm-3">
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch"
								   id="utf8mb4" name="utf8mb4" value="1"
								<?= $this->db->utf8mb4 ? 'checked="checked"' : '' ?>>
							<label class="form-check-label" for="utf8mb4">
								<?= $text->text('DATABASE_LBL_UTF8MB4DETECT') ?>
							</label>
						</div>
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_UTF8MB4DETECT_HELP') ?>
						</span>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="charset_conversion">
						<?= $text->text('DATABASE_LBL_CHARSET_CONVERSION') ?>
					</label>
					<div class="col-sm-9">
						<select name="charset_conversion" id="charset_conversion" class="form-select">
							<option value="yes" <?= $this->db->charset_conversion === 'yes' ? 'selected' : '' ?>>
								<?= $text->text('DATABASE_LBL_CHARSET_CONVERSION_YES') ?>
							</option>
							<option value="auto" <?= (!in_array($this->db->charset_conversion, ['yes', 'no'], true)) ? 'selected' : '' ?>>
								<?= $text->text('DATABASE_LBL_CHARSET_CONVERSION_AUTO') ?>
							</option>
							<option value="no" <?= $this->db->charset_conversion === 'no' ? 'selected' : '' ?>>
								<?= $text->text('DATABASE_LBL_CHARSET_CONVERSION_NO') ?>
							</option>
						</select>
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_CHARSET_CONVERSION_HELP') ?>
						</span>
					</div>
				</div>

				<div class="row mb-3">
					<div class="col-sm-9 offset-sm-3">
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch"
								   id="break_on_failed_create" name="break_on_failed_create" value="1"
								<?= $this->db->break_on_failed_create ? 'checked="checked"' : '' ?>>
							<label class="form-check-label" for="break_on_failed_create">
								<?= $text->text('DATABASE_LBL_ON_CREATE_ERROR') ?>
							</label>
						</div>
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_ON_CREATE_ERROR_HELP') ?>
						</span>
					</div>
				</div>

				<div class="row mb-3">
					<div class="col-sm-9 offset-sm-3">
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch"
								   id="break_on_failed_insert" name="break_on_failed_insert" value="1"
								<?= $this->db->break_on_failed_insert ? 'checked="checked"' : '' ?>>
							<label class="form-check-label" for="break_on_failed_insert">
								<?= $text->text('DATABASE_LBL_ON_OTHER_ERROR') ?>
							</label>
						</div>
						<span class="form-text d-none">
							<?= $text->text('DATABASE_LBL_ON_OTHER_ERROR_HELP') ?>
						</span>
					</div>
				</div>

				<hr>
				<h4><?= $text->text('DATABASE_HEADER_FINETUNING') ?></h4>

				<div class="small text-info mb-3">
					<?= $text->text('DATABASE_MSG_FINETUNING') ?>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="maxexectime">
						<?= $text->text('DATABASE_LBL_MAXEXECTIME') ?>
					</label>
					<div class="col-sm-9">
						<div class="input-group">
							<input class="form-control" type="text" id="maxexectime" name="maxexectime"
								   placeholder="<?= $text->text('DATABASE_LBL_MAXEXECTIME') ?>"
								   value="<?= $this->db->maxexectime ?>" />
							<span class="input-group-text">sec</span>
						</div>
						<span class="akeeba-help-text" style="display: none;">
							<?= $text->text('DATABASE_LBL_MAXEXECTIME_HELP') ?>
						</span>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="throttle">
						<?= $text->text('DATABASE_LBL_THROTTLEMSEC') ?>
					</label>
					<div class="col-sm-9">
						<div class="input-group">
							<input class="form-control" type="text" id="throttle" name="throttle"
								   placeholder="<?= $text->text('DATABASE_LBL_THROTTLEMSEC') ?>" value="<?= $this->db->throttle ?>" />
							<span class="input-group-text">msec</span>
						</div>
						<span class="akeeba-help-text" style="display: none;">
							<?= $text->text('DATABASE_LBL_THROTTLEMSEC_HELP') ?>
						</span>
					</div>
				</div>

			</div>
		</div>
	</div>
</form>
