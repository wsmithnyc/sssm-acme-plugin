<?php

add_action( 'admin_footer', 'sync_action_javascript' ); // Write our JS below here

function sync_action_javascript(): void
{ ?>
    <script type="text/javascript" >
        jQuery(document).ready(function($) {
            $('#btn_run_sync').click(run_sync);
            $('#btn_update_posts').click(update_posts);
        });

        function run_sync() {
            const data = {
                'action': 'sync_acme_data',
            };

            jQuery('#sync_status').html('<h3>Response</h3><p>Syncing Data...</p>');

            jQuery.post('/wp-admin/admin-ajax.php', data, function(response) {
                jQuery('#sync_status').html('<h3>Response</h3>');
            });
        }

        function update_posts() {
            const data = {
                'action': 'sync_acme_post_data',
            };

            jQuery('#meta_status').html('<h3>Response</h3><p>Updating Post Data...</p>');

            jQuery.post('/wp-admin/admin-ajax.php', data, function(response) {
                jQuery('#meta_status').html('<h3>Response</h3>' + response);
            });
        }

    </script>
<?php
}

?>
<div class="wrap">
    <h2>Acme Ticketing Settings</h2>
</div>
<p>Click to run sync:
    <button class="button action" id="btn_run_sync">Run Sync</button></p>
<div id="sync_status"></div>
<p>Click to update the event data on linked Posts:
    <button class="button action" id="btn_update_posts">Update Posts Event Data</button></p>
<div id="meta_status"></div>

