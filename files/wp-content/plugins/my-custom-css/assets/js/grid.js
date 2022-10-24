jQuery(function($){
    $('.mycc-active-toggle').click(function(){
        var nonce = $(this).data('nonce');
        var id = $(this).data('id');
        var el = $(this);
        $.ajax({
            url:ajaxurl,
            type:'post',
            data: {
                action: 'mycc_active_toggle',
                id: id,
                _wpnonce: nonce
            },
            success: function () {
                el.toggleClass('active');
            }
        });
    });
});