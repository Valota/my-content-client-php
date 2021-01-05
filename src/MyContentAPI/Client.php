<?php
/**
 * User: Mikko Korpelainen <mikko@valota.live>
 * User: Jukka Korpelainen <jukka@valota.live>
 * Date: 23.12.2020
 * Time: 8.28
 */

namespace Valota\MyContentAPI;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class Client {

	/**
	 * Base URL for My Content API
	 *
	 * @var string
	 */
	private $url = 'https://my-api.valota.live';

	/**
	 * Version of the API
	 *
	 * @var string
	 */
	private $version = 'v1';

	/**
	 * API key from My Content App
	 * @var string
	 */
	private $apiKey;

	/**
	 * API secret from My Content App
	 * @var string
	 */
	private $apiSecret;


	/**
	 * MyContentClient constructor.
	 *
	 * @param string $apiKey    32 char long API key
	 * @param string $apiSecret 64 char long API secret
	 *
	 * @throws \Exception If API key's or secret's length is invalid
	 */
	public function __construct(string $apiKey, string $apiSecret) {

		if (strlen($apiKey) !== 32) {
			throw new \Exception("API key should be exactly 32 characters long.");
		}

		if (strlen($apiSecret) !== 64) {
			throw new \Exception("API secret should be exactly 64 characters long.");
		}

		$this->apiKey = $apiKey;
		$this->apiSecret = $apiSecret;
	}

	/**
	 * Set base URL
	 *
	 * @param string $url
	 */
	public function setUrl(string $url) {
		$this->url = $url;
	}

	/**
	 * Set API version
	 *
	 * @param string $version
	 */
	public function setVersion(string $version) {
		$this->version = $version;
	}


	public function doRequest(string $method, string $endpoint, string $hash, array $params = []): array {
		$client = new GuzzleClient(['base_uri' => $this->url . '/' . $this->version . '/',
									'headers' => ['x-api-key' => $this->apiKey, 'x-api-hash' => $hash]]);

		try {
			$opts = [];

			if($method === 'POST' && count($params)) {
				$opts['multipart'] = $params;
			}
			$response = $client->request($method, $endpoint, $opts);
			$status = $response->getStatusCode();
			$val = json_decode($response->getBody(), true);
			if ($val === null) {
				throw new \Exception(json_last_error_msg());
			}

			if ($status === 200) {
				return $val;
			}
			throw new ResponseException($val, $status);

		} catch (RequestException $e) {
			$response = $e->getResponse();
			if(!$response) {
				throw new \Exception($e->getMessage(),0, $e);
			}

			$body = $response->getBody();
			if (!$body) {
				throw new ResponseException(['message' => 'Empty response body'], $response->getStatusCode());
			}
			$val = json_decode($response->getBody(), true);

			if ($val === null) {
				throw new \Exception(json_last_error_msg());
			}
			if (!is_array($val)) {
				throw new \Exception("Invalid JSON");
			}

			throw new ResponseException($val, $response->getStatusCode());

		}


	}


	/**
	 * List messages
	 *
	 * @param bool $archive List archive
	 * @param int|null $page page number
	 *
	 * @return array
	 *
	 * @throws \Valota\MyContentAPI\ResponseException
	 */
	public function list(bool $archive = false, $page = null): array {

		$endpoint = 'list';
		if($archive) {
			$endpoint .= '/archive/1';
		}
		if($page !== null) {
			$endpoint .= '/page/' . (int)$page;
		}
		return $this->doRequest('GET', $endpoint, hash('sha256', $this->apiSecret . $this->apiKey));
	}

	/**
	 * Get a message
	 *
	 * @param int $message_id
	 *
	 * @return array
	 * @throws \Valota\MyContentAPI\ResponseException
	 */
	public function get(int $message_id):array {

		return  $this->doRequest('GET', 'get/' . (int)$message_id, hash('sha256', $this->apiSecret . $message_id));

	}

	/**
	 *
	 * Archive a message
	 *
	 * @param int $message_id
	 *
	 * @return array
	 * @throws \Valota\MyContentAPI\ResponseException
	 */
	public function archive(int $message_id):array {
		return  $this->doRequest('POST', 'archive', hash('sha256', $this->apiSecret . $message_id), [['name' => 'message_id', 'contents' => $message_id]]);
	}

	/**
	 * Restore a message from the archive
	 *
	 * @param int $message_id
	 *
	 * @return array
	 * @throws \Valota\MyContentAPI\ResponseException
	 */
	public function restore(int $message_id):array {
		return  $this->doRequest('POST', 'restore', hash('sha256', $this->apiSecret . $message_id), [['name' => 'message_id', 'contents' => $message_id]]);
	}

	/**
	 * Permanently delete a message
	 *
	 * @param int $message_id
	 *
	 * @return array
	 * @throws \Valota\MyContentAPI\ResponseException
	 */
	public function delete(int $message_id):array {
		return  $this->doRequest('DELETE', 'delete/' . (int)$message_id, hash('sha256', $this->apiSecret . $message_id));
	}


	/**
	 * Posts a message
	 *
	 * @param \Valota\MyContentAPI\PostMessage $postMessage
	 *
	 * @return int message id of the created message
	 *
	 * @throws \Exception if message is invalid
	 */
	public function post(PostMessage $postMessage):int {

		if(!$postMessage->validate()) {
			throw new \Exception("Invalid message. Please check that is has at least title, message or media.");
		}

		$post = [];

		$title = $postMessage->getTitle();
		if($title) {
			$post[] = [
				'name' => 'title',
				'contents' => $title
			];
		}
		$msg = $postMessage->getMessage();
		if($msg) {
			$post[] = [
				'name' => 'message',
				'contents' => $msg
			];
		}
		$media = $postMessage->getMedia();
		if($media) {
			$post[] = [
				'name' => 'media',
				'contents' => fopen($media, 'r')
			];
		}

		$hash = hash('sha256', $this->apiSecret . ($media ? md5_file($media) : '') . $title. $msg);

		$durationFrom = $postMessage->getDurationFrom();
		if($durationFrom >  0) {
			$post[] = [
				'name' => 'duration_from',
				'contents' => $durationFrom
			];
		}

		$durationTo = $postMessage->getDurationTo();
		if($durationTo >  0) {
			$post[] = [
				'name' => 'duration_to',
				'contents' => $durationTo
			];
		}

		$displayTime = $postMessage->getDisplayTime();
		if($displayTime > 0) {
			$post[] = [
				'name' => 'display_time',
				'contents' => $displayTime
			];
		}
		if(count($postMessage->pages)) {
			$pages = [];
			foreach($postMessage->pages as $page) {
				$pages[] = $page->toArray();
			}
			$post[] = [
				'name' => 'pages',
				'contents' => json_encode($pages)
			];
		}

		$ret = $this->doRequest('POST', 'post', $hash, $post);

		return (int)$ret['message_id'];
	}

	/**
	 * Edit a message
	 *
	 * @param \Valota\MyContentAPI\EditMessage $editMessage
	 *
	 * @return array assoc array with message
	 *
	 * @throws \Exception if message is invalid
	 */
	public function edit(EditMessage $editMessage):array {

		if(!$editMessage->validate()) {
			throw new \Exception("Invalid message. Refuse to send.");
		}

		$post = [];
		$post[] = [
			'name' => 'message_id',
			'contents' => $editMessage->messageId
		];



		if($editMessage->titleEdited) {
			$post[] = [
				'name' => 'title',
				'contents' => $editMessage->getTitle()
			];
		}
		if($editMessage->messageEdited) {
			$post[] = [
				'name' => 'message',
				'contents' => $editMessage->getMessage()
			];
		}


		$hash = hash('sha256', $this->apiSecret . $editMessage->messageId);

		$durationFrom = $editMessage->getDurationFrom();
		if($durationFrom >=  0) {
			$post[] = [
				'name' => 'duration_from',
				'contents' => $durationFrom
			];
		}

		$durationTo = $editMessage->getDurationTo();
		if($durationTo >=  0) {
			$post[] = [
				'name' => 'duration_to',
				'contents' => $durationTo
			];
		}

		$displayTime = $editMessage->getDisplayTime();
		if($displayTime >= 0) {
			$post[] = [
				'name' => 'display_time',
				'contents' => $displayTime
			];
		}
		if(count($editMessage->pages)) {
			$pages = [];
			foreach($editMessage->pages as $page) {
				$pages[] = $page->toArray(true);
			}
			$post[] = [
				'name' => 'pages',
				'contents' => json_encode($pages)
			];
		}

		return $this->doRequest('POST', 'edit', $hash, $post);


	}

	/**
	 * Get basic information
	 *
	 * @return array
	 *
	 * @throws \Valota\MyContentAPI\ResponseException
	 */
	public function information(): array {
		return $this->doRequest('GET', 'information', hash('sha256', $this->apiSecret . $this->apiKey));
	}

}