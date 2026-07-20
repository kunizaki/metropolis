
<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Layout\LayoutHelper;

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

$user = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$canOrder = $user->authorise('core.edit.state', 'com_spproperty');
$saveOrder = ($listOrder == 'a.ordering');
$saveOrderingUrl = '';
if ($saveOrder && !empty($this->items)) {
    if (SppropertyHelper::getVersion() < 4) {
        $saveOrderingUrl = 'index.php?option=com_spproperty&task=propertyfeatures.saveOrderAjax&tmpl=component';
        HTMLHelper::_('sortablelist.sortable', 'propertyfeatureList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
    } else {
        $saveOrderingUrl = 'index.php?option=com_spproperty&task=propertyfeatures.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
        HTMLHelper::_('draggablelist.draggable');
    }
}
HTMLHelper::_('jquery.framework', false);
?>

<script type="text/javascript">
window.addEventListener('DOMContentLoaded', e => {
    Joomla.orderTable = function() {
        table = document.getElementById('sortTable');
        direction = document.getElementById('directionTable');
        order = table.options[table.selectedIndex].value;
        if (order != '<?php echo $listOrder; ?>') {
            dirn = 'asc';
        } else {
            dirn = direction.options[direction.selectedIndex].value;
        }
        Joomla.tableOrdering(order, dirn, '');
    }
});
</script>

<form action="<?php echo Route::_('index.php?option=com_spproperty&view=propertyfeatures'); ?>" method="POST" name="adminForm" id="adminForm">

    <?php if (SppropertyHelper::getVersion() < 4 && !empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>

    <div id="j-main-container" class="span10" >
    <?php else : ?>
            <div id="j-main-container"></div>
    <?php endif; ?>

    <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
    <div class="clearfix"></div>
    <?php if(empty($this->items)) : ?>
        <div class="alert alert-info">
            <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>
        <table class="table table-striped" id="propertyfeatureList">
            <thead>
                <tr>
                    <th class="nowrap center hidden-phone" width="1%">
                        <?php echo HTMLHelper::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
                    </th>

                    <th width="1%" class="hidden-phone">
                        <input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                    </th>

                    <th width="1%" class="nowrap center">
                        <?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                    </th>

                    <th>
                        <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                    </th>

                    <th>
                        <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_CREATED', 'a.created_by', $listDirn, $listOrder); ?>
                    </th>

                    <th>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_SPPROPERTY_FIELD_CREATED_ON', 'a.created', $listDirn, $listOrder); ?>
                    </th>

                    <th>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_SPPROPERTY_FIELD_LANGUAGE_LABEL', 'a.language', $listDirn, $listOrder); ?>
                    </th>
                    
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="10">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>

            <?php if (SppropertyHelper::getVersion() < 4) :?>
                <tbody>
            <?php else : ?>
                <tbody <?php if ($saveOrder) :
                    ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php
                       endif; ?>>
            <?php endif; ?>
                <?php foreach ($this->items as $i => $item) : ?>
                    <?php
                    $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
                    $canChange = $user->authorise('core.edit.state', 'com_spproperty') && $canCheckin;
                    $canEdit = $user->authorise('core.edit', 'com_spproperty') || ($user->authorise('core.edit.own', 'com_spproperty') && $item->created_by === $user->get('id'));
                    ?>

                    <?php if (SppropertyHelper::getVersion() < 4) :?>
                    <tr>
                    <?php else : ?>
                        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="1" data-draggable-group="1">
                    <?php endif; ?>
                        <td class="order nowrap center hidden-phone">
                            <?php if ($canChange) :
                                $disableClassName = '';
                                $disabledLabel = '';
                                if (!$saveOrder) :
                                    $disabledLabel = Text::_('JORDERINGDISABLED');
                                    $disableClassName = 'inactive tip-top';
                                endif;
                                ?>

                                <span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>" title="<?php echo $disabledLabel; ?>">
                                    <i class="icon-menu"></i>
                                </span>
                                <input type="text" style="display: none;" name="order[]" size="5" class="width-20 text-area-order " value="<?php echo $item->ordering; ?>" >
                            <?php else : ?>
                                <span class="sortable-handler inactive">
                                    <i class="icon-menu"></i>
                                </span>
                            <?php endif; ?>
                        </td>

                        <td class="center hidden-phone">
                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                        </td>

                        <td class="center">
                            <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'propertyfeatures.', $canEdit, 'cb');?>
                        </td>

                        <td>
                            <?php if ($item->checked_out) : ?>
                                <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->created_by, $item->checked_out_time, 'propertyfeatures.', $canCheckin); ?>
                            <?php endif; ?>

                            <?php if ($canEdit) : ?>
                                <a class="title" href="<?php echo Route::_('index.php?option=com_spproperty&task=propertyfeature.edit&id=' . $item->id); ?>">
                                    <?php echo $this->escape($item->title); ?>
                                </a>
                            <?php else : ?>
                                <?php echo $this->escape($item->title); ?>
                            <?php endif; ?>

                            <span class="small break-word">
                                <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                            </span>
                        </td>

                        <td>
                            <?php echo Factory::getUser($item->created_by)->get('username', $item->created_by);?>
                        </td>
                        
                        <td>
                            <?php echo date_format(new DateTime($item->created), 'd-m-Y'); ?>
                        </td>

                        <td>
                            <?php echo $item->language == '*' ? 'All' : $item->lang ; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>