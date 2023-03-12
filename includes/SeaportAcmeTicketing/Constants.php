<?php

namespace SeaportAcmeTicketing;

class Constants
{
    //setting names
    const SETTING_API_BASE_URL = 'api_base_url';
    const SETTING_API_KEY = 'api_key';
    const SETTING_SYNC_ACTIVE = 'sync_active';
    const SETTING_SYNC_INTERVAL = 'sync_interval';
    const SETTING_TICKET_BASE_URL = 'ticket_base_url';
    const SETTING_ACME_CUSTOMER_ID = 'acme_customer_id';

    //log types
    const LOG_INFO = 'info';
    const LOG_WARNING = 'warning';
    const LOG_ERROR = 'error';
    const LOG_API_ERROR = 'api-error';
    const LOG_DEBUG = 'debug';

    //tables
    const TABLE_TEMPLATE = 'acme_ticketing_templates';
    const TABLE_TEMPLATE_CALENDAR = 'acme_ticketing_template_calendar';
    const TABLE_EVENT_CALENDAR = 'acme_ticketing_event_calendar';
    const TABLE_LOG = 'acme_ticketing_log';
    const TABLE_SETTINGS = 'acme_ticketing_settings';
    const TABLE_SYNC_LOG = 'acme_ticketing_sync_log';

    //api
    const API_AUTH_HEADER = 'x-acme-api-key';

    //posts
    const SSSM_POST_TYPE = 'sssm-page';
    const CUSTOM_FIELD_TEMPLATE = 'acme-template-id';
    const CUSTOM_FIELD_BOOK_NOW = 'book-now-url';
    const CUSTOM_FIELD_HIDE_EVENT_TEXT = 'hide-event-text';
    const CUSTOM_FIELD_EVENT_TITLE = 'event-title';
    const CUSTOM_FIELD_EVENT_SHORT_DESC = 'event-short-desc';
    const CUSTOM_FIELD_EVENT_DESC = 'event-desc';
    const CUSTOM_FIELD_EVENT_DATES = 'event-date-list';
    const WP_LIST_CUSTOM_EVENT_COLUMN = 'sssm_acme_event_link';
    const WP_TAG_THIS_WEEK = 'This Week';

    //Acme Admin
    const ACME_BACKOFFICE_BASE_URL = 'https://backoffice.acmeticketing.com';
}