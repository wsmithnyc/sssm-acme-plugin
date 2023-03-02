<?php

add_action( 'admin_footer', 'my_action_javascript' ); // Write our JS below here

function my_action_javascript() { ?>
    <script type="text/javascript" >
        jQuery(document).ready(function($) {

            var data = {
                'action': 'acme_ticketing_sync',
            };

            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(ajaxurl, data, function(response) {
                alert('Got this from the server: ' + response);
            });
        });
    </script> <?php
}

?>

