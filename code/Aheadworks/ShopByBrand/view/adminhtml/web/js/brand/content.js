/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'mage/template',
    'mage/translate',
    'mage/adminhtml/events',
    'mage/adminhtml/wysiwyg/tiny_mce/setup',
    'mage/adminhtml/wysiwyg/widget',
    'Magento_Variable/variables'
], function ($, mageTemplate) {
    'use strict';

    $.widget('mage.awSbbContent', {
        dataIndex: 0,
        options: {
            template: '[data-role=row-template]',
            rowsContainer: '[data-role=rows-container]',
            row: '[data-role=row]',
            storeViewSelect: '[data-role=store-view-select]',
            addButton: '[data-role=add-button]',
            deleteButton: '[data-role=delete-button]',
            deleteFlag: '[data-role=delete-flag]',
            wysiwygElements: 'textarea[data-wysiwyg]',
            wysiwygConfig: [],
            optionValues: []
        },

        /**
         * Initialize widget
         */
        _create: function() {
            var self = this;

            this.template = mageTemplate(this.options.template);
            $.each(this.options.optionValues, function() {
                self._addRow(this);
            });
            this._bind();
        },

        /**
         * Event binding
         */
        _bind: function () {
            this._on({
                'click [data-role=add-button]': function () {
                    this._addRow({});
                },
                'click [data-role=delete-button]': function (event) {
                    this._deleteRow($(event.currentTarget).data('index'));
                }
            });
        },

        /**
         * Add row
         *
         * @param {Object} data
         * @private
         */
        _addRow: function (data) {
            data.index = this.dataIndex++;
            this.element
                .find(this.options.rowsContainer)
                .append(this.template({data: data}));

            // todo: more general logic
            if ('store_id' in data) {
                this.element
                    .find(this.options.storeViewSelect)
                    .filter('[data-index=' + data.index + ']')
                    .val(data.store_id);
            }
            this._initWysiwyg(data.index);
            if (data.index == 0) {
                this.element
                    .find(this.options.deleteButton)
                    .remove();
                this.element
                    .find(this.options.storeViewSelect)
                    .prop('disabled', true);
            }
        },

        /**
         * Delete row
         *
         * @param {integer} index
         * @private
         */
        _deleteRow: function (index) {
            this.element
                .find(this.options.deleteFlag)
                .filter('[data-index=' + index + ']')
                .val(1);
            this.element
                .find(this.options.row)
                .filter('[data-index=' + index + ']')
                .hide();
            this.element
                .find('input, textarea')
                .filter('.required-entry[data-index=' + index + ']')
                .removeClass('required-entry');
        },

        /**
         * Init wysiwyg elements
         *
         * @param {integer} index
         * @private
         */
        _initWysiwyg: function (index) {
            var wysiwygElements = this.element
                    .find(this.options.wysiwygElements)
                    .filter('[data-index=' + index + ']'),
                wysiwygConfig = this.options.wysiwygConfig;

            wysiwygElements.each(function () {
                var wysiwyg = new tinyMceWysiwygSetup($(this).attr('id'), wysiwygConfig);

                wysiwyg.setup('exact');
            });
        }
    });

    return $.mage.awSbbContent;
});
