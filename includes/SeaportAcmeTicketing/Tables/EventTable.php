<?php

namespace SeaportAcmeTicketing\Tables;

use SeaportAcmeTicketing\Database;
use SeaportAcmeTicketing\Helpers;
use WP_List_Table;

class EventTable extends WP_List_Table
{
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
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns(): array
    {
        return [
            'id' => __('Template Id'),
            'name' => __('Event Name'),
            'short_description' => __('Short Description'),
            'linked_posts' => __('Linked Posts'),
            'admission_type' => __('Admission Type'),
            'review_state' => __('Status'),
            'starts_at' => __('Starts'),
            'ends_at' => __('Runs Until'),
        ];
    }

    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns(): array
    {
        return [
            'id' => ['id', true],
            'name' => ['name', false],
            'admission_type' => ['admission_type', false],
            'review_state' => ['review_state', false],
            'starts_at' => ['starts_at', false],
            'ends_at' => ['ends_at', false],
        ];
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'linked_posts':
            case 'id':
            case 'name':
            case 'short_description':
            case 'admission_type':
            case 'review_state':
            case 'starts_at':
            case 'ends_at':
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

        $this->_column_headers = $this->get_column_info();

        $sortable = $this->get_sortable_columns();

        $safeColumns = array_keys($sortable);

        /* -- Sort Ordering parameters -- */
        $sortDirection = Helpers::getTableSortDirection();
        $sortColumn = Helpers::getTableSortColumn($safeColumns);

        /* -- Pagination parameters -- */
        //Number of elements in your table?
        //return the total number of affected rows
        $totalItems = Database::getTemplateDataRowCount();
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
        $this->items = (new Database())->getEventTemplates($page, $perPage, $sortColumn, $sortDirection);
    }

    public function no_items() {
        _e( 'No log events available.');
    }
}