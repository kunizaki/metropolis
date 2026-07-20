<?php

/**
* @package mod_spproperty_categories
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;

$user = Factory::getUser();

?>

<div id="sp-property-categories<?php echo $module->id; ?>" class="spproperty <?php echo $params->get('moduleclass_sfx') ?>">
    <?php if (!empty($categories)) { ?>
        <div class="spproperty-category-box">
            <div class="spproperty-cateogry-box-body">
                <div class="spproperty-list">
                    <?php foreach ($categories as $category) { ?>
                        <div class="item">
                            <a href="<?php echo $category->url; ?>">
                                <?php echo $category->title; ?>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>
            
        </div>
    <?php } else { ?>
    <?php } ?>
</div>