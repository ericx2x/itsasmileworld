(function($){
    var activate_btn = $(".js-mycc-activate-addon");
    var activate_checkbox = $('.js-mycc-agree-checkbox');

    activate_checkbox.on('change', function(){
        if($(this).is(":checked")) {
            activate_btn.prop('disabled', false);
        }else{
            activate_btn.prop('disabled', true);
        }
    });

    activate_btn.click(function () {
        var $this = $(this);
        var agree = $(".js-mycc-agree-checkbox:checked");
        if(agree.length === 0){
            return;
        }
        if($this.hasClass('loading')){
            return;
        }
        var nonce = $(this).data('nonce');

        $(this).addClass('loading');


        $.post(ajaxurl, {action:'mycc_install_addon', wpnonce:nonce}, function (request) {
            $this.removeClass('loading');
            if(!request.data.active){
                console.error(request.data.msg);
                return;
            }
            $('.js-disabled-link').removeClass('js-disabled-link');
            $('.js-mycc-requirements').html('<p class="mycc-activate-success">'+request.data.msg+'</p>');
        });
    });

    $('body').on('click', '.js-disabled-link', function (ev) {
        ev.stopPropagation();
        ev.preventDefault();
    });
})(jQuery);