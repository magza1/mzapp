/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

define([
    'jquery',
    './default',
    'slick'
], function ($, Layout) {
    'use strict';

    return $.extend({}, Layout, {
        /**
         * @inheritdoc
         */
        initLayout: function () {
            this._initSlider();
            this._bind();
        },

        /**
         * Init slider
         *
         * @private
         */
        _initSlider: function () {
            this.element.find(this.options.itemList).slick({
                adaptiveHeight: false,
                autoplay: false,
                autoplaySpeed: 3000,
                arrows: true,
                dots: false,
                pauseOnHover: true,
                pauseOnDotsHover: false,
                respondTo: 'slider',
                responsive: [
                    {
                        breakpoint: 800,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2
                        }
                    },
                    {
                        breakpoint: 400,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            adaptiveHeight: true
                        }
                    }
                ],
                slidesToShow: 4,
                slidesToScroll: 4
            });
            this.element.css('opacity', 1);
        },

        /**
         * Event binding
         *
         * @private
         */
        _bind: function () {
            $(window).on('resize', $.proxy(this.recalculateWidth, this));
        },

        /**
         * Recalculation of the block width depending on the width of the screen
         */
        recalculateWidth: function() {
            var mainContent = this.element.closest(this.options.mainContent);

            if (mainContent.length) {
                if (mainContent.width() < 768) {
                    var column = this.element.closest('.columns > .column.main, .columns > .sidebar'),
                        sliderWidth = column.length && column.width() < mainContent.width()
                            ? column.width()
                            : mainContent.width();
                    this.element.outerWidth(sliderWidth);
                    this.element.find(this.options.itemsSelector).width(this.element.width());
                } else {
                    this.element.css('width', '');
                    this.element.find(this.options.itemsSelector).css('width', '');
                }
            }
        }
    });
});
