<?php

use SeaportAcmeTicketing\Database;
use SeaportAcmeTicketing\Helpers;

function acme_ticketing_query_vars($qvars ) {
    $qvars[] = 'data-page';
    return $qvars;
}

add_filter( 'query_vars', 'acme_ticketing_query_vars' );


$page = get_query_var('data-page', 1);

$data = Database::getLogData($page);

echo "<p>Log Entries</p>";

echo Helpers::queryResultsToHtml($data);
