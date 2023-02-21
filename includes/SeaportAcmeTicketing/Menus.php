<?php

namespace SeaportAcmeTicketing;

class Menus {
	const PARENT_SLUG = 'acme_ticketing/admin.php';

	public static function registerAdminMenu(): void {
		add_action('admin_menu', [Menus::class,'setupMenuItems']);
	}

	public static function setupMenuItems (): void {
		add_menu_page(
			'Acme Ticketing',
			'Acme Ticketing',
			'manage_options',
			self::PARENT_SLUG,
			[ Admin::class, 'acme_ticketing_admin_page' ],
			'dashicons-tickets',
			6
		);


		add_submenu_page(
			self::PARENT_SLUG,
			'Acme Ticketing: Events',
			'Events',
			'manage_options',
			'acme_ticketing/events.php',
			'test',
			[ Admin::class, 'acme_ticketing_admin_page' ]
		);

		add_submenu_page(
			self::PARENT_SLUG,
			'Acme Ticketing: Configuration',
			'Config',
			'manage_options',
			'acme_ticketing/events.php',
			'test',
			[ Admin::class, 'acme_ticketing_admin_page' ]
		);
	}
}