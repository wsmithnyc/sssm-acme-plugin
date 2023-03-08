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
			'Templates',
			'manage_options',
			'acme_ticketing_events.php',
			[ Admin::class, 'acme_ticketing_events_page' ]
		);

		add_submenu_page(
			self::PARENT_SLUG,
			'Acme Ticketing: Daily Calendar',
			'Daily Calendar',
			'manage_options',
			'acme_ticketing_daily_calendar.php',
			[ Admin::class, 'acme_ticketing_template_calendar' ]
		);

		add_submenu_page(
			self::PARENT_SLUG,
			'Acme Ticketing: Configuration',
			'Config',
			'manage_options',
			'acme_ticketing_config.php',
			[ Admin::class, 'acme_ticketing_config_page' ]
		);

		add_submenu_page(
			self::PARENT_SLUG,
			'Acme Ticketing: Data Sync',
			'Sync',
			'manage_options',
			'acme_ticketing_sync.php',
			[ Admin::class, 'acme_ticketing_sync_page' ]
		);

        add_submenu_page(
            self::PARENT_SLUG,
            'Acme Ticketing: System Log',
            'Logs',
            'manage_options',
            'acme_ticketing_log.php',
            [ Admin::class, 'acme_ticketing_log_page' ]
        );
	}
}