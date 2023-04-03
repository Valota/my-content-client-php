![Valotalive Logo](https://store.valotalive.com/img/valotalive_logo.png)

# Valotalive - PHP Client for My Content API

This is a helper library for [Valotalive](https://valota.live) Digital
Signage [My Content API](https://github.com/Valota/my-content-api). You have to have at least
one [My Content](https://valota.live/apps/my-content/) activated in our system to use this library.

## Requirements

See *require* from [composer.json](composer.json)

## Installation

Use [Composer](https://getcomposer.org/)  
`composer require valota/my-content-client`

## Usage

```php
// Initialize your client
$myClient = new Valota\MyContentAPI\Client(_API_KEY, _API_SECRET);


// get basic information
$response =$myClient->information();

// Post
$postMessage = new Valota\MyContentAPI\PostMessage();
$postMessage->setTitle('Title'); 
$postMessage->setMessage('Message');
$postMessage->setMedia('/path/to/image.or.video.jpg'); 
$postMessage->setDisplayTime(10); //seconds 
$postMessage->setSchedule([["from"=>1670615272, "to"=>1680615273]]);
// All are optional, but post has to have at least one of title, message or media.
$response = $myClient->post($postMessage);
//$response will be id of the new message. e.g. 123

// Edit
$editMessage = new Valota\MyContentAPI\EditMessage(174);
$editMessage->setTitle('Edited title'); // empty string unsets
$editMessage->setMessage('Edited message'); // empty string unsets
$editMessage->setDisplayTime(0); // 0 unsets
$postMessage->setSchedule([]); // empty array unsets
// All are optional. Only changes the values that are set.
$response = $myClient->edit($editMessage);


// List all messages
$response =$myClient->list($archive = false, $page = null);

// Get one message
$response =$myClient->get($message_id);

// Archive a message
$response =$myClient->archive($message_id);

// Restore a message from the archive
$response =$myClient->restore($message_id);

// Delete a message permanently
$response =$myClient->delete($message_id);
```
>API description has more detailed information about responses and arguments at https://github.com/Valota/my-content-api 

## Links

- **Valotalive**: https://valota.live
- **My Content Channel**: https://valota.live/apps/my-content/
- **My Content API description**: https://github.com/Valota/my-content-api