<?php

namespace Nextform\Validation\Tests;

use Nextform\Config\XmlConfig;
use Nextform\Fields\InputField;
use Nextform\Validation\Models\FileModel;
use Nextform\Validation\Validation;
use Nextform\Validation\ValidatorFactory;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    /**
     * @var InputField
     */
    private $textField;

    /**
     * @var ValidatorFactory
     */
    private $factory;


    public function setUp()
    {
        $this->factory = new ValidatorFactory();
        $this->textField = new InputField();
        $this->textField->setAttribute('name', 'test');
    }


    public function testEmailValidator()
    {
        $validator = $this->factory->create('email', $this->textField, 'true');

        $this->assertTrue($validator->validate('testmail@test.de'));
        $this->assertFalse($validator->validate('testmaitest'));
        $this->assertFalse($validator->validate(''));
    }


    public function testDatecheckValidator()
    {
        $validator = $this->factory->create('datecheck', $this->textField, 'true');

        $this->assertTrue($validator->validate(date('d-m-Y')));
        $this->assertTrue($validator->validate('18.02.2007'));
        $this->assertFalse($validator->validate('02-14-2017'));
        $this->assertFalse($validator->validate(''));
    }


    public function testDateformatValidator()
    {
        $validator = $this->factory->create('dateformat', $this->textField, 'd.m.Y');

        $this->assertTrue($validator->validate(date('d.m.Y')));
        $this->assertTrue($validator->validate('18.02.2007'));
        $this->assertFalse($validator->validate('02-12-2017'));
        $this->assertFalse($validator->validate('02-14-2017'));
        $this->assertFalse($validator->validate(''));
    }


    public function testMaxlengthValidator()
    {
        $validator = $this->factory->create('maxlength', $this->textField, '5');

        $this->assertTrue($validator->validate(str_repeat('1', 5)));
        $this->assertTrue($validator->validate(str_repeat('1', 2)));
        $this->assertFalse($validator->validate(str_repeat('1', 6)));
        $this->assertFalse($validator->validate(str_repeat('1', 10)));
        $this->assertTrue($validator->validate(''));
    }


    public function testMinlengthValidator()
    {
        $validator = $this->factory->create('minlength', $this->textField, '5');

        $this->assertFalse($validator->validate(str_repeat('1', 4)));
        $this->assertFalse($validator->validate(str_repeat('1', 2)));
        $this->assertTrue($validator->validate(str_repeat('1', 5)));
        $this->assertTrue($validator->validate(str_repeat('1', 10)));
        $this->assertFalse($validator->validate(''));
    }


    public function testZipcodeValidator()
    {
        $validatorDe = $this->factory->create('zipcode', $this->textField, 'de');
        $validatorUsDe = $this->factory->create('zipcode', $this->textField, 'us,de');

        $this->assertTrue($validatorDe->validate('74889'));
        $this->assertTrue($validatorDe->validate('12345'));
        $this->assertFalse($validatorDe->validate('1234'));

        $this->assertTrue($validatorUsDe->validate('74889'));
        $this->assertTrue($validatorUsDe->validate('12345'));
        $this->assertFalse($validatorUsDe->validate('1234'));
    }


    public function testRegexValidator()
    {
        $validator = $this->factory->create('regex', $this->textField, '/^test$/');

        $this->assertTrue($validator->validate('test'));
        $this->assertFalse($validator->validate('test2'));
    }


    public function testRequiredValidator()
    {
        $validator = $this->factory->create('required', $this->textField, 'true');

        $this->assertFalse($validator->validate([]));
        $this->assertFalse($validator->validate([[]]));
        $this->assertFalse($validator->validate(['test' => []]));
        $this->assertFalse($validator->validate(''));
        $this->assertFalse($validator->validate('    '));
        $this->assertTrue($validator->validate('test'));

        $this->assertTrue($validator->validate([
            new FileModel('file1.jpg', 'image/jpg', '/private/path', 0, 40000),
            new FileModel('file2.jpg', 'image/jpg', '/private/path', 0, 30000),
            new FileModel('file3.jpg', 'image/jpg', '/private/path', 0, 20000)
        ]));

        $this->assertFalse($validator->validate(
            new FileModel('', '', '', '', '')
        ));

        $this->assertFalse($validator->validate(
            [
                new FileModel('', '', '', '', ''),
                new FileModel('', '', '', '', ''),
                new FileModel('', '', '', '', '')
            ]
        ));
    }


    public function testMaxsizeValidator()
    {
        $validator = $this->factory->create('maxsize', $this->textField, '5000');
        $file1 = new FileModel('file1.jpg', 'image/jpg', '/private/path', 0, 0);
        $file2 = new FileModel('file2.jpg', 'image/jpg', '/private/path', 0, 0);

        $file1->size = 5000 * 1000;
        $this->assertTrue($validator->validate([$file1]));

        $file1->size = 50000 * 1000;
        $this->assertFalse($validator->validate([$file1]));

        $file1->size = 50000 * 1000;
        $file2->size = 5000 * 1000;
        $this->assertFalse($validator->validate([$file1, $file2]));

        $file1->size = 5000 * 1000;
        $file2->size = 5000 * 1000;
        $this->assertTrue($validator->validate([$file1, $file2]));
    }


    public function testMinsizeValidator()
    {
        $validator = $this->factory->create('minsize', $this->textField, '6000');
        $file1 = new FileModel('file1.jpg', 'image/jpg', '/private/path', 0, 0);
        $file2 = new FileModel('file2.jpg', 'image/jpg', '/private/path', 0, 0);

        $file1->size = 10000 * 1000;
        $this->assertTrue($validator->validate([$file1]));

        $file1->size = 500 * 1000;
        $this->assertFalse($validator->validate([$file1]));

        $file1->size = 10000 * 1000;
        $file2->size = 500 * 1000;
        $this->assertFalse($validator->validate([$file1, $file2]));

        $file1->size = 10000 * 1000;
        $file2->size = 10000 * 1000;
        $this->assertTrue($validator->validate([$file1, $file2]));
    }


    public function testFiletypeValidator()
    {
        $validator = $this->factory->create('filetype', $this->textField, 'jpg,jpeg');
        $file1 = new FileModel('file1.jpg', 'image/jpg', '/private/path', 0, 10000);
        $file2 = new FileModel('file2.jpeg', 'image/jpg', '/private/path', 0, 10000);
        $file3 = new FileModel('file2.txt', 'file/txt', '/private/path', 0, 10000);

        $this->assertTrue($validator->validate([$file1, $file2]));
        $this->assertFalse($validator->validate([$file3]));
    }


    public function testConnectValidation()
    {
        $config = new XmlConfig(
            '<form>
                <input name="password" type="password">
                    <validation>
                        <connections
                            equals="content:password-validate"
                            minlength="password-validate" />
                    </validation>
                </input>
                <input name="password-validate" type="password">
                    <validation minlength="8" />
                </input>
            </form>',
            true
        );

        $validation = new Validation($config);

        // Connected equals validation
        $this->assertTrue($validation->validate([
            'password' => '12345678',
            'password-validate' => '12345678'
        ])->isValid());

        $this->assertFalse($validation->validate([
            'password' => '12345678',
            'password-validate' => '123'
        ])->isValid());

        // Connected minlength validation
        $this->assertFalse($validation->validate([
            'password' => '123',
            'password-validate' => '123'
        ])->isValid());
    }


    public function testListenerValidation()
    {
        $config = new XmlConfig(
            '<form>
                <input name="password" type="password" />
                <input name="password-validate" type="password" />
            </form>',
            true
        );

        $validation = new Validation($config);

        $validation->addListener('password', 'Soemthing went wrong', function ($value) {
            return $value == '12345678';
        });

        $this->assertTrue($validation->validate([
            'password' => '12345678',
            'password-validate' => '12345678'
        ])->isValid());

        $validation->addListener('password', 'Soemthing went wrong', function ($value) {
            return $value != '12345678';
        });

        $this->assertFalse($validation->validate([
            'password' => '12345678',
            'password-validate' => '12345678'
        ])->isValid());
    }
}
