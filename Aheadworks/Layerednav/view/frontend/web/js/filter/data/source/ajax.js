/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery'
], function ($) {
    'use strict';

    return {
        sequence: 0,
        config: {},
        result: null,

        /**
         * Initialize
         *
         * @param {Object} config
         */
        init: function (config) {
            $.extend(this.config, config);
        },

        /**
         * Fetch popover data
         *
         * @param {Array} filterValue
         * @returns {Object}
         */
        fetchPopoverData: function (filterValue) {
            var self = this,
                request = $.Deferred();

            this.sequence++;
            $.ajax({
                url: this.config.url,
                type: 'post',
                dataType: 'json',
                context: this,
                data: {
                    isAjax: 'true',
                    filterValue: filterValue,
                    pageType: this.config.pageType,
                    categoryId: this.config.categoryId,
                    searchQueryText: this.config.searchQueryText,
                    sequence: this.sequence
                },

                /**
                 * Called when request succeeds
                 *
                 * @param {Object} response
                 */
                success: function (response) {
                    if (response.success && response.sequence == self.sequence) {
                        self.result = {
                            itemsContent: response.itemsContent,
                            itemsCount: response.itemsCount
                        };
                        request.resolve();
                    }
                }
            });

            return request.promise();
        },

        /**
         * Get fetch result
         *
         * @returns {Object|null}
         */
        getResult: function () {
            return this.result;
        }
    };
});
