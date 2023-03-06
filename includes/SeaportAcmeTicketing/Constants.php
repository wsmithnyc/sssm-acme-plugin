<?php

namespace SeaportAcmeTicketing;

class Constants
{
    //setting names
    const SETTING_API_BASE_URL = 'api_base_url';
    const SETTING_API_KEY = 'api_key';
    const SETTING_SYNC_ACTIVE = 'sync_active';
    const SETTING_SYNC_INTERVAL = 'sync_interval';

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

}