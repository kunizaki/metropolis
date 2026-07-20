<?php

/**
* @package mod_spproperty_agents
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

$user = Factory::getUser();

?>

<div id="sp-property-agents<?php echo $module->id; ?>" class="spproperty <?php echo $params->get('moduleclass_sfx') ?>">
    <?php if (!empty($agents)) { ?>
        <?php foreach (array_chunk($agents, $columns) as $agents) { ?>
            <div class="row">
                <?php foreach ($agents as $key => $agent) { ?>
                    <div class="spproperty-col-sm-<?php echo round(12 / $columns); ?>">
                        <div class="spproperty-agent-widget">
                            <?php if (isset($agent->image) && $agent->image) { ?>
                                <div class="agent-img">
                                    <img alt="<?php echo $agent->title; ?>" src="<?php  echo $agent->thumb; ?>">
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
                                    <?php echo HTMLHelper::_('string.truncate', strip_tags($agent->description), 80); ?>
                                </div>
                            <?php } ?>

                            <?php if (isset($agent->phone) || isset($agent->mobile) || isset($agent->email)) { ?>
                                <ul class="spproperty-agent-mailing">
                                    <?php if (isset($agent->mobile)) { ?>
                                        <li>
                                            <i class="fa fa-phone-square" aria-hidden="true"></i>
                                            <a href="tel:<?php echo $agent->mobile; ?>">
                                            <span><?php echo $agent->mobile; ?></span>
                                            </a>
                                        </li>
                                    <?php } if (isset($agent->skype)) { ?>
                                        <li>
                                            <i class="fa fa-skype" aria-hidden="true"></i>
                                            <a href="skype:<?php echo $agent->skype; ?>?chat">
                                            <span><?php echo $agent->skype; ?></span>
                                            </a>
                                        </li>
                                    <?php } if (isset($agent->email)) { ?>
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
                                    <?php if (isset($agent->facebook)) { ?>
                                        <li>
                                            <a href="<?php echo $agent->facebook; ?>" target="_blank" class="facebook">
                                                <i class="fa fa-facebook"></i>
                                            </a>
                                        </li>
                                    <?php } if (isset($agent->instagram)) { ?>
                                        <li>
                                            <a href="<?php echo $agent->instagram; ?>" target="_blank" class="instagram">
                                                <i class="fa fa-instagram"></i>
                                            </a>
                                        </li>
                                    <?php } if (isset($agent->twitter)) { ?>
                                        <li>
                                            <a href="<?php echo $agent->twitter; ?>" target="_blank" class="twitter">
                                                <i class="fa fa-twitter"></i>
                                            </a>
                                        </li>
                                    <?php } if (isset($agent->gplus)) { ?>
                                        <li>
                                            <a href="<?php echo $agent->gplus; ?>" target="_blank" class="gplus">
                                                <i class="fa fa-google-plus"></i>
                                            </a>
                                        </li>
                                    <?php } if (isset($agent->linkedin)) { ?>
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
                <?php } ?>
            </div>
        <?php } ?>
    <?php } else { ?>
    <?php } ?>
</div>