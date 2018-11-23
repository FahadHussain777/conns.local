/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'Magento_Customer/js/customer-data'
], function ($, ko, Component, confirm, alert, customerData) {
    'use strict';
    var sidebarInitialized = false;
    return Component.extend({
        options: {
            classes: {
                addToCompareClass: 'connstocompare'
            },
            formKeyInputSelector: 'input[name="form_key"]'
        },
        initialize: function () {
            this._super();
            this.compareProducts = customerData.get('compare-products');
            this._initSidebar();
        },

        _initSidebar: function () {
            /**
             * When a .tocompare is clicked, get its data from its data-compare attribute.
             * Then add its data to this.compareProducts.items.
             */
            self = this;
            if (sidebarInitialized) {
                return;
            }
            sidebarInitialized = true;
            var anchor = $('.' + this.options.classes.addToCompareClass);
            this._setupChangeEvents(anchor);
            this._toggleBind(anchor);
        },
        _toggleBind : function (anchor) {
            self = this;
            // click label toggles checkbox and active class
            anchor.on('click',function (event) {
                event.stopPropagation();
                event.preventDefault();
                var checkbox = $(this).siblings('input.comparestatus');
                self._toggleactive(checkbox);
            });
            var checkbox = anchor.siblings('input.comparestatus');
            //click checkbox toggles checkbox and active class
            checkbox.change(function (event) {
                event.stopPropagation();
                event.preventDefault();
                self._togglechecbox(this);
            });
        },

        _toggleactive: function (checkbox){
            var anchor = $(checkbox).siblings('a.connstocompare');
            if($(anchor).hasClass('active')) {
                $(anchor).removeClass('active');
                var compareButton = $(anchor).siblings('div.compare-button-container');
                compareButton.addClass('no-display');
                var data = $(anchor).attr('data-compare');
                data = JSON.parse(data);
                this._removeItem(data.id,data);
                checkbox.prop('checked',false);
            }
            else {
                $(anchor).addClass('active');
                var compareButton = $(anchor).siblings('div.compare-button-container');
                compareButton.removeClass('no-display');
                checkbox.prop('checked',true);
            }
        },

        _togglechecbox: function (checkbox){
            var anchor = $(checkbox).siblings('a.connstocompare');
            if($(anchor).hasClass('active')) {
                $(anchor).removeClass('active');
                var compareButton = $(anchor).siblings('div.compare-button-container');
                compareButton.addClass('no-display');
                var data = $(anchor).attr('data-compare');
                data = JSON.parse(data);
                this._removeItem(data.id,data);
            }
            else {
                $(anchor).addClass('active');
                var compareButton = $(anchor).siblings('div.compare-button-container');
                compareButton.removeClass('no-display');
                var data = $(anchor).attr('data-compare');
                data = JSON.parse(data);
                this._addItem(data);
            }
        },

        _setupChangeEvents: function (anchor) {
            var $widget = this;
            $(anchor).on('click', function () {
                var element = $(this);
                var found = $widget._itemExists(element.data('compare'));
                if (!found) {
                    $widget._addItem({
                        'id': $(this).data('compare').id,
                        'product_url': $(this).data('compare').product_url,
                        'image_url': $(this).data('compare').image_url,
                        'small_image_url': $(this).data('compare').small_image_url,
                        'name': $(this).data('compare').name,
                        'remove_url': $(this).data('compare').remove_url,
                        'add_data': $(this).data('compare').add_data,
                        'price' : $(this).data('compare').price
                    });
                }
            });
        },

        _itemExists: function (compare) {
            if (!this.compareProducts().items) return false;
            var found = $.map(this.compareProducts().items, function (item) {
                if (item.id == compare.id) {
                    return item.id;
                }
            });
            return !$.isEmptyObject(found);
        },

        _addItem: function (item) {
            if(this.compareProducts().items === undefined)
            {
                this.compareProducts().items = [];
                this.compareProducts().count = 0;
                this.compareProducts().countCaption = this.compareProducts().count + ' Items';
            }
            if(this.compareProducts().items.length >= 5){
                alert({
                    content: "You can't add more than five items",
                    actions: {
                        always: function(){
                            var productId = item.id;
                            var anchor = $('a.compare-'+productId);
                            anchor.removeClass('active');
                            var checkbox = anchor.siblings('input.comparestatus');
                            var compareButton = anchor.siblings('div.compare-button-container');
                            compareButton.addClass('no-display');
                            checkbox.prop('checked',false);
                        }
                    }
                });
            }else{
                this.compareProducts().items.push(item);
                this.compareProducts().count++;
                this.compareProducts().countCaption = this.compareProducts().count == 1 ? this.compareProducts().count + ' Item' : this.compareProducts().count + ' Items';
                this.compareProducts.valueHasMutated(); // HACK: Does not update view if called from within jQuery.on(), so this is needed for some reason.
                var addData = JSON.parse(item.add_data);
                var formKey = $(this.options.formKeyInputSelector).val();
                if (formKey) {
                    addData.data.form_key = formKey;
                }
                $.ajax({
                    url: addData.action,
                    type: 'POST',
                    data: addData.data,
                    success: function (data, testStatus, jqXHR) {
                        //alert('Success');
                        // TODO: Check for data.success === true to determine if it was an actual success.
                        if (data.success == false) {
                            alert('actually false');
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert('failure');
                    }
                });
            }
        },

        _removeItem: function (id,data,event) {
            self = this;
            if(event !== undefined) event.stopPropagation();
            self._confirmRemove(id,data)
        },

        _removeItemAndConfirm: function (id,data,event) {
            self = this;
            if(event !== undefined) event.stopPropagation();
            confirm({
                content : "Are you sure you would like to remove this item from the compare products?",
                actions: {
                    confirm: function(){ self._confirmRemove(id,data)},
                    cancel: function(){
                        var productId = id;
                        var anchor = $('a.compare-'+productId);
                        anchor.addClass('active');
                        var checkbox = anchor.siblings('input.comparestatus');
                        var compareButton = anchor.siblings('div.compare-button-container');
                        compareButton.removeClass('no-display');
                        checkbox.prop('checked',true);
                    }
                }
            });
        },

        _afterRender : function(element){
            var li = $(element[1]);
            var productId = li.find('.compare-item-id').val();
            var anchor = $('a.compare-'+productId);
                setTimeout(function () {
                    if(anchor !== undefined && !anchor.hasClass('active')){
                       anchor.addClass('active');
                       var checkbox = anchor.siblings('input.comparestatus');
                       var compareButton = anchor.siblings('div.compare-button-container');
                       checkbox.prop('checked',true);
                       compareButton.removeClass('no-display');
                    }
                },500);
        },
        _confirmRemove : function (id,data) {
            var anchor = $('a.compare-'+id);
            anchor.removeClass('active');
            var checkbox = anchor.siblings('input.comparestatus');
            var compareButton = anchor.siblings('div.compare-button-container');
            checkbox.prop('checked',false);
            compareButton.addClass('no-display');
            this.compareProducts().items = this.compareProducts().items.filter(function(item){
                return item.id != id;
            });
            this.compareProducts().count--;
            this.compareProducts().countCaption = this.compareProducts().count == 1 ? this.compareProducts().count + ' Item' : this.compareProducts().count + ' Items';
            this.compareProducts.valueHasMutated(); // HACK: Does not update view if called from within jQuery.on(), so this is needed for some reason.
            var remove_url = JSON.parse(data.remove_url);
            var formKey = $(this.options.formKeyInputSelector).val();
            if (formKey) {
                remove_url.data.form_key = formKey;
            }
            $.ajax({
                url: remove_url.action,
                type: 'POST',
                data: remove_url.data,
                success: function (data, testStatus, jqXHR) {
                    //alert('Success');
                    // TODO: Check for data.success === true to determine if it was an actual success.
                    if (data.success == false) {
                        //alert('actually false');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    //alert('failure');
                }
            })
        }
    });
    /**
     * NOTES
     * 1. private_content_version cookie does not get set until you make a post request and update something that needs
     *    to be unique to a customer, like a compare products list.
     * 2. to set this.compareProducts, you can use this.compareProducts(data).
     */
});
