<?php

add_action( 'admin_footer', 'my_action_javascript' ); // Write our JS below here

function my_action_javascript() { ?>
    <script type="text/javascript" >
        jQuery(document).ready(function($) {
            $('#btn_run_sync').click(run_sync);
        });

        function run_sync() {
            const data = {
                'action': 'sync_acme_data',
            };

            jQuery('#sync_status').html('<h3>Response</h3><p>Syncing Data...</p>');

            jQuery.post('/wp-admin/admin-ajax.php', data, function(response) {
                jQuery('#sync_status').html('<h3>Response</h3>' + response);
            });
        }
    </script>
<?php
}

?>
<div class="wrap">
    <h2>Acme Ticketing Settings</h2>
</div>
<p>Click to run sync: <button id="btn_run_sync">Run Sync</button></p>
<div id="sync_status"></div>

