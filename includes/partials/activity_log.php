<?php

use SeaportAcmeTicketing\Database;
use SeaportAcmeTicketing\Helpers;
use SeaportAcmeTicketing\LogTable;

//display the wp admin table, see class LogTable for details
$table = new LogTable();
$table->prepare_items();
$table->display();
