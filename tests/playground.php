<?php

require realpath(__DIR__ . '/../vendor/autoload.php');

use Nextform\Validation\Validation;
use Nextform\Config\XmlConfig;
use Nextform\Renderer\Renderer;

// $config = new XmlConfig(realpath(__DIR__ . '/assets/sample.xml'));
// $validator = new Validation($config);

// $result = $validator->validate([
// 	'password' => 'pass',
// 	'password-validate' => 'pass',
// 	'description' => 'asdadsa',
// 	'gender' => [
// 		'test' => '123',
// 		'g1',
// 		'g2',
// 		'g3'
// 	]
// ]);

// echo json_encode($result, JSON_PRETTY_PRINT);

$config = new XmlConfig('
	<form name="test" method="post" enctype="multipart/form-data">
		<input name="test-file[]" type="file" multiple="true">
			<validation required="true" maxsize="5000" filetype="jpg,jpeg">
				<errors>
					<required>Test file required</required>
					<maxsize>The file is too big yo</maxsize>
					<filetype>Only %s supported</filetype>
				</errors>
			</validation>
		</input>
		<input name="submit-btn" type="submit" />
	</form>
', true);

echo "<pre>";

/**
 * Validate
 */

if (array_key_exists('submit-btn', $_POST)) {
	$validator = new Validation($config);

	$validator->addData($_POST);
	$validator->addData($_FILES);

	$result = $validator->validate();

	if ($result->isValid()) {
		echo "OK!";
	}
	else {
		print_r($result->getErrors());
	}
}

/**
 * Render
 */

$renderer = new Renderer($config);
$buffer = $renderer->render();

$buffer->each(function($chunk, $content){
	$chunk->wrap('<div>' . $content . '</div>');
});

$buffer->submitBtn->wrap('<hr>%s');

echo '<div style="max-width: 500px; margin: 50px auto;">';
echo $buffer;
echo '</div>';