/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    './../url',
    './../updater',
    './request/bridge',
    './value'
], function ($, url, updater, requestBridge, value) {
    'use strict';

    $.widget('mage.awLayeredNavFilterActions', {
        showUrl: '',

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
                'click [data-role=show-button]': function () {
                    var submitUrl = url.getSubmitUrl(value.getPrepared());

                    requestBridge.submit(submitUrl).then(
                        /**
                         * Called after request finishes
                         */
                        function () {
                            updater.update(submitUrl, requestBridge.getResult());
                        }
                    );
                },

                /**
                 * Calls callback when event is triggered
                 */
                'click [data-role=clear-button]': function () {
                    var clearUrl = url.getClearUrl();

                    requestBridge.submit(clearUrl).then(
                        /**
                         * Called after request finishes
                         */
                        function () {
                            updater.update(clearUrl, requestBridge.getResult());
                        }
                    );
                }
            });
            this.element.on('awLayeredNav:showActionDisabled', $.proxy(this._disableShowButton, this));
            this.element.on('awLayeredNav:showActionEnabled', $.proxy(this._enableShowButton, this));
        },

        /**
         * Disable Show button
         */
        _disableShowButton: function () {
            var buttonShow = this.element.find('[data-role=show-button]');
            buttonShow.addClass('disabled');
        },

        /**
         * Enable Show button
         */
        _enableShowButton: function () {
            var buttonShow = this.element.find('[data-role=show-button]');
            buttonShow.removeClass('disabled');
        }
    });

    return $.mage.awLayeredNavFilterActions;
});
