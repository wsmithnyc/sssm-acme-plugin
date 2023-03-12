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
}
