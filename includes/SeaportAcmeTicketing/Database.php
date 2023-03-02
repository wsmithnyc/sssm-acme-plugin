<?php

namespace SeaportAcmeTicketing;

class Database {
    const SETTINGS_TABLE_BASE_NAME = 'acme_ticketing_settings';

    public string $settings_table_name;

    public function __construct()
    {
        //WordPress database class
        global $wpdb;

        $this->wpdb = $wpdb;

        $this->settings_table_name = $this->wpdb->prefix . Database::SETTINGS_TABLE_BASE_NAME;
    }

    public static function getFullSettingTableName()
    {
        return (new Database())->settings_table_name;
    }

	/**
	 * Return a list of events, joining Acme Templates, Acme Events and Acme Calendars and linked Posts
	 *
	 *
	 * @param array $filters
	 *
	 * @return void
	 */
	public function getEvents(array $filters = [])
	{

	}

	/**
	 * Returns a single event, joining Acme Templates, Acme Events and Acme Calendars and linked Posts
	 *
	 * @param string $templateId
	 *
	 * @return void
	 */
	public function getEventByTemplateId(string $templateId)
	{

	}

	public function getCalenderbyTemplateID(string $templateId)
	{

	}

    /**
     * Update the API Key Value
     *
     * @param string $value
     * @return void
     */
    public function updateApiKey(string $value): void
    {
        $this->wpdb->update(
            'table',
            array(
                'value' => trim($value),	// string
            ),
            array( 'name' => 'api_key' ),
            array(
                '%s',	// value1
            )
        );
    }


	/**
     * get the settings for the plugin
     *
     * @param string|null $key
     * @return mixed|string
     */public static function getSettings(?string $key = null)
	{
        static $settings;

        if (empty($settings)) {

            $settings = (new Database())->loadSettings();
        }

        return (empty($key)) ? $settings :  $settings[$key] ?? '';
	}

    /**
     * Loads the settings from the plugin database table
     * @return array
     */
    protected function loadSettings()
    {
        $sql = "SELECT name, value from {$this->settings_table_name}";

        $data = $this->wpdb->get_results($sql, OBJECT);

        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }
}