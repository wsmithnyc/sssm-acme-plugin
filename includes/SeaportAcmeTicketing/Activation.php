<?php

namespace SeaportAcmeTicketing;

use Exception;
use wpdb;
use Carbon\Carbon;
use function dbDelta;


class Activation {
	const PLUGIN_DB_OPTION = 'acme_ticketing_db_version';

    public wpdb $wpdb;

    public string $settings_table_name;

	public function __construct()
	{
        //WordPress database class
        global $wpdb;

		$this->wpdb = $wpdb;

        //settings table
        $this->settings_table_name = (new Database())->settings_table_name;
	}

    public static function do_activation()
    {
        (new Activation())->activate();
    }

    public static function do_uninstall()
    {
        (new Activation())->uninstall();
    }

    public function activate(): void
    {
        //create new tables;
        $this->install_tables();

        //insert core setting values
        $this->install_config_data();
    }

    public function deactivate(): void
    {
        //todo: add deactivation code
    }

    public function uninstall(): void
    {
        $this->uninstall_tables();
    }

    /**************************** Database Schema Setup *********************/
    /**
     * set up tables for Acme API data
     *
     * @return void
     */
    function install_tables(): void
    {
		$charset_collate = $this->wpdb->get_charset_collate();

        try {
            //log table
            $table_name = $this->wpdb->prefix . Constants::TABLE_LOG;

            $sql = "CREATE TABLE {$table_name} (
                id bigint NOT NULL AUTO_INCREMENT,
   				type varchar(20) NOT NULL,
   				message text NOT NULL,
   				created_at timestamp DEFAULT CURRENT_TIMESTAMP,
   				PRIMARY KEY (id),
                INDEX (created_at)
				) $charset_collate";

            dbDelta($sql);

            //settings table
            $sql = "CREATE TABLE {$this->settings_table_name} (
   				name varchar(50) NOT NULL,
   				value varchar(500) NOT NULL,
   				created_at timestamp DEFAULT CURRENT_TIMESTAMP,
   				updated_at timestamp,
   				PRIMARY KEY (name, value)
				) $charset_collate";

            dbDelta($sql);

            //acme template table
            $table_name = $this->wpdb->prefix . Constants::TABLE_TEMPLATE;

            $sql = "CREATE TABLE $table_name (
    			id varchar(50) NOT NULL,
    			template_type varchar(50) NOT NULL,
   				name varchar(50) NOT NULL,
   				description text,
   				short_description text,
   				admission_type varchar(50),
   				review_state varchar(50),
   				sold_quantity int,
   				available tinyint NOT NULL DEFAULT 1,
   				member_only_event tinyint NOT NULL DEFAULT 0,
   				starts_at timestamp,
   				ends_at timestamp,
   				created_at timestamp DEFAULT CURRENT_TIMESTAMP,
   				updated_at timestamp,
   				PRIMARY KEY (id)
				) $charset_collate";

            dbDelta($sql);

            //acme template calendar table
            //use this for by-the-day searches
            //populated from /v1/b2c/event/templates/{template_id}/calendar
            $table_name = $this->wpdb->prefix . Constants::TABLE_TEMPLATE_CALENDAR;

            $sql = "CREATE TABLE $table_name (
    			id varchar(50) NOT NULL,
    			template_id varchar(50) NOT NULL,
   				event_date timestamp NOT NULL,
   				name varchar(100),
   				active tinyint NOT NULL DEFAULT 0,
   				created_at timestamp DEFAULT CURRENT_TIMESTAMP,
   				updated_at timestamp,
                PRIMARY KEY (id),
                INDEX (template_id, event_date)
				) $charset_collate";

            dbDelta($sql);

            //acme event calendar table
            //populated from /v2/b2b/event/instances/statements
            $table_name = $this->wpdb->prefix . Constants::TABLE_EVENT_CALENDAR;

            $sql = "CREATE TABLE $table_name (
    			id varchar(50) NOT NULL,
    			template_id varchar(50) NOT NULL,
   				starts_at datetime,
   				ends_at datetime,
   				name varchar(100),
   				schedule_name varchar(100),
   				active tinyint NOT NULL DEFAULT 0,
   				status varchar(50),
   				event_type varchar(50),
   				admission_type varchar(50),
   				sold int,
   				available int,
   				checked_in int,
   				created_at timestamp DEFAULT CURRENT_TIMESTAMP,
   				updated_at timestamp,
   				PRIMARY KEY (id),
   				INDEX (template_id, starts_at)
				) $charset_collate";

            dbDelta($sql);
        } catch (Exception $exception) {
            Log::exception($exception);
        }

        $message = "Acme Ticking Plugin Activation created tables. Plugin DB Version " . Database::PLUGIN_DB_VERSION;

        Log::info($message);

        //set the database version option
        add_option( self::PLUGIN_DB_OPTION, Database::PLUGIN_DB_VERSION);
	}

    /**
     * populate settings data
     *
     * @return void
     */
    function install_config_data(): void
    {
		//initial settings
		/*
 *          api_key varchar(100),
		   sync_active char(1) NOT NULL DEFAULT 'N',
		   sync_interval int NOT NULL DEFAULT 15,
		   api_base_url varchar(255) NOT NULL DEFAULT 'https://api.acmeticketing.com',
 */
        $now = Carbon::now()->toDateTimeString();

        $this->wpdb->insert(
            $this->settings_table_name,
            array(
                'name' => Constants::SETTING_SYNC_ACTIVE,
                'value' => 'Y',
                'created_at' => $now,
                'updated_at' => $now,
            )
        );

        $this->wpdb->insert(
            $this->settings_table_name,
            array(
                'name' => Constants::SETTING_SYNC_INTERVAL,
                'value' => '15',
                'created_at' => $now,
                'updated_at' => $now,
            )
        );

        $this->wpdb->insert(
            $this->settings_table_name,
            array(
                'name' => Constants::SETTING_API_KEY,
                'value' => '',
                'created_at' => $now,
                'updated_at' => $now,
            )
        );

        $this->wpdb->insert(
            $this->settings_table_name,
            array(
                'name' => Constants::SETTING_API_BASE_URL,
                'value' => 'https://api.acmeticketing.com/',
                'created_at' => $now,
                'updated_at' => $now,
            )
        );
	}

    public function uninstall_tables(): void
    {
        $tables = [
            $this->settings_table_name,
            $this->wpdb->prefix . Constants::TABLE_SETTINGS,
            $this->wpdb->prefix . Constants::TABLE_EVENT_CALENDAR,
            $this->wpdb->prefix . Constants::TABLE_TEMPLATE_CALENDAR,
            $this->wpdb->prefix . Constants::TABLE_TEMPLATE,
            $this->wpdb->prefix . Constants::TABLE_LOG,
        ];

        foreach ($tables as $tablename) {
            $this->wpdb->query("DROP TABLE IF EXISTS $tablename");
        }

        delete_option(self::PLUGIN_DB_OPTION);
    }
}