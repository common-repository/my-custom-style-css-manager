
    (function ($) {


        $(document).ready(function(){
            var tmp = $('.CodeMirror').get(0);
            $('#submit').after('<span  id="submit_dummy" class="button button-success" >'+_MCH_BTN_LABEL_UPDATE+'</span>');

            $("#submit_dummy").on("click", function(){
                var params = {
                    action: _MCH_ADMIN_AJAX_ACTION_UPDATE_STYLE_RESPONSE
                };

                $.ajax({
                    url: _MCH_MCSCM_SITE_URL + '/wp-admin/admin-ajax.php',
                    method: 'GET',
                    dataType: 'json',
                    data: params,
                }).done(function (data, textStatus, jqXHR) {
                    var editor = $('.CodeMirror')[0].CodeMirror;
                    editor.setValue(data.newContent);
                    editor.save();
                }).fail(function(jqXHR, textStatus, errorThrown){
                    // console.log(textStatus);
                    // console.log(errorThrown);
                    // console.log(this);
                }).always(function (jqXHR, textStatus) {
                });
            });


        });

    })(jQuery);