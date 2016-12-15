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
     * Save
     * @param id
     * @param collection
     * @param type
     */
    function save(baseadmin,id){
        $(id).validate({
            onsubmit: true,
            event: 'submit',
            rules: {
                wsLogin: {
                     required: true
                 },
                wsPassword: {
                     required: true
                 },
                websiteId: {
                    required: true
                },
                customerIpAddress: {
                    required: true
                },
                formaction: {
                    required: true
                }
            },
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
                            display:true,
                            refresh: true
                        });
                    }
                });
                return false;
            }
        });
    }
    return {
        run:function(baseadmin){
            save(baseadmin,'#forms_plugins_hipay');
        }
    }
})(jQuery, window, document);