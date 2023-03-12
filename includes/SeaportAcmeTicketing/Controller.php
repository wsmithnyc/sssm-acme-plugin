<?php

namespace SeaportAcmeTicketing;


use Envira\Utils\Exception;

class Controller
{
    protected Database $database;
    protected ApiClient $apiClient;
    protected AcmeDataImport $dataImport;

    public function __construct()
    {
        $this->database   = new Database();
        $this->apiClient  = new ApiClient();
        $this->dataImport = new AcmeDataImport( $this->database );
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
    public function syncAcmeDataRequest(): void
    {
        echo json_encode( $this->syncAcmeData() );
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
        $errors = 0;

        $response = $this->apiClient->getTemplates();

        $response = json_decode( $response );

        if ( is_array( $response ) ) {
            $errors = $this->dataImport->syncTemplatesData( $response );
        }

        if ( ! empty( $errors ) ) {
            Log::warning( "syncTemplateData experience $errors errors." );
        }

        return ( $errors == 0 );
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

        $templateIds = $this->database->getTemplateIds();

        foreach ( $templateIds as $templateId ) {
            $response = $this->apiClient->getCalendarByTemplateId( $templateId );

            $response = json_decode( $response );

            if ( ! empty( $response->days ) ) {
                //days is an array of all the day objects for the given template
                foreach ( $response->days as $day ) {
                    $this->dataImport->saveTemplateCalendarByTemplateId( $day, $templateId );
                }
            }
        }

        return true;
    }

    /**
     * Sync the Event Calendar. The event calendar gives granular details
     * on the schedule for an event.
     *
     * @param int|null $page
     *
     * @return bool
     */
    protected function syncEventCalendar( ?int $page = 1 ): bool
    {
        // page=3&sortField=startTime&pageSize=200
        // /v2/b2b/event/instances/statements

        $params = [
            'sortField' => 'startTime',
            'pageSize'  => 200,
            'page'      => $page,
        ];

        $response = $this->apiClient->getEventCalendarStatements( $params );

        $response = json_decode( $response );

        $list = $response?->list ?? [];

        $maxPages = $this->calcMaxPages( $response );

        if ( ! empty( $list ) ) {
            foreach ( $list as $item ) {
                $this->dataImport->saveEventCalendar( $item );
            }

            if ( $page <= $maxPages ) {
                $this->syncEventCalendar( $page + 1 );
            }
        }

        return true;
    }

    /**
     * For Public Request Usage
     * Echos the JSON response from syncing post metadata
     *
     * @return string
     */
    public function syncAcmeMetaDataRequest(): string
    {
        return $this->syncPostEventData();
    }

    public function syncPostEventData(): string
    {
        $templates = $this->database->getActiveTemplates();
        $postMeta  = new PostMeta();

        $return = [];

        foreach ( $templates as $template ) {
            $postCount = $postMeta->updatePostsByTemplate( $template );
            $return[]  = [ 'id' => $template->id, 'name' => $template->name, 'posts' => $postCount ];
        }

        $html = "\n<tr><th>Template ID</th><th>Event Name</th><th>Linked Posts</th></tr>\n";

        foreach ( $return as $row ) {
            $html .= "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['posts']}</td></tr>\n";
        }

        return "<table style='width: 600px;'>$html</table>\n";
    }

    protected function calcMaxPages( object $response ): int
    {
        if (
            empty( $response->pagination ) ||
            empty( $response->pagination->page ) ||
            empty( $response->pagination->pageSize ) ||
            empty( $response->pagination->count )
        ) {
            //can't determine the max page, so assume 1 page
            return 1;
        }

        return (int) ceil( $response->pagination->count / $response->pagination->pageSize );
    }


    public function getTemplateList()
    {

    }

    public function getCalendarByTemplateId( string $templateId )
    {

    }

    public function getUnassignedTemplates()
    {

    }

    public function logSyncStatusStart( string $objectType )
    {

    }

    public function logSyncStatusStop( int $syncLogId )
    {

    }

}