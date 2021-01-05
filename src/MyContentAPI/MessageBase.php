<?php
/**
 * User: Mikko Korpelainen <mikko@valota.live>
 * User: Jukka Korpelainen <jukka@valota.live>
 * Date: 4.1.2021
 * Time: 9.58
 */

namespace Valota\MyContentAPI;


abstract class MessageBase {

	const ALLOWED_TAGS = "<b><strong><div><i><u><strike><s><del><ul><ol><li><br><em><code>";

	protected $edit = false;

	private $title = '';

	private $message = '';

	private $durationFrom = -1;

	private $durationTo = -1;

	private $displayTime = -1;

	public $pages = [];

	public $titleEdited = false;

	public $messageEdited = false;

	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * Sets the title
	 *
	 * @param string $title
	 *
	 * @return $this
	 */
	public function setTitle(string $title): self {
		$this->titleEdited = true;
		$title = substr(htmlspecialchars(trim($title), ENT_QUOTES), 0, 512);

		$this->title = $title;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getMessage(): string {
		return $this->message;
	}

	/**
	 * Sets the message
	 *
	 * @param string $message
	 *
	 * @return $this
	 */
	public function setMessage(string $message): self {
		$this->messageEdited = true;
		if(!$message) {
			$this->message = "";
		} else {
			$isEmpty = trim(strip_tags($message)) ? false : true;
			if ($isEmpty) {
				$this->message = "";
			} else {
				$this->message = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i", '<$1$2>', strip_tags(trim($message), self::ALLOWED_TAGS));
			}
		}


		return $this;
	}

	/**
	 * @return int
	 */
	public function getDurationFrom():int {
		return $this->durationFrom;
	}

	/**
	 * @param int $durationFrom unix epoch, use 0 to unset
	 */
	public function setDurationFrom(int $durationFrom): self {
		if($durationFrom === 0) {
			$this->durationFrom = 0;
		} else if($durationFrom > 0) {
			$this->durationFrom = $durationFrom;
		}

		return $this;
	}

	/**
	 * Return duration To
	 * @return int
	 */
	public function getDurationTo():int {
		return $this->durationTo;
	}

	/**
	 * @param int $durationTo unix epoch, use 0 to unset
	 *
	 * @return $this
	 */
	public function setDurationTo(int $durationTo): self {
		if($durationTo === 0) {
			$this->durationTo = 0;
		} else if($durationTo > 0) {
			$this->durationTo = $durationTo;
		}

		return $this;
	}

	/**
	 * Checks that the message is valid
	 *
	 * @return bool
	 */
	abstract public function validate():bool;


	/**
	 * @return int
	 */
	public function getDisplayTime() {
		return $this->displayTime;
	}

	/**
	 * @param int $displayTime use 0 to unset
	 */
	public function setDisplayTime(int $displayTime): self {
		if($displayTime === 0) {
			$this->displayTime = 0;
		} else {
			$this->displayTime = self::formatDisplayTime($displayTime);
		}

		return $this;
	}

	/**
	 * Add new page configuration
	 *
	 * @param \Valota\MyContentAPI\PDFPage $page
	 *
	 * @return $this
	 */
	public function addPage(PDFPage $page):self {
		$this->pages[] = $page;
		return $this;
	}

	/**
	 * Remove page configuration
	 *
	 * @param int $pageId Page's ID
	 *
	 * @return $this
	 */
	public function removePage(int $pageId):self {
		for($i =0;$i<count($this->pages); ++$i) {
			if($this->pages[$i]->pageId === $pageId) {
				array_splice($this->pages, $i, 1);
				break;
			}
		}


		return $this;
	}

	/**
	 * Format display time
	 *
	 * @param int $displayTime display time in seconds
	 *
	 * @return int
	 */
	public static function formatDisplayTime(int $displayTime): int {
		return min(max($displayTime, 4), 30);
	}



}