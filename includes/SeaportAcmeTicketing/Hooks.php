<?php

namespace SeaportAcmeTicketing;

class Hooks
{
    public static function add_sssm_posts_event_columns(array $columns): array
    {
        $columns[Constants::WP_LIST_CUSTOM_EVENT_COLUMN] = __('Acme Event', 'seaport_museum_acme_ticketing');

        return $columns;
    }

    public static function display_acme_event_custom_column(string $column, ?int $post_id = null): void
    {
        if ($column == Constants::WP_LIST_CUSTOM_EVENT_COLUMN) {
            $id = get_post_meta( $post_id, Constants::CUSTOM_FIELD_TEMPLATE, true );

            if (!empty($id)) {
                $url = AcmeUrl::getBackofficeTemplateUrl( trim( $id ) );
                echo "<a target='_blank' href='$url'><span class='dashicons dashicons-tickets-alt'></span> <span class='dashicons dashicons-external'></span></a>";
            }
        }
    }
}