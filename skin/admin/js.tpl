{script src="/{baseadmin}/min/?f=plugins/{$pluginName}/js/admin.js" concat={$concat} type="javascript"}
<script type="text/javascript">
    $(function(){
        if (typeof MC_Hipay == "undefined"){
            console.log("MC_Hipay is not defined");
        }else{
            MC_Hipay.run(baseadmin);
        }
    });
</script>