<?php

require realpath(__DIR__ . '/../vendor/autoload.php');

use Nextform\Config\XmlConfig;
use Nextform\Renderer\Renderer;
use Nextform\Validation\Validation;

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
		<input name="username" type="text" placeholder="Username">
			<validation minlength="5">
				<errors>
					<minlength>The Username too short</minlength>
				</errors>
			</validation>
		</input>
		<input name="password" type="password" placeholder="Password" />
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

echo '<pre>';

/**
 * Validate
 */

if (array_key_exists('submit-btn', $_POST)) {
    $validator = new Validation($config);

    $validator->addListener('username', 'The username is invalid', function ($value) {
        return $value == 'Davide';
    });

    $validator->addListener('username', 'The username is too long', function ($value) {
        return strlen('Davide') < 7;
    });

    $validator->addData($_POST);
    $validator->addData($_FILES);

    $result = $validator->validate();

    if ($result->isValid()) {
        echo 'OK!';
    } else {
        print_r($result->getErrors());
    }
}

/**
 * Render
 */

$renderer = new Renderer($config);
$buffer = $renderer->render();

$buffer->group(['username', 'password']);
$buffer->each(function ($chunk, $content) {
    $chunk->wrap(
        '<div class="input-wrapper" style="padding: 10px; border: 1px solid lightgray; margin-bottom: 10px">' . $content . '</div>'
    );
});

$buffer->submitBtn->wrap('<hr>%s');

echo '<div style="max-width: 500px; margin: 50px auto;">';
echo $buffer;
echo '</div>';
