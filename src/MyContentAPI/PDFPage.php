<?php


namespace Valota\MyContentAPI;


class PDFPage {


	/**
	 *
	 * @var int Page number for new posts and page id for edited posts
	 */
	public $pageId;

	public $displayTime = -1;

	public $visible = true;

	/**
	 * PDFPage constructor.
	 *
	 * @param int $pageId ID of the page when editing message or page number when creating a new one (pages starts at 1).
	 * @param int $displayTime Display time of the page. Set to 0 to unset
	 * @param bool $visible should this page be visible?
	 */
	public function __construct(int $pageId, int $displayTime = -1, bool $visible = true) {
		$this->pageId=$pageId;

		if($displayTime === 0) {
			$this->displayTime=0;
		} else {
			$this->displayTime=PostMessage::formatDisplayTime($displayTime);
		}
		$this->visible = $visible;

	}

	public function toArray($edit = false):array {
		$ret = [];
		if($edit) {
			$ret['page_id'] = $this->pageId;
		} else {
			$ret['page'] = $this->pageId;
		}

		if($this->displayTime !== -1) {
			$ret['display_time'] = $this->displayTime;
		}
		$ret['visible'] = $this->visible;

		return $ret;
	}
}