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
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Model\AdminModel;

$user = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$canOrder = $user->authorise('core.edit.state', 'com_spproperty');
$saveOrder = ($listOrder == 'a.ordering');
$saveOrderingUrl = '';
if ($saveOrder && !empty($this->items)) {
    if (SppropertyHelper::getVersion() < 4) {
        $saveOrderingUrl = 'index.php?option=com_spproperty&task=visitrequests.saveOrderAjax&tmpl=component';
        HTMLHelper::_('sortablelist.sortable', 'visitrequestList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
    } else {
        $saveOrderingUrl = 'index.php?option=com_spproperty&task=visitrequests.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
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


<form action="<?php echo Route::_('index.php?option=com_spproperty&view=visitrequests'); ?>" method="POST" name="adminForm" id="adminForm">

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
        <table class="table table-striped" id="visitrequestList">
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
                        <?php echo HTMLHelper::_('grid.sort', 'COM_SPPROPERTY_FIELD_CUSTOMER_NAME', 'a.customer_name', $listDirn, $listOrder); ?>
                    </th>

                    <th>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_SPPROPERTY_REQUEST_TYPE', 'a.type', $listDirn, $listOrder); ?>
                    </th>

                    <th>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_SPPROPERTY_FIELD_CUSTOMER_EMAIL', 'a.customer_email', $listDirn, $listOrder); ?>
                    </th>

                    <th>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_SPPROPERTY_FIELD_CUSTOMER_PHONE', 'a.customer_phone', $listDirn, $listOrder); ?>
                    </th>

                    <th>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_SPPROPERTY_FIELD_PROPERTY', 'a.property_id', $listDirn, $listOrder); ?>
                    </th>

                    <th>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_SPPROPERTY_FIELD_REQUEST_USER', 'a.userid', $listDirn, $listOrder); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_SPPROPERTY_FIELD_CREATED_ON', 'a.created', $listDirn, $listOrder); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_SPPROPERTY_FIELD_CUSTOMER_IP', 'a.visitor_ip', $listDirn, $listOrder); ?>
                    </th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="12">
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
                    $propertyModel = AdminModel::getInstance('Property', 'SppropertyModel');
                    $agentid = SppropertyHelper::userAgentId($user->id);
                    $canViewRow = ($propertyModel->getItem($item->property_id)->agent_id == $agentid);
                    ?>
                    <?php if (in_array(SppropertyHelper::getUserGroupId('Super Users'), Access::getGroupsByUser($user->id)) || $canViewRow) { ?>
                        <?php if (SppropertyHelper::getVersion() < 4) :?>
                    <tr>
                        <?php else : ?>
                    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="1">
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
                            <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'visitrequests.', $canEdit, 'cb');?>
                        </td>

                        <td>
                            <?php if ($item->checked_out) : ?>
                                <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->created_by, $item->checked_out_time, 'visitrequests.', $canCheckin); ?>
                            <?php endif; ?>

                            <?php if ($canEdit || $canViewRow) : ?>
                                <a class="title" href="<?php echo Route::_('index.php?option=com_spproperty&task=visitrequest.edit&id=' . $item->id); ?>">
                                    <?php echo $this->escape($item->customer_name); ?>
                                </a>
                            <?php else : ?>
                                <?php echo $this->escape($item->customer_name); ?>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php echo (!empty($item->type) && $item->type == 'visit') ? 'Visit Request' : 'Price Request'; ?>
                            
                        </td>

                        <td>
                            <?php echo $item->customer_email; ?>
                        </td>

                        <td>
                            <?php echo $item->customer_phone; ?>
                        </td>

                        <td>
                            <a href="<?php echo Route::_('index.php?option=com_spproperty&task=property.edit&id=' . $item->property_id); ?>"><?php echo $item->property_title; ?></a>
                            
                        </td>

                        <td>
                            <?php echo $item->name; ?>
                        </td>

                        <td>
                            <?php echo date_format(new DateTime($item->created), 'd-m-Y'); ?>
                        </td>

                        <td>
                            <?php echo $item->visitor_ip; ?>
                        </td>
                    </tr>
                    <?php } ?>
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