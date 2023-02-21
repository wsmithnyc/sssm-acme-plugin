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
	public bool $verbose_logging_mode = false;

	public function __construct()
	{
		$this->client = new Client();

		//get base url from settings

		//get api key from settings


	}

	//*********************************  Acme API Endpoints **********************************/

	public function getTemplates()
	{
		$endpoint =  '/v1/b2b/event/template';
	}

	public function getEvents()
	{

	}

	public function getCalendarByTemplateId(string $templateId)
	{
		if (empty($templateId)) {
			return null;
		}

		$endpoint =  "/v1/b2c/event/templates/{$templateId}/calendar";
	}

	public function getNextAvailableEventByTemplateId(string $templateId)
	{
		if (empty($templateId)) {
			return null;
		}

		$endpoint = "/v1/b2c/events/{$templateId}/nextAvailable";
	}

	public function getAcmeData(string $url)
	{




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
				'x-acme-api-key' => $this->apiKey,
				'Host' => $this->baseUrl,
				'Accept' => 'application/json',
				'Content-Type' => 'application/json',
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
			$log_entry = "ACME Ticketing request. Method: $method. Requested URL: $url. Options: $options_string. Response code: $status_code.";
			$log_entry = str_replace($this->apiKey, '[[API-KEY-REDACTED]]', $log_entry);
			$this->log_data[] = $log_entry;
		}

		return $response;
	}

}