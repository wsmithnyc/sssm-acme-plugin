<?php

namespace SeaportAcmeTicketing\Tables;

use SeaportAcmeTicketing\Database;
use SeaportAcmeTicketing\Helpers;
use WP_List_Table;

class EventCalendarTable extends WP_List_Table
{
    protected Database $database;
    protected array $filters = [];

    function __construct()
    {
        parent::__construct(
            [
                'singular' => 'wp_list_text_link', //Singular label
                'plural' => 'wp_list_test_links',
                //plural label, also this well be one of the table css class
                'ajax' => false //We won't support Ajax for this table
            ]
        );

        $this->database = new Database();
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns(): array
    {
        return [
            'event_date' => __('Date'),
            'name' => __('Event Name'),
            'active' => __('Active'),
            'id' => __('Template Id'),
        ];
    }

    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns(): array
    {
        return [
            'id' => ['id', false],
            'name' => ['name', false],
            'event_date' => ['event_date', true],
            'active' => ['active', false],
        ];
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'active':
                return ($item[$column_name] == 1) ? "Yes" : "No";
            case 'id':
            case 'name':
            case 'event_date':
            default:
                return $item[$column_name];
        }
    }

    /**
     * Prepare the table with different parameters, pagination, columns and
     * table elements
     */
    function prepare_items()
    {
        global $_wp_column_headers;
        $screen = get_current_screen();

        $this->getFilters();

        $this->_column_headers = $this->get_column_info();

        $sortable = $this->get_sortable_columns();

        $safeColumns = array_keys($sortable);

        /* -- Sort Ordering parameters -- */
        $sortDirection = Helpers::getTableSortDirection('asc');
        $sortColumn = Helpers::getTableSortColumn($safeColumns, 'event_date');

        /* -- Pagination parameters -- */
        //Number of elements in your table?
        //return the total number of affected rows
        $totalItems = $this->database->getTemplateCalendarDataRowCount($this->filters);
        //How many to display per page?
        $perPage = 25;
        //Which page is this?
        $page = Helpers::getTablePage();
        //Calculated
        $totalPages = Helpers::getTotalPagesFromRecordCount(
            $totalItems,
            $perPage
        );

        /* -- Register the pagination -- */
        //The pagination links are automatically built according to those parameters
        $this->set_pagination_args(
            [
                "total_items" => $totalItems,
                "total_pages" => $totalPages,
                "per_page" => $perPage,
            ]
        );

        $hidden = array();
        $primary  = 'id';

        /* -- Register the Columns -- */
        $columns = $this->get_columns();
        $_wp_column_headers[$screen->id] = $columns;

        $this->_column_headers = array($columns, $hidden, $sortable, $primary);

        /* -- Fetch the items -- */
        $this->items = $this->database->getTemplateCalendar(
            $page,
            $perPage,
            $sortColumn,
            $sortDirection,
            $this->filters);
    }

    public function no_items() {
        _e( 'No log events available.');
    }

    protected function getFilters()
    {
        $template_id = $_GET['template_id'] ?? null;
        $template_id = substr($template_id, 0, 30);

        if (!empty($template_id)) {
            $this->filters['template_id'] = $template_id;
        }

        $active = $_GET['active'] ?? null;
        $active = substr($active, 0, 1);

        if ($active == '0' || $active == '1') {
            $this->filters['active'] = (int)$active;
        }
    }
}