<?php

namespace SeaportAcmeTicketing;

use Carbon\Carbon;

class AcmeUrl
{
    public string $baseUrl;
    public string $customerId;

    public function __construct()
    {
        $this->baseUrl = $this->getBookingBaseUrl();
        $this->customerId = $this->getBookingCustomerId();
    }

    public function getEventDetailUrl(string $templateId): string
    {
        return "{$this->baseUrl}/events/{$this->customerId}/detail/{$templateId}";
    }

    public function getEventCalendarUrl(string $templateId): string
    {
        return "{$this->baseUrl}/orders/{$this->customerId}/calendar?eventId={$templateId}&cart";
    }

    public function getEventCartPageByDate(string $templateId, Carbon|string $date): string
    {
        if ($date instanceof Carbon) {
            $dt = $date->toDateTimeLocalString();
        } else {
            $dt = Carbon::parse($date)->toDateTimeLocalString();
        }

        return "{$this->baseUrl}/orders/{$this->customerId}/tickets?eventId={$templateId}&date={$dt}";
    }

    public function getEventCartPageForToday(string $templateId): string
    {
        return "{$this->baseUrl}/orders/{$this->customerId}/tickets?eventId={$templateId}";
    }

    public function getEventDetailByDate(string $templateId, Carbon|string $date): string
    {
        if ($date instanceof Carbon) {
            $dt = $date->toDateTimeLocalString();
        } else {
            $dt = Carbon::parse($date)->toDateTimeLocalString();
        }

        return "{$this->baseUrl}/events/{$this->customerId}/detail/{$templateId}?date={$dt}";
    }

    public function getGeneralCalendar(): string
    {
        return "{$this->baseUrl}/orders/{$this->customerId}/calendar";
    }

    /**
     * Get the base URL for Links
     * Provide template id to link to specific event
     *
     * @return string
     */
    public function getBookingBaseUrl(): string
    {
        return Database::getSettings( Constants::SETTING_TICKET_BASE_URL );
    }
    
    /**
     * Get the base Acme Customer ID for ticketing Links
     * This ID is used in Acme URLS to specify a particular organization
     *
     * @return string
     */
    public function getBookingCustomerId(): string
    {
        return Database::getSettings( Constants::SETTING_ACME_CUSTOMER_ID );
    }

    /**
     * Returns the Acme Backoffice deep link to the event template
     *
     * @param string $templateId
     *
     * @return string
     */
    public static function getBackofficeTemplateUrl(string $templateId): string
    {
        return Constants::ACME_BACKOFFICE_BASE_URL . "/app/templates/{$templateId}/edit/description";
    }
}
