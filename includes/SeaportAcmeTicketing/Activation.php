<?php

namespace SeaportAcmeTicketing;

use Envira\Utils\Exception;
use wpdb;
use Carbon\Carbon;
use function dbDelta;


class Activation {
	const PLUGIN_DB_OPTION = 'acme_ticketing_db_version';
    const PLUGIN_DB_VERSION = '1.0';

	public wpdb $wpdb;

    public string $settings_table_name;


	public function __construct()
	{
        //WordPress database class
        global $wpdb;

		$this->wpdb = $wpdb;

        //settings table
        $this->settings_table_name = $this->wpdb->prefix . Database::SETTINGS_TABLE_BASE_NAME;
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
    function install_tables()
	{
		$charset_collate = $this->wpdb->get_charset_collate();

        try {
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
            $table_name = $this->wpdb->prefix . 'acme_ticketing_templates';

            $sql = "CREATE TABLE $table_name (
    			id varchar(50) NOT NULL,
    			template_type varchar(50) NOT NULL,
   				name varchar(50) NOT NULL,
   				description text,
   				short_description text,
   				review_state varchar(50),
   				sold_quantity int,
   				available tinyint NOT NULL DEFAULT 1,
   				member_only_event tinyint NOT NULL DEFAULT 0,
   				starts__at timestamp,
   				ends_at timestamp,
   				created_at timestamp DEFAULT CURRENT_TIMESTAMP,
   				updated_at timestamp,
   				PRIMARY KEY (id)
				) $charset_collate";

            dbDelta($sql);

            //acme template calendar table
            //use this for by-the-day searches
            //populated from /v1/b2c/event/templates/{template_id}/calendar
            $table_name = $this->wpdb->prefix . 'acme_ticketing_template_calendar';

            $sql = "CREATE TABLE $table_name (
    			id varchar(50) NOT NULL,
    			template_id varchar(50) NOT NULL,
   				event_date timestamp NOT NULL,
   				name varchar(100),
   				active tinyint NOT NULL DEFAULT 0,
   				created_at timestamp DEFAULT CURRENT_TIMESTAMP,
   				updated_at timestamp,
                PRIMARY KEY (id)
				) $charset_collate";

            dbDelta($sql);

            //acme event calendar table
            //populated from /v2/b2b/event/instances/statements
            $table_name = $this->wpdb->prefix . 'acme_ticketing_event_calendar';

            $sql = "CREATE TABLE $table_name (
    			id varchar(50) NOT NULL,
    			template_id varchar(50) NOT NULL,
   				starts_at datetime,
   				ends_at datetime,
   				name varchar(100),
   				active tinyint NOT NULL DEFAULT 0,
   				event_type varchar(50),
   				adminission_type varchar(50),
   				sold int,
   				available int,
   				checked_in int,
   				created_at timestamp DEFAULT CURRENT_TIMESTAMP,
   				updated_at timestamp,
   				PRIMARY KEY (id)
				) $charset_collate";

            dbDelta($sql);
        } catch (Exception $exception) {
            die( $exception->getMessage());
        }

        //set the database version option
        add_option( self::PLUGIN_DB_OPTION, self::PLUGIN_DB_VERSION);
	}

    /**
     * populate settings data
     *
     * @return void
     */
    function install_config_data()
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
            $this->wpdb->prefix . "acme_ticketing_event_calendar",
            $this->wpdb->prefix . "acme_ticketing_templates",
            $this->wpdb->prefix . "acme_ticketing_event_calendar",
        ];

        foreach ($tables as $tablename) {
            $this->wpdb->query("DROP TABLE IF EXISTS $tablename");
        }

        delete_option(self::PLUGIN_DB_OPTION);
    }
}