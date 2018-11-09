/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    './../filter/request/bridge',
    './../updater',
    'jquery/ui'
], function($, requestBridge, updater) {
    'use strict';

    $.widget('mage.awLayeredNavPager', {
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
                'click a[href!=#]': function (event) {
                    var updateUrl = $(event.currentTarget).attr('href');

                    event.preventDefault();
                    requestBridge.submit(updateUrl).then(
                        /**
                         * Called after request finishes
                         */
                        function () {
                            updater.update(updateUrl, requestBridge.getResult());
                        }
                    );
                }
            });
        }
    });

    return $.mage.awLayeredNavPager;
});
