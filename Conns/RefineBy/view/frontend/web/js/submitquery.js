define([
    'jquery'
], function($) {
    "use strict";

    $.widget('Conns.submitquery', {


        _init: function() {
            this._bind();
        },

        _bind: function () {
            this.element.on('click',function () {
                // empty query object initialization
                var query = {};

                // Setting pagination filters
                var url = window.location;
                var url = new URL(url);

                var pagelistlimit = url.searchParams.get("product_list_limit");
                var productlistorder = url.searchParams.get("product_list_order");
                var productlistmode = url.searchParams.get("product_list_mode");
                var productlistdir = url.searchParams.get("product_list_dir");

                if(pagelistlimit != null) query["product_list_limit"] = pagelistlimit;
                if(productlistorder != null) query["product_list_order"] = productlistorder;
                if(productlistmode != null) query["product_list_mode"] = productlistmode;
                if(productlistdir != null) query["product_list_dir"] = productlistdir;

                // Setting active filters
                $('.conns-refineby-items').each(function () {

                    // For chekbox filters
                    if($(this).prop('tagName')=='OL'){
                        $(this).children('li.conns-nav-item.active').each(function () {
                            var parameter = $(this).attr('parameter');
                            var value = $(this).attr('val');
                            if(query[parameter] != undefined){
                                query[parameter] = query[parameter]+','+value;
                            }
                            else
                                query[parameter] = value;
                        });
                    }
                    // For swatch filters
                    else if($(this).prop('tagName')=='DIV'){
                        $(this).find('a.conns-nav-item.active').each(function () {
                            var parameter = $(this).attr('parameter');
                            var value = $(this).attr('val');
                            if(query[parameter] != undefined){
                                query[parameter] = query[parameter]+','+value;
                            }
                            else
                                query[parameter] = value;
                        });
                    }
                });
                var recursiveDecoded = decodeURIComponent( $.param( query ) );
                window.location = window.location.pathname+'?'+recursiveDecoded;
            });
        }

    });
    return $.Conns.submitquery;
});
