<?php
/**
 * User: Mikko Korpelainen <mikko@valota.live>
 * User: Jukka Korpelainen <jukka@valota.live>
 * Date: 4.1.2021
 * Time: 9.58
 */

namespace Valota\MyContentAPI;


class EditMessage extends MessageBase {

	public $messageId;



	public function __construct(int $messageId) {
		$this->edit = true;
		$this->messageId = $messageId;
	}

	/**
	 * @inheritDoc
	 */
	public function validate(): bool {
		return true;
	}


}