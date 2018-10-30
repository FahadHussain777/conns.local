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
                var query = {};
                $('.conns-layerednavigation-items').each(function () {
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
