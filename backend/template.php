<script>
    jQuery(function () {
        jQuery("#tabs").tabs();
    });


    function savesettings() {

        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: "POST",
            data: {action: "af_feedback_settings", data: jQuery('#af_feedback_settings').serialize()},
            success: function (html) {
                jQuery('#save').append(html);

            }
        });
    }

    function resend(id) {
        var id = id;
        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: "POST",
            data: {action: "af_feedback_resend", data: id},
            success: function (html) {
                jQuery('#send-'+id).append(html);

            }
        });
    }
</script>
<style type="text/css">

    td, th {
        padding: 3px;
        border: 1px solid #1a17f6;
    }
    tbody {
        background: #d3d3d3;
    }
    thead {
        background: #808080;
    }
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
<div id="tabs">
    <ul>
        <li><a href="#tabs-first">Entries</a></li>
        <li><a href="#tabs-second">Settings</a></li>
    </ul>
    <div id="tabs-first">
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Start date</th>
                <th>End date</th>
                <th>Send time</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ( $submittedForms as $item ): ?>
                <tr>
                    <th><?php echo $item->username; ?></th>
                    <th><?php echo $item->phone; ?></th>
                    <th><?php echo $item->email; ?></th>
                    <th><?php echo $item->from_date; ?></th>
                    <th><?php echo $item->to_date; ?></th>
                    <th><?php echo date('l jS \of F Y h:i:s A', $item->submit_time); ?></th>
                    <th id="send-<?php echo $item->id; ?>"><input type="button" onclick="resend(<?php echo $item->id; ?>)" value="Send email"></th>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div id="tabs-second">
        <form id="af_feedback_settings">
            <label>Managers email</label><br>
            <input type="text" id="manager_email" name="manager_email" value="<?php echo $pluginOption[ 'manager_email' ]; ?>"><br>
            <label>Confirmation email subject</label><br>
            <input type="text" id="email_subj" name="email_subj" value="<?php echo $pluginOption[ 'email_subj' ]; ?>"><br>
            <label>Confirmation email content</label><br>
            <textarea id="email_content" name="email_content"><?php echo $pluginOption[ 'email_content' ]; ?></textarea><br>
            <input onclick="savesettings()" type="button" value="Save">
            <span id="save"></span>
        </form>
    </div>
</div>
