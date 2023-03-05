<?php

namespace SeaportAcmeTicketing;

use wpdb;

class Database {
    public const PLUGIN_DB_VERSION = '1.0';
    public string $settings_table_name;
    public string $log_table_name;

    public wpdb $wpdb;

    public function __construct()
    {
        //WordPress database class
        global $wpdb;

        $wpdb->show_errors();

        $this->wpdb = $wpdb;

        $this->settings_table_name = $this->getSettingTableName();

        $this->log_table_name = $this->getLogTableName();

    }

    /************************** Data Read ************************************/
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
     * Returns a list of template ids from the templates table.
     * Template IDs are the primary key provided by Acme.
     *
     * @return array
     */
    public function getTemplateIds(): array
    {
        $list = [];

        $table = $this->getTemplateTableName();
        $sql = "SELECT id from $table";

        $data = $this->wpdb->get_results($sql);

        foreach ($data as $row) {
            $list[] = $row->id;
        }

        return $list;
    }

    //***********************  Log Report ***********************************/
    public static function getLogData(int $page)
    {
        $instance = new Database();

        $table = $instance->log_table_name;
        $sql = "SELECT id, type, message, created_at FROM {$table} ORDER BY id DESC";

        return $instance->wpdb->get_results($sql);
    }

    /************************** Data Write ***********************************/
    public function saveTemplate(array $data): bool
    {
        $table = $this->getTemplateTableName();

        $ret = $this->wpdb->replace($table, $data);

        return ($ret > 0);
    }

    public function saveTemplateCalendar(array $data): bool
    {
        $table = $this->getTemplateCalendarTableName();

        $ret = $this->wpdb->replace($table, $data);

        return ($ret > 0);
    }

    public function saveEventCalendar(array $data): bool
    {
        $table = $this->getEventCalenderTableName();

        $ret = $this->wpdb->replace($table, $data);

        return ($ret > 0);
    }



    /************************** Plugin Settings*******************************/
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
     */public static function getSettings(?string $key = null): mixed
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

    /******************* Logging Names *************************************/
    public function saveLog(string $logType, string $message): void
    {
        $this->wpdb->insert(
            $this->log_table_name,
            array(
                'type' => $logType,
                'message' => $message,
            )
        );
    }


    /******************* Table Names *************************************/
    public function getTemplateTableName()
    {
        return $this->wpdb->prefix . Constants::TABLE_TEMPLATE;
    }

    public function getTemplateCalendarTableName()
    {
        return $this->wpdb->prefix . Constants::TABLE_TEMPLATE_CALENDAR;
    }

    public function getEventCalenderTableName()
    {
        return $this->wpdb->prefix . Constants::TABLE_EVENT_CALENDAR;
    }

    public function getLogTableName()
    {
        return $this->wpdb->prefix . Constants::TABLE_LOG;
    }

    public function getSettingTableName()
    {
        return $this->wpdb->prefix . Constants::TABLE_SETTINGS;
    }
}