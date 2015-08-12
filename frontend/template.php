<script>
    jQuery(function () {
        jQuery("input[id*='date']").datepicker();
        jQuery( "#tabs" ).tabs({ disabled: [1, 2] });
    });

    function first_step () {

        jQuery('#tabs').tabs('option','disabled',[2]).tabs('option', 'active',  [1]);


    }

    function send(){

        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: "POST",
            data: {action: "af_feedback_send", data : jQuery('#af_feedback_form').serialize()},
            success: function(html){
                jQuery('#tabs-third').html('saqwc');
                jQuery('#tabs').tabs('option','disabled',[]).tabs('option', 'active',  [2]);
            }
        });
    };
</script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
<div id="tabs">
    <ul>
        <li><a href="#tabs-first">Step 1</a></li>
        <li><a href="#tabs-second">Step 2</a></li>
        <li><a href="#tabs-third">Step 3</a></li>
    </ul>
    <form id="af_feedback_form" action="" method="POST">
        <div id="tabs-first">
            <label>From</label>
            <input type="text" id="date-from" name="from"><br>
            <label>To</label>
            <input type="text" id="date-to" name="to">

            <input onclick="first_step()" type="button" id="first" value="Next Step">
        </div>
        <div id="tabs-second">
            <label>Name</label>
            <input type="text" id="name" name="name"><br>
            <label>Phone</label>
            <input type="text" id="phone" name="phone"><br>
            <label>Email</label>
            <input type="text" id="email" name="email">

            <input onclick="send()" type="button" id="second" value="Next Step">
        </div>
        <div id="tabs-third">

        </div>
    </form>
</div>
