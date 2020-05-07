define([
    'jquery',
    'jquery-ui-modules/widget',
    'mage/translate'
], function ($) {
    'use strict';

    let selectAllLink = document.getElementById('select_all_bough_together');
    let similarProductsCheckbox = document.getElementsByClassName('block bought-together')[0].getElementsByClassName('checkbox bought-together');
    let button = false;

    selectAllLink.onclick = function () {
        if (button) {
            button = false;
            Array.prototype.forEach.call(similarProductsCheckbox, function (item) {
                item.checked = false;
                document.getElementById('button_select_all_bought_together').innerHTML = $.mage.__("select all");
            });
        } else {
            button = true;
            Array.prototype.forEach.call(similarProductsCheckbox, function (item) {
                item.checked = true;
            });
            document.getElementById('button_select_all_bought_together').innerHTML = $.mage.__("unselect all");
        }
    }
});
