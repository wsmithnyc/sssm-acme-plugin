<?php

namespace SeaportAcmeTicketing;

use Carbon\Carbon;
use wpdb;

class AcmeDataImport {
    protected Database $database;

    public function __construct(?Database $database = null)
    {
        $this->database = (empty($database)) ? new Database() : $database;
    }

    /**
     * Returns the number of errors encountered.
     * 0 errors would be 100% successful
     *
     * @param array|object|null $templates
     *
     * @return int
     */
    public function syncTemplatesData(array|object|null $templates = []): int
    {
        $errors = 0;

        if (empty($templates)) {
            return 0;
        }

        foreach($templates as $template) {
            $ret = $this->saveTemplateApiData($template);

            if (!$ret) {
                $errors++;
            }
        }

        return $errors;
    }

	public function saveTemplateApiData(object $apiData): bool
    {
        return $this->database->saveTemplate($this->mapTemplateData($apiData));
	}

    public function saveTemplateCalendarByTemplateId(object $apiData, string $templateId): bool
    {
        if (!empty($apiData->date)) {
            return $this->database->saveTemplateCalendar($this->mapTemplateCalendarData($apiData, $templateId));
        }

        return false;
    }

	public function saveEventCalendar(object $apiData): bool
    {
        return $this->database->saveEventCalendar($this->mapEventCalendarData($apiData));
	}

    /*********************** API Data Maps *********************************/

    /**
     * Maps the API Data to the database columns
     *
     * @param object $apiData
     * @return array
     */
    protected function mapTemplateData(object $apiData): array
    {
        return [
            'id' => $apiData->id,
            'template_type' => $apiData->type,
            'name' => $apiData->name,
            'description' => $apiData->description,
            'short_description' => $apiData->shortDescription,
            'admission_type' => $apiData->admissionType,
            'review_state' => $apiData->reviewState,
            'sold_quantity' => $apiData->soldQuantity,
            'available' => $apiData->available,
            'member_only_event' => $apiData->memberOnlyEvent,
            'starts_at' => $apiData?->startTime ?? null,
            'ends_at' => $apiData?->endTime ?? null,
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];
	}

    /**
     * Maps the API Data to the database columns
     *
     * @param object $apiData
     * @return array
     */
    protected function mapEventCalendarData(object $apiData): array
    {
        return [
            'id' => $apiData->id,
            'template_id' => $apiData->templateId,
            'starts_at' => $apiData->startTime,
            'ends_at' => $apiData->endTime,
            'name' => substr($apiData->name, 0, 100),
            'schedule_name' => substr($apiData->scheduleName, 0,100),
            'active' => ($apiData->state == 'ACTIVE') ? 1 : 0,
            'status' => trim($apiData->state),
            'sold' => $apiData->sold,
            'available' => $apiData->available,
            'admission_type' => $apiData->admissionType,
            'event_type' => $apiData->type,
            'checked_in' => $apiData->checkedIn,
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];
	}

    /**
     * Maps the API Data to the database columns
     * Argument $templateId must be passed, because it was a parameter for the
     * API request but not part of the API response
     *
     * @param object $apiData
     * @param string $templateId
     * @return array
     */
    protected function mapTemplateCalendarData(object $apiData, string $templateId): array
    {
        return [
            'template_id' => $templateId,
            'event_date' => $apiData->date,
            'name' => $apiData->name,
            'active' => ($apiData->active) ? 1 : 0,
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];
	}
}