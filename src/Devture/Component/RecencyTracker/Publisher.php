<?php
namespace Devture\Component\RecencyTracker;

class Publisher {

	private $publishUrl;
	private $secret;

	public function __construct($publishUrl, $secret) {
		$this->publishUrl = $publishUrl;
		$this->secret = $secret;
	}

	public function publish($resource, $version, array $data = array()) {
		$resultText = $this->makePostRequest($this->publishUrl, array(
			'secret' => $this->secret,
			'resource' => $resource,
			'version' => $version,
			'data' => json_encode($data, JSON_FORCE_OBJECT),
		));

		if ($resultText === false) {
			throw new Exception('Could not make publishing request');
		}

		$result = json_decode($resultText, true);
		if ($result === null) {
			throw new Exception(sprintf('Malformed JSON in response: `%s`', $resultText));
		}

		if (!is_array($result) || !isset($result['ok'])) {
			throw new Exception(sprintf('Invalid response data: `%s`', $resultText));
		}

		if ($result['ok']) {
			return;
		}

		$error = isset($result['error']) ? $result['error'] : 'unknown reason';
		throw new Exception(sprintf('Publishing failed: `%s`', $error));
	}

	private function makePostRequest($url, array $postData) {
		$opts = array(
			'http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query($postData)
			)
		);
		$context  = stream_context_create($opts);

		return @file_get_contents($url, false, $context);
	}

}
