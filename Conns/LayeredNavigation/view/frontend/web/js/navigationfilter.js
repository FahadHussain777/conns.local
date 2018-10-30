define([
    'jquery'
], function($) {
    "use strict";

    $.widget('Conns.navigationfilter', {


        _init: function() {
            var self = this;
            if(self.element.prop('tagName') == 'LI'){
                var label = self.element.find('label');
                var checkbox = $(label).siblings('input');
                this._bind(self,label,checkbox);
                if(self.element.hasClass('active')) $(checkbox).prop('checked',true);
            }
            else if(self.element.prop('tagName') == 'A'){
                this._anchorbind(self);
            }
        },

        _bind: function(self,label,checkbox) {

            // click label toggles checkbox and active class
            label.on('click',function (event) {
                event.stopPropagation();
                event.preventDefault();
                self._toggleactive(checkbox);
            });

            // click checkbox toggles checkbox and active class
            checkbox.change(function (event) {
                event.stopPropagation();
                event.preventDefault();
                self._togglechecbox();
            });
        },

        _anchorbind:function(self){
            self.element.on('click',function (event) {
                event.stopPropagation();
                event.preventDefault();
                if(self.element.hasClass('active')) {
                    self.element.removeClass('active');
                    $('#conns-layered-nav-apply').css('visibility','visible');
                }
                else {
                    self.element.addClass('active');
                    $('#conns-layered-nav-apply').css('visibility','visible');
                }
            });
        },

        _toggleactive: function(checkbox){
            if(this.element.hasClass('active')) {
                this.element.removeClass('active');
                checkbox.prop('checked',false);
                $('#conns-layered-nav-apply').css('visibility','visible');
            }
            else {
                this.element.addClass('active');
                checkbox.prop('checked',true);
                $('#conns-layered-nav-apply').css('visibility','visible');
            }
        },

        _togglechecbox: function(){
            if(this.element.hasClass('active')) {
                this.element.removeClass('active');
                $('#conns-layered-nav-apply').css('visibility','visible');
            }
            else {
                this.element.addClass('active');
                $('#conns-layered-nav-apply').css('visibility','visible');
            }
        },

    });
    return $.Conns.navigationfilter;
});
