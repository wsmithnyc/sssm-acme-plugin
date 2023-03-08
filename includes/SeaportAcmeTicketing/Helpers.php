<?php

namespace SeaportAcmeTicketing;

class Helpers {
    public static function getTableSortDirection(?string $default = 'desc')
    {
        $sortDirection = $_GET['order'] ?? $default;
        return ($sortDirection == 'desc') ? 'desc' : 'asc';
    }

    public static function getTableSortColumn(
        ?array  $validDbColumns = [],
        ?string $defaultColumn = 'id'
    ) {
        $sortColumn = $_GET['orderby'] ?? $defaultColumn;

        return (in_array(
            $sortColumn,
            $validDbColumns
        )) ? $sortColumn : $defaultColumn;
    }

    public static function getTablePage(): int
    {
        $paged = $_GET['paged'] ?? '1';

        if ((empty($paged) || !is_numeric($paged))) {
            return 1;
        }

        return (int)$paged;
    }

    public static function getTotalPagesFromRecordCount(
        int $count,
        int $perPage
    ): int {
        if ($count <= $perPage) {
            return 1;
        }

        return (int)ceil($count / $perPage);
    }

    /**
     * Get the URL for the Book Now buttons
     * Provide template id to link to specific event
     *
     * @param $templateId
     * @return string
     */
    public static function getBookingUrl($templateId): string
    {
        //temp URL
       return "https://buy.acmeticketing.com/orders/548/calendar?eventId={$templateId}&cart";
    }
}