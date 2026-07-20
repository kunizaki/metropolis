<?php

/**
 * @package Empire
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('resticted aceess');

SpAddonsConfig::addonConfig(
        array(
            'type' => 'repeatable',
            'addon_name' => 'sp_thumb_slider',
            'category' => 'empire',
            'title' => JText::_('COM_SPPAGEBUILDER_ADDON_THUMB_SLIDER'),
            'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_THUMB_SLIDER_DESC'),
            'inline'     => [
                'contenteditable' => true,
                'buttons'         => [
                ],
            ],
            'attr' => array(
                'general' => array(
                    'title' => array(
                        'type' => 'text',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_DESC'),
                        'std' => ''
                    ),
                    'heading_selector' => array(
                        'type' => 'select',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_DESC'),
                        'values' => array(
                            'h1' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H1'),
                            'h2' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H2'),
                            'h3' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H3'),
                            'h4' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H4'),
                            'h5' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H5'),
                            'h6' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H6'),
                        ),
                        'std' => 'h3',
                    ),
                    'title_text_color' => array(
                        'type' => 'color',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR_DESC'),
                    ),
                    'slide_height' => array(
                        'type' => 'number',
                        'title' => JText::_('COM_SPPAGEBUILDER_SLIDE_HEIGHT'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_SLIDE_HEIGHT_DESC'),
                        'std' => '700',
                        'placeholder' => '700',
                    ),
                    'autoplay' => array(
                        'type' => 'select',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_AUTOPLAY'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_AUTOPLAY_DESC'),
                        'values' => array(
                            1 => JText::_('JYES'),
                            0 => JText::_('JNO'),
                        ),
                        'std' => 1,
                    ),
                    'arrows' => array(
                        'type' => 'select',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_SHOW_ARROWS'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_SHOW_ARROWS_DESC'),
                        'values' => array(
                            1 => JText::_('JYES'),
                            0 => JText::_('JNO'),
                        ),
                        'std' => 1,
                    ),
                    'class' => array(
                        'type' => 'text',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
                        'std' => ''
                    ),
                    // Repeatable Items
                    'sp_thumb_slider_item' => array(
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_REPEATABLE_ITEMS'),
                        'attr' => array(
                            'title' => array(
                                'type' => 'textarea',
                                'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_ITEM_TITLE'),
                                'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_ITEM_TITLE_DESC'),
                                'std' => 'Carousel Item Title',
                            ),
                            'sub_title' => array(
                                'type' => 'textarea',
                                'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_ITEM_SUB_TITLE'),
                                'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_ITEM_SUB_TITLE_DESC'),
                                'std' => 'Carousel Item Sub Title',
                            ),
                            'button_text' => array(
                                'type' => 'text',
                                'title' => JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_TEXT'),
                                'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_TEXT_DESC'),
                            ),
                            'button_url' => array(
                                'type' => 'text',
                                'title' => JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_ONE_URL'),
                                'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_ONE_URL_DESC'),
                            ),
                            'image' => array(
                                'type' => 'media',
                                'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_ITEM_BACKGROUND_IMAGE'),
                                'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_ITEM_BACKGROUND_IMAGE_DESC'),
                            ),
                            'separator_title' => array(
                                'type' => 'separator',
                                'title' => JText::_('COM_SPPAGEBUILDER_SF_TITLE_ANIMATION'),
                            ),
                            'title_animation' => array(
                                'type' => 'animation',
                                'title' => JText::_('COM_SPPAGEBUILDER_COLUMN_ANIMATION'),
                                'desc' => JText::_('COM_SPPAGEBUILDER_COLUMN_ANIMATION_DESC'),
                            ),
                            'title_animationduration' => array(
                                'type' => 'number',
                                'title' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION'),
                                'desc' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION_DESC'),
                                'std' => '300',
                                'placeholder' => '300',
                            ),
                            'title_animationdelay' => array(
                                'type' => 'number',
                                'title' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY'),
                                'desc' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY_DESC'),
                                'std' => '0',
                                'placeholder' => '300',
                            ),
                            'separator_subtitle' => array(
                                'type' => 'separator',
                                'title' => JText::_('COM_SPPAGEBUILDER_SF_SUBTITLE_ANIMATION'),
                            ),
                            'subtitle_animation' => array(
                                'type' => 'animation',
                                'title' => JText::_('COM_SPPAGEBUILDER_COLUMN_ANIMATION'),
                                'desc' => JText::_('COM_SPPAGEBUILDER_COLUMN_ANIMATION_DESC'),
                            ),
                            'subtitle_animationduration' => array(
                                'type' => 'number',
                                'title' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION'),
                                'desc' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION_DESC'),
                                'std' => '300',
                                'placeholder' => '300',
                            ),
                            'subtitle_animationdelay' => array(
                                'type' => 'number',
                                'title' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY'),
                                'desc' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY_DESC'),
                                'std' => '0',
                                'placeholder' => '300',
                            ),
                            'separator_button' => array(
                                'type' => 'separator',
                                'title' => JText::_('COM_SPPAGEBUILDER_SF_button_ANIMATION'),
                            ),
                            'button_animation' => array(
                                'type' => 'animation',
                                'title' => JText::_('COM_SPPAGEBUILDER_COLUMN_ANIMATION'),
                                'desc' => JText::_('COM_SPPAGEBUILDER_COLUMN_ANIMATION_DESC'),
                            ),
                            'button_animationduration' => array(
                                'type' => 'number',
                                'title' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION'),
                                'desc' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION_DESC'),
                                'std' => '300',
                                'placeholder' => '300',
                            ),
                            'button_animationdelay' => array(
                                'type' => 'number',
                                'title' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY'),
                                'desc' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY_DESC'),
                                'std' => '0',
                                'placeholder' => '300',
                            ),
                        )
                    ),
                ),
            )
        )
);
