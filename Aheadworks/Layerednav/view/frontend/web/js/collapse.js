/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mage.awLayeredNavCollapse', {
        /**
         * Initialize widget
         */
        _create: function () {
            this._bind();
        },

        /**
         * Event binding
         */
        _bind: function () {
            this._on({

                /**
                 * Calls callback when event is triggered
                 */
                'click [data-role=title]': function () {
                    $(this.element).toggleClass('active');
                }
            });
        }
    });

    return $.mage.awLayeredNavCollapse;
});
