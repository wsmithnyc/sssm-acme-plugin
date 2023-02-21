<?php

namespace SeaportAcmeTicketing;

use wpdb;
use function dbDelta;
class Activation {
	public $jal_db_version = '1.0';
	public wpdb $wpdb;
	public function __construct(&$wpdb)
	{
		$this->wpdb = $wpdb;
	}

	function jal_install()
	{
		$charset_collate = $this->wpdb->get_charset_collate();

		//settings table
		$table_name = $this->wpdb->prefix . 'acme_ticketing_settings';

		$sql = "CREATE TABLE $table_name (
   				name varchar(50) NOT NULL,
   				value varchar(500) NOT NULL,
   				created_at timestamp DEFAULT CURRENT_TIMESTAMP,
   				updated_at timestamp,
   				PRIMARY KEY (name, value)
				) $charset_collate";

		dbDelta( $sql );


		//acme template table
		$table_name = $this->wpdb->prefix . 'acme_ticketing_templates';

		$sql = "CREATE TABLE $table_name (
    			id varchar(50) NOT NULL PRIMARY,
    			type varchar(50) NOT NULL,
   				name varchar(50) NOT NULL,
   				description text,
   				short_description text,
   				review_state varchar(50),
   				sold_quantity int,
   				available tinyint NOT NULL DEFAULT 1,
   				created_at timestamp DEFAULT CURRENT_TIMESTAMP,
   				updated_at timestamp,
				) $charset_collate";

		dbDelta( $sql );

		//acme event table


		//acme calendar table
	}

	function jal_install_data()
	{
		//initial settings
		/*
 * api_key varchar(100),
		   sync_active char(1) NOT NULL DEFAULT 'N',
		   sync_interval int NOT NULL DEFAULT 15,
		   api_base_url varchar(255) NOT NULL DEFAULT 'https://api.acmeticketing.com',
 */
	}
}