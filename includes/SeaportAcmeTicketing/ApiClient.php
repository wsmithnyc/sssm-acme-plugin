<?php
namespace SeaportAcmeTicketing;

use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class ApiClient {

	public array $log_data = [];
	public string $baseUrl;
	public string $apiKey;
	protected GuzzleHttp\ClientInterface $client;
	public bool $verbose_logging_mode = true;

	public function __construct()
	{
		$this->client = new Client();

		//get base url from settings
        $this->baseUrl = Database::getSettings(Constants::SETTING_API_BASE_URL);

        if (str_ends_with($this->baseUrl, '/')) {
            $this->baseUrl = substr($this->baseUrl, 0,-1);
        }

		//get api key from settings
        $this->apiKey = Database::getSettings(Constants::SETTING_API_KEY);
    }

	//*********************************  Acme API Endpoints **********************************/

	public function getTemplates(): string
    {
		$endpoint = '/v1/b2b/event/template';

        return $this->getAcmeData($endpoint);
	}

	public function getEventCalendarStatements(?array $params = []): string
    {
        $endpoint = '/v2/b2b/event/instances/statements';

        return $this->getAcmeData($endpoint, $params);
	}

	public function getCalendarByTemplateId(string $templateId): ?string
    {
		if (empty($templateId)) {
			return null;
		}

		$endpoint =  "/v1/b2c/event/templates/{$templateId}/calendar";

        return $this->getAcmeData($endpoint);
	}

	public function getNextAvailableEventByTemplateId(string $templateId): ?string
    {
		if (empty($templateId)) {
			return null;
		}

		$endpoint = "/v1/b2c/events/{$templateId}/nextAvailable";

        return $this->getAcmeData($endpoint);
	}

	public function getAcmeData(string $endpoint, ?array $params = []): string
	{
        try {
            $response = $this->request('GET', $endpoint, $params);

            return $response->getBody();

        } catch (GuzzleException $ex) {
            Log::guzzleException($ex);
        }

        return  json_encode(['error'=>true]);
	}

    /**
     * Makes a request to the ACME Ticketing API.
     *
     * @param  string  $method
     *  The HTTP method to use for the request.
     * @param  string  $path
     *  The unique component of the API endpoint to use for this request.
     * @param  array|null  $query_params
     *  Associative array of URL query parameters to add to the request.
     * @param  array|null  $body
     *  Associative array of data to be sent as the body of the requests. This
     *  will be converted to JSON.
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function request(
        string $method,
        string $path,
        ?array $query_params = [],
        array $body = null
    ): ResponseInterface {

        $options = [
            'headers' => [
                Constants::API_AUTH_HEADER => $this->apiKey,
                //'Accept' => 'application/json',
                //'Content-Type' => 'application/json',
            ],
        ];

        if (count($query_params) > 0) {
            $options['query'] = $query_params;
        }

        if (!is_null($body)) {
            $options['json'] = $body;
        }

        $url = $this->baseUrl . $path;

        $response = $this->client->request($method, $url, $options);

        if ($this->verbose_logging_mode) {
            $status_code = $response->getStatusCode();
            $options_string = json_encode($options);
            $log_entry = "Acme API request. Method: $method. Requested URL: $url. Options: $options_string. Response code: $status_code.";
            $log_entry = str_replace($this->apiKey, '[[API-KEY-REDACTED]]', $log_entry);
            Log::debug($log_entry);
        }

        return $response;
    }
}
