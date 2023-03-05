<?php

namespace SeaportAcmeTicketing;



use Envira\Utils\Exception;

class Controller {
    protected $database;
    protected $apiClient;
    protected $dataImport;

    public function __construct()
    {
        $this->database = new Database();
        $this->apiClient = new ApiClient();
        $this->dataImport = new AcmeDataImport($this->database);
    }

    /**
     * Sync the Acme Data
     *
     * Returns an array with each sub-process status
     *
     * @return array
     */
    public function syncAcmeData(): array
    {
        $ret['template_status'] = $this->syncTemplateData();

        $ret['template_calendar_status'] = $this->syncTemplateCalendar();

        $ret['event_calendar_status'] = $this->syncEventCalendar();

        return $ret;
    }

    /**
     * For Public Request Usage
     * Echos the JSON response from syncing data
     *
     * @return void
     */
    public function syncAcmeDataRequest()
    {
        echo json_encode($this->syncAcmeData());
    }

    /**
     * Sync the Template data
     * This will download the templates and save to the database.
     * Existing records will be updated, new records will be created.
     *
     * @return bool
     */
    protected function syncTemplateData(): bool
    {
        try {
            $errors = 0;

            $response = $this->apiClient->getTemplates();

            $response = json_decode($response);

            if (is_array($response)) {
                $errors = $this->dataImport->syncTemplatesData($response);
            }

            if (!empty($errors)) {
                Log::warning("syncTemplateData experience {$errors} errors.");
            }

            return ($errors == 0);
        } catch (Exception $exception) {
            Log::exception($exception);
            return false;
        }
    }

    /**
     * Syncs the Template Calendar
     * The Template Calendar lists the days for a template, but not times
     * This data can be used to determine if a particular event will occur
     * on a given day
     *
     * @return bool
     */
    protected function syncTemplateCalendar(): bool
    {
        ///v1/b2c/event/templates/{63dec5f6176b2e180764bc6a}/calendar
        /// The templateID is provided as a route parameter to the Acme API
        try {
            $templateIds = $this->database->getTemplateIds();

            foreach ($templateIds as $templateId) {
                $response = $this->apiClient->getCalendarByTemplateId($templateId);

                $response = json_decode($response);

                if (!empty($response->days)) {
                    //days is an array of all the day objects for the given template
                    foreach ($response->days as $day) {
                        $this->dataImport->saveTemplateCalendarByTemplateId($day, $templateId);
                    }
                }
            }

            return true;
        } catch (Exception $exception) {
            Log::exception($exception);
        }

        return false;
    }

    /**
     * Sync the Event Calendar. The event calendar gives granualar details
     * on the schedule for an event.
     *
     * @param int|null $page
     * @return bool
     */
    protected function syncEventCalendar(?int $page = 1): bool
    {
        // page=3&sortField=startTime&pageSize=200
        // /v2/b2b/event/instances/statements

        $params = [
            'sortField' => 'startTime',
            'pageSize' => 200,
            'page' => $page,
        ];

        try {
            $response = $this->apiClient->getEventCalendarStatements($params);

            $response = json_decode($response);

            $list = $response?->list ?? [];

            $maxPages = $this->calcMaxPages($response);

            if (!empty($list)) {
                foreach ($list as $item) {
                    $this->dataImport->saveEventCalendar($item);
                }

                if ($page <= $maxPages) {
                    $this->syncEventCalendar($page + 1);
                }
            }

        } catch (Exception $exception) {
            Log::exception($exception);
            return false;
        }

        return true;
    }

    protected function calcMaxPages(object $response)
    {
        if (empty($response->pagination)) {
            return 1;
        }

        if (
            empty($response->pagination->page) ||
            empty($response->pagination->pageSize) ||
            empty($response->pagination->count)
        ) {
            return 1;
        }

        return ceil($response->pagination->count / $response->pagination->pageSize);
    }


    public function getTemplateList()
    {

    }

    public function getCalendarByTemplateId(string $templateId)
    {

    }

    public function getUnassignedTemplates()
    {

    }



}