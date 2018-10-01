/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'Aheadworks_ShopByBrand/js/product-list/layout/default',
    'Aheadworks_ShopByBrand/js/product-list/layout/single-row',
    'Aheadworks_ShopByBrand/js/product-list/layout/slider'
], function($, defaultLayout, singleRow, slider) {
    'use strict';

    $.widget('mage.awSbbProductListLayout', {
        layout: singleRow,
        options: {
            layoutType: 'single_row',
            itemList: '[data-role=item-list]',
            itemSelector: '[data-role=item]',
            mainContent: '#maincontent'
        },

        /**
         * Initialize widget
         */
        _create: function() {
            this._resolveLayoutType()
                ._initLayout();
        },

        /**
         * Resolve layout type
         *
         * @returns {Object}
         * @private
         */
        _resolveLayoutType: function () {
            var layoutType = this.options.layoutType;

            if (layoutType == 'single_row') {
                this.layout = singleRow;
            } else if (layoutType == 'slider') {
                this.layout = slider;
            } else {
                this.layout = defaultLayout;
            }

            return this;
        },

        /**
         * Init layout type
         *
         * @returns {Object}
         * @private
         */
        _initLayout: function () {
            $.extend(this, this.layout);
            this.initLayout();
        }
    });

    return $.mage.awSbbProductListLayout;
});
