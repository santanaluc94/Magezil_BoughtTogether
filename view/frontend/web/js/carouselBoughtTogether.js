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
    let selectAllLink = $('#select_all_bought_together'),
        boughtTogetherProducts = $('.block-bought-together .block-content-bought-together').find('.product-item'),
        button = false,
        productObject = '{}';


    $.each(boughtTogetherProducts, function (index, item) {
        let productId = $(item).find('[data-product-id]').data('product-id'),
            productQty = $(item).find('[data-product-qty]').data('product-qty');

        productObject.products[index] = {
            productId: productId,
            qty: productQty
        };

        console.log(index);
        console.log(productId);
        console.log(productObject);
        console.log('----------');
    });

    selectAllLink.onclick = function () {
        if (button) {
            button = false;
            Array.prototype.forEach.call(similarProductsCheckbox, function (item) {
                item.checked = false;
                document.getElementById('select_all_bought_together').innerHTML = $.mage.__("select all");
            });
        } else {
            button = true;
            Array.prototype.forEach.call(similarProductsCheckbox, function (item) {
                item.checked = true;
            });
            document.getElementById('select_all_bought_together').innerHTML = $.mage.__("unselect all");
        }
    }

    // Insert product ids in input to add to cart
    let productIds = $('#product_ids').val();

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
