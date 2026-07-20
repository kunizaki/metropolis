/**
 * @package com_spproperty
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

var getVariables = function (url) {
    let query = url.slice(url.indexOf('?') + 1).split('&');
    variables = [];
    for (let i = 0; i < query.length; i++) {
        let val = query[i].split('=');
        variables[val[0]] = val[1];
    }
    return variables;
}
jQuery(function ($) {
    $sort = $('#sorting');
    $sort.on('change', function (event) {
        event.preventDefault();
        $this = $(this);
        let value = $this.val();
        var url = window.location.href;
        const root = url.split('?')[0];
        var variables = getVariables(url);
        if (variables['sorting'] == undefined) {
            variables['sorting'] = value;
        } else {
            variables['sorting'] = value;
        }

        let vars = [];
        for (let [key, val] of Object.entries(variables)) {
            vars.push(key + '=' + val);
        }
        vars = vars.join('&');
        url = root + '?' + vars;

        window.location.href = url;
    });
});