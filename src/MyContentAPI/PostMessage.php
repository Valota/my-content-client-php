<?php
/**
 * User: Mikko Korpelainen <mikko@valota.live>
 * User: Jukka Korpelainen <jukka@valota.live>
 * Date: 4.1.2021
 * Time: 9.58
 */

namespace Valota\MyContentAPI;


class PostMessage extends MessageBase {

	private $media = '';

	/**
	 * @inheritDoc
	 */
	public function validate(): bool {
		return ($this->getTitle() || $this->getMessage() || $this->getMedia());
	}

	/**
	 *
	 * get media file path
	 *
	 * @return string
	 */
	public function getMedia(): string {
		return $this->media;
	}


	/**
	 * Set media for the post
	 *
	 * @param string $filepath abs filepath to media /path/to/image.jpg
	 *
	 * @return $this
	 * @throws \Exception if media mime type is incorrect or file is not found
	 */
	public function setMedia(string $filepath): self {
		$media = trim($filepath);

		if (!$media) {
			$this->media = '';
		} else {
			if (!file_exists($media)) {
				throw new \Exception('File not found: ' . $media);
			}

			$mime = mime_content_type($media);

			if (strpos($mime, 'image/') !== 0 && strpos($mime, 'video/') !== 0 && $mime !== "application/pdf") {
				throw new \Exception('Invalid mimetype for the media file, we accept image/*, video/* and application/pdf: ' . $mime);
			}

			$this->media = $media;
		}

		return $this;
	}
}