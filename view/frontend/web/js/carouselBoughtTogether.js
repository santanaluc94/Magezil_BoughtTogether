define([
    'jquery',
    'jquery-ui-modules/widget',
    'mage/translate',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    // Reload cart
    window.load = function () {
        let sections = ['cart'];

        customerData.invalidate(sections);
        customerData.reload(sections, true);
    };

    // Select all products
    let selectAllLink = document.getElementById('select_all_bought_together');
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

    // Insert product ids in input to add to cart
    let productIds = $('#product_ids').val();

    if (productIds) {
        productIds = productIds.split(',');
    } else {
        productIds = [];
    }

    $('.checkbox.bought-together').click(function (e) {
        let productId = e.target.value;

        if (productIds.includes(productId)) {
            productIds.splice(productIds.indexOf(productId), 1);
        } else {
            productIds.push(productId);
        }

        $('#product_ids').value = productIds.toString();
    });
});
