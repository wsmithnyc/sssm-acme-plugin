<?php
/*
Plugin Name: Acme Ticketing Plugin
Plugin URI:
description: A plugin for reading data from Acme Ticketing: <a href='https://www.acmeticketing.com/' target='_blank'>https://www.acmeticketing.com</a>
Version: 1.0
Author: William Mallick
Author URI: wmallick@outlook.com
License: GPL2
*/
//require the autoload file made by composer
//file composer.json will include the class maps for this plugin's classes
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_head', 'acme_ticketing_admin_css' );

function acme_ticketing_admin_css()
{
    echo '<style>
    table,  {
      min-width: 300px;
    } 
    
    td, th {
        padding: 4px 20px 4px 4px;
        font-size: 16px;
        text-align: left;
    }
    
    table.wp-list-table.fixed {
       table-layout: auto !important;
    }
    
    .column-short_description {
        width: 25%;
    }
    
    .column-linked_posts {
        width: 12%;
    }

  </style>';
}

require_once __DIR__ . '/vendor/autoload.php';

use JetBrains\PhpStorm\NoReturn;
use SeaportAcmeTicketing\Controller;
use SeaportAcmeTicketing\Menus;


//Register the admin menu
Menus::registerAdminMenu();

require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

//plugin activation
register_activation_hook( __FILE__, [ 'SeaportAcmeTicketing\Activation', 'do_activation' ] );

//plugin deletion
register_uninstall_hook( __FILE__, [ 'SeaportAcmeTicketing\Activation', 'do_uninstall' ] );

/************************* Admin AJAX Methods *****************************/
add_action( 'wp_ajax_sync_acme_data', 'acme_ticketing_sync_acme_data' );

function acme_ticketing_sync_acme_data(): void
{
    ( new Controller() )->syncAcmeDataRequest();
}

add_action( 'wp_ajax_sync_acme_post_data', 'acme_ticketing_sync_acme_meta_data' );

#[NoReturn] function acme_ticketing_sync_acme_meta_data(): void
{
    $html = ( new Controller() )->syncAcmeMetaDataRequest();

    exit( $html );
}

//add acme event link to SSSM_POSTS list
add_action( 'manage_sssm-page_posts_custom_column',
            [ 'SeaportAcmeTicketing\Hooks', 'display_acme_event_custom_column' ],
            5,
            2 );
add_filter( 'manage_sssm-page_posts_columns', [ 'SeaportAcmeTicketing\Hooks', 'add_sssm_posts_event_columns' ] );
