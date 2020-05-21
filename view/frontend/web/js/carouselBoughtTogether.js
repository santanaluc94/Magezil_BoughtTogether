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
    let productIds = document.getElementById('product_ids').value;

    if (productIds) {
        productIds = productIds.split(',');
    } else {
        productIds = [];
    }

    document.body.addEventListener('click', function (e) {
        if (!e.target.classList.contains('checkbox-bought-together')) return;

        let productId = e.target.value;

        if (productIds.includes(productId)) {
            productIds.splice(productIds.indexOf(productId), 1);
        } else {
            productIds.push(productId);
        }

        document.getElementById('product_ids').value = productIds;
    });
});
