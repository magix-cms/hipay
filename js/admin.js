var MC_Hipay = (function($, window, document, undefined){
    /**
     * set ajax load data
     * @param type
     * @param baseadmin
     * @param getlang
     * @param edit
     * @returns {string}
     */
    function setAjaxUrlLoad(baseadmin){
        return '/'+baseadmin+'/plugins.php?name=hipay';
    }

    /**
     *
     * @param baseadmin
     */
    function getCategoryData(baseadmin,id){
        $.nicenotify({
            ntype: "ajax",
            uri: setAjaxUrlLoad(baseadmin)+'&category='+id,
            typesend: 'get',
            datatype: 'html',
            beforeParams:function(){
                $('#category').empty();
            },
            successParams:function(data){
                $.nicenotify.initbox(data,{
                    display:false
                });
                $('#category').html(data);
                $('#categoryId').rules('add', {required: true});
                /*$('#websiteId').rules('remove', "remote");
                $('#websiteId').attr("readonly","readonly");*/
            }
        });
    }

    function setAjaxBox(id){
        $(id).on('change',function() {
            var $currentOption = $(this).find('option:selected').val();
            var $boxWebsiteId = $($(this).data('target-url')),
                $actionBox = 'show';
            $boxWebsiteId.collapse($actionBox);
            $.nicenotify({
                ntype: "ajax",
                uri: setAjaxUrlLoad(baseadmin),
                typesend: 'post',
                noticedata:{formaction:$currentOption},
                beforeParams:function(){},
                successParams:function(e){
                    $.nicenotify.initbox(e,{
                        display:false
                    });
                }
            });
        });
    }

    /**
     * Save
     * @param id
     * @param baseadmin
     */
    function save(baseadmin,id){
        var rules = {
            wsLogin: {
                required: true
            },
            wsPassword: {
                required: true
            },
            websiteId: {
                required: true,
                    remote: {
                    url: '/admin/plugins.php?name=hipay',
                        type: "get",
                        complete: function(data) {
                            if(data.responseText === 'true'){

                                getCategoryData(baseadmin,$("#websiteId").val());
                                var $options = $('.optional-fields');

                                $options.each(function() {
                                    var $box = $($(this).data('target')),
                                        $action = 'show';

                                    $box.collapse($action);
                                });

                            }else{
                                var $options = $('.optional-fields');

                                $options.each(function() {
                                    var $box = $($(this).data('target')),
                                        $action = 'hide';

                                    $box.collapse($action);
                                    $('#categoryId').rules('remove');
                                });
                            }
                        }
                    /*complete: function(data) {
                     alert('okkkk');
                     }*/

                }
            },
            customerIpAddress: {
                required: true
            },
            formaction: {
                required: true
            }
        };
        $(id).validate({
            onsubmit: true,
            event: 'submit',
            rules: rules,
            submitHandler: function(form) {
                $.nicenotify({
                    ntype: "submit",
                    uri: setAjaxUrlLoad(baseadmin),
                    typesend: 'post',
                    idforms: $(form),
                    resetform: false,
                    beforeParams:function(){},
                    successParams:function(e){
                        $.nicenotify.initbox(e,{
                            display:true
                        });
                        window.setTimeout(function() { $(".mc-message .alert-success").alert('close'); }, 4000);
                    }
                });
                return false;
            }
        });
    }
    return {
        run:function(baseadmin){
            setAjaxBox('select#formaction');
            save(baseadmin,'#forms_plugins_hipay');
            /*$('#websiteId').blur(function(){
                alert('test');
            });*/
        }
    }
})(jQuery, window, document);