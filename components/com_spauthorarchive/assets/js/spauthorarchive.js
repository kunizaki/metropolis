/**
* @package com_spauthorarchive
*
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2019 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
jQuery((function(s){s(".sp-bookmark-form .btn-spbookmark-action").on("click",(function(a){a.preventDefault();var e=s(this).parent("form.sp-bookmark-form"),o=e.serializeArray();s.ajax({type:"POST",url:"index.php?option=com_spauthorarchive&task=bookmarks.addBookmark",format:"json",data:o,beforeSend:function(){e.find("span.spbookmark-icon").removeClass("fa-bookmark-o").addClass("fa-spinner fa-pulse")},success:function(s){var a="string"==typeof s&&s.length>0&&JSON.parse(s);a?(a.status?"add"==a.action_type||"update"==a.action_type?e.find("span.spbookmark-icon").removeClass("fa-spinner fa-pulse").addClass("fa-bookmark"):e.find("span.spbookmark-icon").removeClass("fa-spinner fa-pulse").addClass("fa-bookmark-o"):(e.find("span.spbookmark-icon").removeClass("fa-spinner fa-pulse").addClass("fa-bookmark-o"),a.loggedin?a.message&&Joomla.renderMessages({success:[a.message]}):(a.message&&Joomla.renderMessages({success:[a.message]}),window.location.href=a.loginurl)),a.message&&Joomla.renderMessages({success:[a.message]})):a.message&&Joomla.renderMessages({danger:[a.message]})}})}))}));