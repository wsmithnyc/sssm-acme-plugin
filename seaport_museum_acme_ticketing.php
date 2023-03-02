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

require_once __DIR__ . '/vendor/autoload.php';

use SeaportAcmeTicketing\Menus;


//Register the admin menu
Menus::registerAdminMenu();

require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

//plugin activation
register_activation_hook(__FILE__, ['SeaportAcmeTicketing\Activation', 'do_activation']);

register_uninstall_hook(    __FILE__, array( 'SeaportAcmeTicketing\Activation', 'do_uninstall' ) );

