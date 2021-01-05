<?php
/**
 * User: Mikko Korpelainen <mikko@valota.live>
 * User: Jukka Korpelainen <jukka@valota.live>
 * Date: 23.12.2020
 * Time: 12.29
 */

namespace Valota\MyContentAPI;


use Throwable;

class ResponseException extends \Exception {

	private $status;
	private $response;

	public function __construct(array $response = [], int $code = 0, Throwable $previous = null) {
		$this->status = $code;
		$this->response =$response;
		parent::__construct(json_encode($response), $code, $previous);
	}

	/**
	 * @return int
	 */
	public function getStatus(): int {
		return $this->status;
	}

	/**
	 * @return array
	 */
	public function getResponse(): array {
		return $this->response;
	}

}