<?php

namespace SeaportAcmeTicketing;

class Admin {


	public static function acme_ticketing_admin_page()
    {
		echo '<div class="wrap">
			<h2>Acme Ticketing Integration</h2>
		</div>';

	}

    public static function acme_ticketing_events_page()
    {
        echo '<div class="wrap">
			<h2>Acme Events List</h2>
		</div>';
    }

    public static function acme_ticketing_config_page()
    {
        echo '<div class="wrap">
			<h2>Acme Ticketing Settings</h2>
		</div>';

        $settings = Database::getSettings();

        $html = '<table width="400"><tr><th width="33%">Setting</th><th>Value</th></tr>';
        foreach ($settings as $key=>$value) {
            $html .= "<tr><td>{$key}</td><td>{$value}</td></tr>";
        }
        $html .= '</table>';

        echo $html;
    }

    public static function acme_ticketing_sync_page()
    {
        echo '<div class="wrap">
			<h2>Acme Ticketing Settings</h2>
		</div>';

        require_once (__DIR__ . '/../partials/js_run_sync.php');

    }

    public static function acme_ticketing_log_page()
    {
        echo '<div class="wrap">
			<h2>Acme Ticketing System Log</h2>
		</div>';

        require_once (__DIR__ . '/../partials/activity_log.php');
    }
}