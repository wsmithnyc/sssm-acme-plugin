<?php

namespace SeaportAcmeTicketing;

use Carbon\Carbon;
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
    public function select(string $preparedSql, bool $returnArray = false): array|object|null
    {
        $output = ($returnArray) ? ARRAY_A : OBJECT;

        return $this->wpdb->get_results($preparedSql, $output);
    }

    /************************** Templates ************************************/
    /**
     * Return a list of events, joining Acme Templates, Acme Events and Acme Calendars and linked PostMeta
     *
     *
     * @param $page
     * @param $perPage
     * @param $sortColumn
     * @param $sortDirection
     * @return array
     */
	public function getEventTemplates($page, $perPage, $sortColumn, $sortDirection): array
    {
        $table = $this->getTemplateTableName();
        $sql = "SELECT id, name, short_description, admission_type, review_state, starts_at, ends_at FROM {$table} ORDER BY $sortColumn $sortDirection";

        //apply paging
        if (!empty($page) && !empty($perPage)) {
            $offset = ($page - 1) * $perPage;
            $sql .= " LIMIT $perPage OFFSET $offset";
        }

        $templates = $this->select($sql, true);

        $data = [];

        foreach ($templates as $template) {
            $data[$template['id']] = $template;
            //get the posts related to this event template
            $posts = $this->getLinkedPostsByTemplateID($template['id']);
            //append the html list of posts as a new column to the data set
            $data[$template['id']]['linked_posts'] = $this->getEventTablePostsListHtmlSnippet($posts);
        }

        return $data;
	}

    protected function getEventTablePostsListHtmlSnippet($posts): string
    {
        if (empty($posts)) {
            return '';
        }

        $html = '';

        foreach ($posts as $post) {
            $url = urldecode($post->guid);
            $postTitle = $post->post_title;
            $html .= "<a href='$url' target='_blank'>$postTitle</a><br>";
        }

        return $html;
    }

    public function getTemplateDataRowCount(): ?string
    {
        $table = $this->getTemplateTableName();
        $sql = "SELECT count(*) as total_rows FROM {$table}";

        return $this->wpdb->get_var($sql);
    }

    public function getTemplateById(string $templateId): array|object|null
    {
        $table = $this->getTemplateTableName();
        $sql = "SELECT id, name, short_description, admission_type, review_state, starts_at, ends_at FROM {$table} WHERE id = %s";
        $sql = $this->wpdb->prepare($sql, $templateId);

        return $this->select($sql);
    }

    public function getActiveTemplates(): object|array|null
    {
        $table = $this->getTemplateTableName();
        $sql = "SELECT id, name, short_description, description, admission_type, review_state, starts_at, ends_at FROM {$table} WHERE review_state = %s";
        $sql = $this->wpdb->prepare($sql, 'published');

        return $this->select($sql);
    }

    /*********************************** Template Calendar ************************************************/

    public function getTemplateCalendar(
        int     $page,
        int     $perPage,
        ?string $sortColumn = 'event_date',
        ?string $sortDirection = 'asc',
        ?array  $filters = []
    ): array|object|null {
        $tableCalendar = $this->getTemplateCalendarTableName();
        $tableTemplate = $this->getTemplateTableName();

        $sql = "SELECT t.id, t.name, c.event_date, c.active 
                FROM $tableCalendar c JOIN $tableTemplate t on c.template_id = t.id";

        $sql = $this->prepareTemplateCalendarQuery($sql, $filters);

        $sql .= " ORDER BY $sortColumn $sortDirection";

        //apply paging
        if (!empty($page) && !empty($perPage)) {
            $offset = ($page - 1) * $perPage;
            $sql .= " LIMIT $perPage OFFSET $offset";
        }

        return $this->select($sql, true);
    }


    public function getTemplateCalendarDataRowCount(?array $filters = []): ?string
    {
        $table = $this->getTemplateCalendarTableName();
        $sql = "SELECT count(*) as total_rows FROM {$table} as c";

        $sql = $this->prepareTemplateCalendarQuery($sql, $filters);

        return $this->wpdb->get_var($sql);
    }

    /**
     * Dedicated function for template calendar query to handle building the filter parameters
     *
     * @param string $sql
     * @param array|null $filters
     * @return string|null
     */
    protected function prepareTemplateCalendarQuery(string $sql, ?array $filters): ?string
    {
        $queryParameters = [];

        //filter by the template_id
        if (!empty($filters['template_id'])) {
            $sql .= ' WHERE c.template_id = %s';
            $queryParameters[] = $filters['template_id'];
        }

        //filter by active, which can be '0' or '1'
        if (isset($filters['active'])) {
            $active = ($filters['active'] == 0) ? 0 : 1;
            $sql .= ' WHERE c.active = %d';
            $queryParameters[] = $active;
        }

        if (!empty($queryParameters)) {
            $sql = $this->wpdb->prepare($sql, $queryParameters);
        }

        return $sql;
    }

	/**
	 * Returns a single event, joining Acme Templates, Acme Events and Acme Calendars and linked PostMeta
	 *
	 * @param string $templateId
	 *
	 * @return void
	 */
	public function getEventByTemplateId(string $templateId)
	{

	}

    /**
     * Returns the active dates for a given event template
     * This can be used to show events in a date range criteria, with a granularity of whole days.
     *
     * @param string $templateId
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @return array|object|null
     */
    public function getCalenderByTemplateID(string $templateId, ?Carbon $from = null, Carbon $to = null): array|object|null
    {
        $filters = [$templateId];
        $criteria = 'WHERE active = 1 AND template_id = %s';

        if (!empty($from)) {
            $filters[] = $from->toDateString();
            $criteria .= ' AND event_date >= %s';
        }

        if (!empty($to)) {
            $filters[] = $to->toDateString();
            $criteria .= ' AND event_date <= %s';
        }

        $table = $this->getTemplateCalendarTableName();
        $sql = "SELECT event_date FROM $table $criteria ORDER BY event_date";
        $sql = $this->wpdb->prepare($sql, $filters);

        return $this->select($sql);
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

        $data = $this->select($sql);

        foreach ($data as $row) {
            $list[] = $row->id;
        }

        return $list;
    }


    /**
     * Get the count of events for a given template ID.
     *
     * @param string $templateId
     *
     * @return int
     */
    public function getEventCountByTemplateId(string $templateId): int
    {
        $table = $this->getTemplateCalendarTableName();
        $sql = "SELECT count(*) as event_count FROM $table WHERE template_id = %s";

        $sql = $this->wpdb->prepare($sql, $templateId);

        return (int)$this->wpdb->get_var($sql);
    }

    /************************** WordPress PostMeta Queries **********************/
    public function getLinkedPostsByTemplateID(string $templateId): array
    {
        return get_posts([
            'numberposts' => -1,
            'post_type' => Constants::SSSM_POST_TYPE,
            'meta_key' => Constants::CUSTOM_FIELD_TEMPLATE,
            'meta_value' => $templateId,
        ]);
    }

    public function getUnlinkedPosts()
    {


    }

    //***********************  Log Report ***********************************/
    public function getLogData(
        int     $page,
        int     $perPage,
        ?string $sortColumn = 'id',
        ?string $sortDirection = 'desc'
    ): array|object|null {

        $table = $this->log_table_name;
        $sql = "SELECT id, type, message, created_at FROM {$table} ORDER BY $sortColumn $sortDirection";

        //apply paging
        if (!empty($page) && !empty($perPage)) {
            $offset = ($page - 1) * $perPage;
            $sql .= " LIMIT $perPage OFFSET $offset";
        }

        return $this->select($sql, true);
    }

    /**
     * Used for pagination: get the count of log records
     *
     * @return string|null
     */
    public  function getLogDataRowCount(): ?string
    {
        $table = $this->log_table_name;
        $sql = "SELECT count(*) as total_rows FROM {$table}";

        return $this->wpdb->get_var($sql);
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

    public static function isSyncActive(): bool
    {
        $value = self::getSettings(Constants::SETTING_SYNC_ACTIVE);

        return ($value == 'Y');
    }

    /**
     * Loads the settings from the plugin database table
     * @return array
     */
    protected function loadSettings(): array
    {
        $sql = "SELECT name, value from {$this->settings_table_name}";

        $data = $this->select($sql);

        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    /***************************** Logging  *************************************/
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


    /***************************** Table Names *************************************/
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

    public function getSyncLogTableName()
    {
        return $this->wpdb->prefix . Constants::TABLE_SYNC_LOG;
    }
}