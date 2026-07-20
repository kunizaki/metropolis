<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

$doc = Factory::getDocument();
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
if (SppropertyHelper::getVersion() < 4) {
    HTMLHelper::_('formbehavior.chosen', 'select', null, array('disable_search_threshold' => 0));
}
$row_css_class = JVERSION < 4 ? 'row-fluid' : 'row';
$col_css_class = JVERSION < 4 ? 'span' : 'col-lg-';
$JHtmlTag = JVERSION < 4 ? 'bootstrap' : 'uitab';
?>

<form action="<?php echo Route::_('index.php?option=com_spproperty&view=form&layout=edit&id=' . (int) $this->item->id); ?>" name="adminForm" id="adminForm" method="post" class="form-validate">
    <?php if (SppropertyHelper::getVersion() < 4 && !empty($this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10" >
    <?php else : ?>
        <div id="j-main-container"></div>
    <?php endif; ?>
    <div class="form-horizontal">
        <div class="<?php echo $row_css_class; ?>">
            <div class="<?php echo $col_css_class; ?>12">
                <?php
                $options = array(
                    'useCookie' => true,
                    'active' => 'basic'
                );

                // Start Tabs
                echo '<div class="tabbable">';
                echo HTMLHelper::_($JHtmlTag . '.startTabSet', 'agent_tabs', $options);

                echo HTMLHelper::_($JHtmlTag . '.addTab', 'agent_tabs', 'basic', 'Basic *');
                echo $this->form->renderFieldset('basic');
                echo HTMLHelper::_($JHtmlTag . '.endTab');

                echo HTMLHelper::_($JHtmlTag . '.addTab', 'agent_tabs', 'social', 'Social');
                echo $this->form->renderFieldset('social');
                echo HTMLHelper::_($JHtmlTag . '.endTab');

                // End Tabs
                echo HTMLHelper::_($JHtmlTag . '.endTabSet');
                echo '</div>';
                ;?>
            </div>
        </div>
    </div>

    <input type="hidden" name="task" value="property.edit" />
    <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
