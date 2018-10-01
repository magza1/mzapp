/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    './default'
], function ($, Layout) {
    'use strict';

    return $.extend({}, Layout, {
        /**
         * @inheritdoc
         */
        initLayout: function () {
            this.hideExcessItems();
            this._bind();
        },

        /**
         * Event binding
         *
         * @private
         */
        _bind: function () {
            $(window).on('resize', $.proxy(this.hideExcessItems, this));
        },

        /**
         * Show items to fit one row
         */
        hideExcessItems: function () {
            var itemList = $(this.options.itemList),
                itemListWidth = itemList.width(),
                items = itemList.find(this.options.itemSelector),
                itemWidth = items.first().outerWidth(),
                itemsToShow = Math.round(itemListWidth/itemWidth);

            items.each(function(index, item) {
                if (index < itemsToShow) {
                    $(item).show();
                } else {
                    $(item).hide();
                }
            });
        }
    });
});
