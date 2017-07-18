<?php

require realpath(__DIR__ . '/../vendor/autoload.php');

use Nextform\Validation\Validation;
use Nextform\Config\XmlConfig;

$config = new XmlConfig(realpath(__DIR__ . '/assets/sample.xml'));
$validator = new Validation($config);

echo "<pre>";
// print_r($config);

$result = $validator->validate([
	'password' => 'pass',
	'password-validate' => 'pass',
	'description' => 'asdadsa',
	'gender' => [
		'test' => '123',
		'g1',
		'g2',
		'g3'
	]
]);

echo json_encode($result, JSON_PRETTY_PRINT);