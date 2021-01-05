<?php
/**
 * User: Mikko Korpelainen <mikko@valota.live>
 * User: Jukka Korpelainen <jukka@valota.live>
 * Date: 4.1.2021
 * Time: 8.54
 */

use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase {

	private $client;
	private $lipsum;

	protected function setUp(): void {
		$credFile = __DIR__ . '/../../credentials.php';
		if(!file_exists($credFile)) {
			die('credentials.php file is missing.');
		}
		require_once($credFile);
		$this->client = new Valota\MyContentAPI\Client(_API_KEY, _API_SECRET);
		$this->lipsum = new joshtronic\LoremIpsum();

		if (_API_URL) {
			$this->client->setUrl(_API_URL);
		}
		if (_API_VERSION) {
			$this->client->setVersion(_API_VERSION);
		}


	}

	public function testList(): void {
		$response = $this->client->list();
		$this->assertArrayHasKey('total', $response);
		$this->assertArrayHasKey('messages', $response);
	}

	public function testInformation(): void {
		$response = $this->client->information();
		$this->assertArrayHasKey('name', $response);
	}


	public function testPost(): array {
		$title = $this->lipsum->sentence();
		$message = $this->lipsum->sentences(5,'em');
		$post = new Valota\MyContentAPI\PostMessage();
		$post->setTitle($title);
		$post->setMessage($message);
		$message_id=$this->client->post($post);
		$this->assertIsInt($message_id);
		$this->assertGreaterThan(0, $message_id);

		return ['message_id' => $message_id, 'title' => $title, 'message' => $message];
	}

	/**
	 * @depends testPost
	 */
	public function testGet(array $args): int {

		$response =$this->client->get($args['message_id']);
		$this->assertSame($args['message_id'], $response['id']);
		$this->assertSame($args['title'], $response['title']);
		$this->assertSame($args['message'], $response['message']);
		return $args['message_id'];
	}

	/**
	 * @depends testGet
	 */
	public function testEdit(int $message_id): array {
		$title = $this->lipsum->sentence();
		$message = $this->lipsum->sentences(5,'em');
		$post = new Valota\MyContentAPI\EditMessage($message_id);
		$post->setTitle($title);
		$post->setMessage($message);
		$response=$this->client->edit($post);
		$this->assertArrayHasKey('message', $response);
		$this->assertSame("Message was updated.", $response['message']);

		return ['message_id' => $message_id, 'title' => $title, 'message' => $message];
	}


	/**
	 * @depends testEdit
	 */
	public function testGetAfterEdit(array $args): int {

		$response =$this->client->get($args['message_id']);
		$this->assertSame($args['message_id'], $response['id']);
		$this->assertSame($args['title'], $response['title']);
		$this->assertSame($args['message'], $response['message']);
		return $args['message_id'];
	}

	/**
	 * @depends testGetAfterEdit
	 */
	public function testArchive(int $message_id):int {
		$response =$this->client->archive($message_id);

		$this->assertArrayHasKey('message', $response);
		$this->assertSame("Message $message_id has been archived.", $response['message']);

		return $message_id;

	}

	/**
	 * @depends testArchive
	 */
	public function testRestore(int $message_id):int {
		$response =$this->client->restore($message_id);

		$this->assertArrayHasKey('message', $response);
		$this->assertSame("Message $message_id was restored.", $response['message']);

		return $message_id;

	}

	/**
	 * @depends testRestore
	 */
	public function testDelete(int $message_id):int {
		$response =$this->client->delete($message_id);

		$this->assertArrayHasKey('message', $response);
		$this->assertSame("Message $message_id was deleted.", $response['message']);

		return $message_id;

	}


}