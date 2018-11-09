/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    './../executor'
], function ($, Executor) {
    'use strict';

    return $.extend(Executor, {
        result: null,
        config: {
            processOutputFlag: 'aw_layered_nav_process_output',
            loaderContext: 'body'
        },

        /**
         * Initialize
         *
         * @param {Object} config
         */
        init: function (config) {
            $.extend(this.config, config);
        },

        /**
         * Submit request
         *
         * @param {String} url
         * @returns {Object}
         */
        submit: function (url) {
            var self = this,
                request = $.Deferred();

            $.ajax({
                url: this._addProcessOutputFlagToUrl(url),
                type: 'get',
                dataType: 'html',
                cache: false,

                /**
                 * A pre-request callback
                 */
                beforeSend: function () {
                    $(self.config.loaderContext).trigger('processStart');
                },

                /**
                 * Called when request succeeds
                 *
                 * @param {Object} response
                 */
                success: function (response) {
                    try {
                        self.result = JSON.parse(response);
                    } catch (e) {
                        window.location.replace(url);
                    }
                    request.resolve();
                },

                /**
                 * Called when request finishes
                 */
                complete: function () {
                    $(self.config.loaderContext).trigger('processStop');
                }
            });

            return request.promise();
        },

        /**
         * Get request result
         *
         * @returns {Object|null}
         */
        getResult: function () {
            return this.result;
        },

        /**
         * Add process output flag to the url and return modified url
         *
         * @param {String} url
         * @returns {String}
         */
        _addProcessOutputFlagToUrl: function (url) {
            var urlParts = url.split('?'),
                processOutputParam = this.config.processOutputFlag + '=' + '1',
                baseUrl = urlParts[0],
                urlParams = urlParts[1]
                    ? (urlParts[1] + '&' + processOutputParam)
                    : processOutputParam;

            return baseUrl + '?' + urlParams;
        }
    });
});
