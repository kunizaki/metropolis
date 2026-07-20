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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;

$agent = $displayData['agent'];
$desc_limit = ($displayData['desc_limit']) ? $displayData['desc_limit'] : false;
$params = ComponentHelper::getParams('com_spproperty');
$doc = Factory::getDocument();
if ($agent) { ?>
    <div class="row">
        <div class="spproperty-col-sm-12">
            <div class="spproperty-agent-widget">
                <?php if (isset($agent->image) && $agent->image) { ?>
                    <div class="agent-img">
                        <img alt="<?php echo $agent->title; ?>" src="<?php echo $agent->thumb; ?>">
                    </div>
                <?php } ?>

                <h3>
                    
                    <a href="<?php echo $agent->url; ?>">
                        <?php if (isset($agent->designation)) { ?>
                            <span><?php echo $agent->designation; ?></span>
                        <?php } ?>
                        <?php echo $agent->title; ?>
                    </a>
                </h3>

                <?php if (isset($agent->description)) { ?>
                    <div class="spproperty-agent-desc">
                        <?php if ($desc_limit == true) { ?>
                            <?php echo HTMLHelper::_('string.truncate', strip_tags($agent->description), 80); ?>
                        <?php } else { ?>
                            <?php if ($params->get('content_prepare', false)) { ?>
                                <?php echo HTMLHelper::_('content.prepare', $agent->description); ?>
                            <?php } else { ?>
                                <?php echo $agent->description; ?>
                            <?php } ?>
                        <?php } ?>
                    </div>
                <?php } ?>

                <?php if (isset($agent->phone) || isset($agent->mobile) || isset($agent->email)) { ?>
                    <ul class="spproperty-agent-mailing">
                        <?php if (isset($agent->mobile) && !empty($agent->mobile)) { ?>
                            <li>
                                <i class="fa fa-phone-square" aria-hidden="true"></i>
                                <a href="tel:<?php echo $agent->mobile; ?>">
                                <span><?php echo $agent->mobile; ?></span>
                                </a>
                            </li>
                        <?php } if (isset($agent->skype) && !empty($agent->skype)) { ?>
                            <li>
                                <i class="fa fa-skype" aria-hidden="true"></i>
                                <a href="skype:<?php echo $agent->skype; ?>?chat">
                                <span><?php echo $agent->skype; ?></span>
                                </a>
                            </li>
                        <?php } if (isset($agent->email) && !empty($agent->email)) { ?>
                            <li>
                                <i class="fa fa-envelope-square" aria-hidden="true"></i>
                                <a href="mailto:<?php echo $agent->email; ?>?subject=Request for visit">
                                <span><?php echo $agent->email; ?></span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
                <?php if (isset($agent->facebook) || isset($agent->instagram) || isset($agent->twitter) || isset($agent->gplus) || isset($agent->linkedin)) { ?>
                    <ul class="spproperty-agent-social">
                        <?php if (isset($agent->facebook) && !empty($agent->facebook)) { ?>
                            <li>
                                <a href="<?php echo $agent->facebook; ?>" target="_blank" class="facebook">
                                    <i class="fa fa-facebook"></i>
                                </a>
                            </li>
                        <?php } if (isset($agent->instagram) && !empty($agent->instagram)) { ?>
                            <li>
                                <a href="<?php echo $agent->instagram; ?>" target="_blank" class="instagram">
                                    <i class="fa fa-instagram"></i>
                                </a>
                            </li>
                        <?php } if (isset($agent->twitter) && !empty($agent->twitter)) { ?>
                            <li>
                                <a href="<?php echo $agent->twitter; ?>" target="_blank" class="twitter">
                                    <i class="fa fa-twitter"></i>
                                </a>
                            </li>
                        <?php } if (isset($agent->gplus) && !empty($agent->gplus)) { ?>
                            <li>
                                <a href="<?php echo $agent->gplus; ?>" target="_blank" class="gplus">
                                    <i class="fa fa-google-plus"></i>
                                </a>
                            </li>
                        <?php } if (isset($agent->linkedin) && !empty($agent->linkedin)) { ?>
                            <li>
                                <a href="<?php echo $agent->linkedin; ?>" target="_blank" class="linkedin">
                                    <i class="fa fa-linkedin"></i>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>

            </div> <!-- /.spproperty-agent-widget -->
        </div>
    </div>
    
<?php } ?>


