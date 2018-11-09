/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    './value',
    './item/current',
    './../url',
    'jquery/ui',
    './../lib/jquery.ui.touch-punch.min'
], function($, filterValue, currentFilterItem, url) {
    'use strict';

    $.widget('mage.awPriceSlider', $.ui.slider, {
        options: {
            popover: '[data-role=aw-layered-nav-popover]',
            submitOnValueChange: false,
            rangeLabelFromSelector: '[data-role="aw-layered-nav-price-label-from"]',
            rangeLabelToSelector: '[data-role="aw-layered-nav-price-label-to"]',
            fromInputSelector: '[data-role="aw-layered-nav-price-from"]',
            toInputSelector: '[data-role="aw-layered-nav-price-to"]',
            submitSelector: '[data-role="aw-layered-nav-price-submit"]'
        },

        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function() {
            this._bind();
            this._super();
            this.options.slide = this.setInputValues.bind(this);
            this.options.stop = this.onSliderChangeStop.bind(this);
            this.setInputValues();
            url.registerFilterRequestParam('price');
            if (this.isFilterActive()) {
                this.updateFilterValue();
            }
        },

        /**
         * Event binding
         * @private
         */
        _bind: function() {
            var fromInput = $(this.options.fromInputSelector),
                toInput = $(this.options.toInputSelector),
                submitSelector = $(this.options.submitSelector);

            fromInput.on('change', this.onChangeFrom.bind(this));
            toInput.on('change', this.onChangeTo.bind(this));
            submitSelector.on('click', this.onSubmitClick.bind(this));
        },

        /**
         * This method handles change event for 'from' input
         *
         * @param {Object} event
         * @private
         */
        onChangeFrom: function(event) {
            var input = $(event.target),
                minValue = this._valueMin(),
                toValue = this.values()[1];

            if(input.val() >= minValue && input.val() <= toValue) {
                this.values(0, input.val());
                this.changeRangeLabel(this.values());
                if (this.options.submitOnValueChange) {
                    this.submitFilter(input);
                }
            } else {
                input.val(this.values()[0])
            }
        },

        /**
         * This method handles change event for 'to' input
         *
         * @param {Object} event
         * @private
         */
        onChangeTo: function(event) {
            var input = $(event.target),
                maxValue = this._valueMax(),
                fromValue = this.values()[0];

            if(input.val() <= maxValue && input.val() >= fromValue) {
                this.values(1, input.val());
                this.changeRangeLabel(this.values());
                if (this.options.submitOnValueChange) {
                    this.submitFilter(input);
                }
            } else {
                input.val(this.values()[1])
            }
        },

        /**
         * This method is fired when slider control dragging stops
         * @private
         */
        onSliderChangeStop: function() {
            if (this.options.submitOnValueChange) {
                this.submitFilter(this.element);
            }
        },

        /**
         * This method handles submit button click
         * @private
         */
        onSubmitClick: function() {
            this.submitFilter(this.element);
        },

        /**
         * This method submits filter
         * @private
         */
        submitFilter: function(currentItem) {
            currentFilterItem.set(currentItem);
            $(this.options.popover).trigger('awLayeredNav:filterItemClick',[currentItem]);
            this.updateFilterValue();
        },

        /**
         * This method updates filter value
         * @private
         */
        updateFilterValue: function() {
            var fromInput = $(this.options.fromInputSelector),
                toInput = $(this.options.toInputSelector),
                priceRange = fromInput.val() + '-' + toInput.val();

            filterValue.add(
                'aw-filter-option-price',
                'price',
                priceRange
            );
        },

        /**
         * Check if price filter is set
         *
         * @returns {boolean}
         * @private
         */
        isFilterActive: function() {
            var minValue = this._valueMin(),
                maxValue = this._valueMax(),
                fromValue = this.values()[0],
                toValue = this.values()[1];

            return fromValue != minValue || toValue != maxValue;
        },

        /**
         * This method sets from-to inputs values on slider change
         *
         * @param {Object} event
         * @param {Object} ui
         */
        setInputValues: function(event, ui) {
            var fromInput = $(this.options.fromInputSelector),
                toInput = $(this.options.toInputSelector),
                values;

            if (ui) {   //'slide' event triggered
                values = ui.values
            } else {    //initial call
                values = this.values();
            }
            if (values) {
                this.changeRangeLabel(values);
                fromInput.val(values[0]);
                toInput.val(values[1]);
            }
        },

        /**
         * This method changes filter label on slider change
         *
         * @param {Array} values
         * @private
         */
        changeRangeLabel: function(values) {
            var rangeLabelFrom = $(this.options.rangeLabelFromSelector),
                rangeLabelTo = $(this.options.rangeLabelToSelector);

            if (values) {
                rangeLabelFrom.html(values[0]);
                rangeLabelTo.html(values[1]);
            }
        }
    });
    return $.mage.awPriceSlider;
});