/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([], function () {
    'use strict';

    return {
        executor: null,

        /**
         * Initialize
         *
         * @param {Object} config
         */
        init: function (config) {
            this.executor = config.executor;
        },

        /**
         * Submit request
         *
         * @param {String} url
         * @returns {Object}
         */
        submit: function (url) {
            return this.executor.submit(url);
        },

        /**
         * Get request result
         *
         * @returns {Object|null}
         */
        getResult: function () {
            return this.executor.getResult();
        }
    };
});
