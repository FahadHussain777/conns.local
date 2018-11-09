/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery'
], function ($) {
    'use strict';

    return {
        currentUrl: window.location.href,
        filterRequestParams: [],
        paramsToRemove: ['_', 'aw_layered_nav_process_output'],

        /**
         * Register filter request param
         *
         * @param {String} paramName
         */
        registerFilterRequestParam: function (paramName) {
            if ($.inArray(paramName, this.filterRequestParams) == -1) {
                this.filterRequestParams.push(paramName);
            }
        },

        /**
         * Set current url
         *
         * @param {String} url
         */
        setCurrentUrl: function (url) {
            this.currentUrl = this._removeParams(url, this.paramsToRemove);
        },

        /**
         * Get current url
         *
         * @returns {String}
         */
        getCurrentUrl: function () {
            return this.currentUrl;
        },

        /**
         * Get current url with modified request param
         *
         * @param {String} paramName
         * @param {String} paramValue
         * @param {String} defaultValue
         * @returns {String}
         */
        getCurrentUrlWithChangedParam: function (paramName, paramValue, defaultValue) {
            var paramsToUpdate = {};

            if (paramValue == defaultValue) {
                return this._removeParams(this.currentUrl, [paramName]);
            }
            paramsToUpdate[paramName] = paramValue;

            return this._updateParams(this.currentUrl, paramsToUpdate);
        },

        /**
         * Get submit url
         *
         * @param {Array} filterValue
         * @returns {String}
         */
        getSubmitUrl: function (filterValue) {
            var url = this._removeParams(this.currentUrl, this.filterRequestParams);

            return this._updateParams(url, this._prepareFilterValue(filterValue));
        },

        /**
         * Get clear url
         *
         * @returns {String}
         */
        getClearUrl: function () {
            return this._removeParams(this.currentUrl, this.filterRequestParams);
        },

        /**
         * Prepare filter value
         *
         * @param {Array} filterValue
         * @returns {Object}
         */
        _prepareFilterValue: function (filterValue) {
            var result = {};

            $.each(filterValue, function () {
                if (result.hasOwnProperty(this.key)) {
                    result[this.key] = result[this.key] + ',' + this.value;
                } else {
                    result[this.key] = this.value;
                }
            });

            return result;
        },

        /**
         * Update params in url and return modified url
         *
         * @param {String} url
         * @param {Object} params
         * @returns {String}
         */
        _updateParams: function (url, params) {
            var urlData = this._parseUrl(url);

            for (var paramName in params) {
                if (params.hasOwnProperty(paramName)) {
                    urlData.params[paramName] = params[paramName];
                }
            }

            return this._buildUrl(urlData);
        },

        /**
         * Remove params from url and return modified url
         *
         * @param {String} url
         * @param {Array} paramNames
         * @returns {String}
         */
        _removeParams: function (url, paramNames) {
            var urlData = this._parseUrl(url);

            $.each(paramNames, function () {
                if (urlData.params.hasOwnProperty(this)) {
                    delete urlData.params[this];
                }
            });

            return this._buildUrl(urlData);
        },

        /**
         * Parse url
         *
         * @param {String} url
         * @returns {Object}
         */
        _parseUrl: function (url) {
            var decode = window.decodeURIComponent,
                urlPaths = url.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters;

            for (var i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[decode(parameters[0])] = parameters[1] !== undefined
                    ? decode(parameters[1].replace(/\+/g, '%20'))
                    : '';
            }

            return {baseUrl: baseUrl, params: paramData};
        },

        /**
         * Build url
         *
         * @param {String} urlData
         * @returns {String}
         */
        _buildUrl: function (urlData) {
            var params = $.param(urlData.params);

            return urlData.baseUrl + (params.length ? '?' + params : '');
        }
    };
});
