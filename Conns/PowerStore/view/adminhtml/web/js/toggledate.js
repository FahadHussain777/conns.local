require(['jquery'],function ($) {
    var shopStatus = $("[id^='enabled']");
    shopStatus.each(function () {
        $(this).on('change',function () {
            var id = $(this).attr('id');
            var index = id[id.length-1];
            var fields = $('.day_visibility_'+index);
            if($(this).val() == 0){
                fields.attr('disabled','disabled');
            }else{
                fields.attr('disabled',false);
            }
        })
    });
});