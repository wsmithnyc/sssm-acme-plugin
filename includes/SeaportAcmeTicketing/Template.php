<?php

namespace SeaportAcmeTicketing;

class Template {
	public array $pages = [
		'Events' => 'events.php',
		'Sync Data' => 'sync.php',
		'Setup' => 'setup.php'
	];
	public static function header()
	{
		$html = "<div><h2>Acme Ticketing</h2></div>";

		return $html;
	}


}