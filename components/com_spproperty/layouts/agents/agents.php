<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$agents = $displayData['agents'];
$columns = $displayData['columns'];

if (count($agents)) { ?>
    <?php foreach (array_chunk($agents, $columns) as $agents) { ?>
        <div class="row">
            <?php foreach ($agents as $agent) { ?>
                <div class="spproperty-col-sm-<?php echo round(12 / $columns); ?>">
                    <?php echo LayoutHelper::render('agents.agent', array('agent' => $agent, 'desc_limit' => true)); ?>
                </div> <!-- /.col-sm- -->
            <?php } ?>
        </div> <!-- /.row -->
    <?php } ?>
    
<?php } else { ?>
    <div class="row">
        <div class="spproperty-col-sm-12 sp-no-item-found">
            <p><?php echo Text::_('COM_SPPROPERTY_NO_ITEMS_FOUND'); ?></p>
        </div>
    </div>
<?php } ?>

