<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
if (JVERSION < 4) {
    HTMLHelper::_('formbehavior.chosen', 'select');
}
$doc = Factory::getDocument();


$input = Factory::getApplication()->input;
$published = (($this->item->id != 0) && ($this->item->published == 1)) ? true : false;
$title = ($this->item->id != 0) ? Text::_('COM_SPPROPERTY_EDIT_PROFILE') . ' (' . $this->item->title . ') ' : Text::_('COM_SPPROPERTY_APPLY_FOR_AGENT');
HTMLHelper::_('jquery.token');

?>
<input type="hidden" class="agent-token" value="<?php echo Session::getFormToken(); ?>" />
<?php echo LayoutHelper::render('messages'); ?>
<div class="container">
    <div class="row justify-content-center apply-as-agent-wrap">
        <div class="col-sm-8">
            <div class="edit">
                <h3 class="th-form-title"><?php echo $title; ?></h3>
                <form action="<?php echo Route::_('index.php?option=com_spproperty&id=' . (int) $this->item->id); ?>" method="POST" name="adminForm" id="adminForm" class="form-validate form-vertical">

                    <?php
                        $options = array(
                            'useCookie' => true,
                            'active' => 'basic'
                        );

                        // Start Tabs
                        echo '<div class="tabbable">';
                        echo HTMLHelper::_('bootstrap.startTabSet', 'agent_tabs', $options);

                        echo HTMLHelper::_('bootstrap.addTab', 'agent_tabs', 'basic', Text::_('COM_SPPROPERTY_TAB_BASIC_STAR'));
                        echo $this->form->renderFieldset('basic');
                        echo HTMLHelper::_('bootstrap.endTab');

                        echo HTMLHelper::_('bootstrap.addTab', 'agent_tabs', 'social', Text::_('COM_SPPROPERTY_TAB_SOCIAL'));
                        echo $this->form->renderFieldset('social');
                        echo HTMLHelper::_('bootstrap.endTab');

                        // End Tabs
                        echo HTMLHelper::_('bootstrap.endTabSet');
                        echo '</div>';
                        ;?>

                    <div class="fieldset-wrap">
                        <input type="hidden" name="task" value="" />
                        <input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
                        <?php echo HTMLHelper::_('form.token'); ?>
                        <div class="button-group">
                            <button type="button" class="btn btn-success btn-md" onclick="Joomla.submitbutton('form.save')">
                                <span class="ico ico-check"></span> <?php echo ($this->item->id) ? Text::_('COM_SPPROPERTY_SAVE_CHANGES') : Text::_('COM_SPPROPERTY_APPLY_FOR_AGENT'); ?>
                            </button>
                            <button type="button" class="btn btn-warning btn-md ml-2" onclick="Joomla.submitbutton('form.cancel')">
                                <span class="ico ico-close"></span> <?php echo Text::_('JCANCEL') ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
