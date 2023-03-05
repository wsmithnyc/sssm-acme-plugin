<?php

namespace SeaportAcmeTicketing;

class Helpers {
    /**
     * Builds an HTML table from a query result set
     *
     *
     * @param array $data
     * @param string|null $tableClass
     * @return string
     */
    public static function queryResultsToHtml(array $data, ?string $tableClass = ''): string
    {
        if (empty($data)) {
            return '';
        }

        $headerLoaded = false;
        $header = '';
        $body = '';
        $bodyRow = '';

        foreach ($data as $row) {
            foreach ($row as $key => $value) {
                if (!$headerLoaded) {
                    $column = self::dbColumnToTableHeader($key);
                    $header .= "<th>$column</th>";
                }

                $value = trim(htmlentities($value));
                $bodyRow .= "<td>$value</td>";
            }

            $body .= "<tr>$bodyRow</tr>\n";
            $headerLoaded = true;

            $bodyRow = '';
        }

        $class = (!empty($tableClass)) ? "class='$tableClass'" : '';
        return "<table {$class}><tr>{$header}</tr>\n{$body}</table>";
    }

    /**
     * Transform DB Column names to display names for table headers
     *
     * @param string $dbColumn
     * @return string
     */
    public static function dbColumnToTableHeader(string $dbColumn)
    {
        return ucwords(str_replace('_', ' ', $dbColumn));
    }
}