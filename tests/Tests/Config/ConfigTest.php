<?php

namespace Eufony\Tests\Config;

use Composer\Autoload\ClassLoader;
use Eufony\Config\Config;
use Eufony\Config\ConfigurationException;
use Eufony\FileSystem\Directory;
use Eufony\FileSystem\File;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use UnexpectedValueException;

/**
 * @covers \Eufony\Config\Config
 */
class ConfigTest extends TestCase {

    public static function setUpBeforeClass(): void {
        $env = <<<ENV
            APP_ENV=test
            ENV;


        $env_test = <<<ENV
            TEST_STRING=hello
            TEST_INT=42
            TEST_FLOAT=23.2
            TEST_BOOL_ONE=true
            TEST_BOOL_TWO=1
            TEST_BOOL_THREE=On
            TEST_BOOL_FOUR=Yes
            TEST_EMPTY=
            ENV;

        // Get application root from composer's autoloader class
        $class_loader_reflection = new ReflectionClass(ClassLoader::class);
        $app_dir = dirname($class_loader_reflection->getFileName(), 3);
        $_ENV['APP_DIR'] = $app_dir;

        // Make .env files
        Directory::make('config');
        File::write('config/.env', $env);
        File::write('config/.env.test', $env_test);

        // Do initial setup
        Config::setup();
    }

    public static function tearDownAfterClass(): void {
        Directory::remove('config');
    }

    /**
     * @covers \Eufony\Config\Config::setup
     */
    public function testSetup(): void {
        $_ENV = [];
        Config::setup();
        $this->assertNotEmpty($_ENV);
    }

    /**
     * @covers \Eufony\Config\Config::exists
     */
    public function testExists(): void {
        $this->assertTrue(Config::exists('TEST_STRING'));
        $this->assertFalse(Config::exists('TEST_NON_EXISTING'));
        $this->assertFalse(Config::exists('TEST_EMPTY'));
    }

    /**
     * @covers \Eufony\Config\Config::get
     */
    public function testGetPlain(): void {
        $this->assertEquals(dirname(__DIR__, 3), Config::get('APP_DIR'));
        $this->assertNull(Config::get('TEST_EMPTY'));
    }

    /**
     * @covers \Eufony\Config\Config::get
     */
    public function testGetNonExistingNonRequired(): void {
        $this->assertNull(Config::get('TEST_NON_EXISTING', required: false));
    }

    /**
     * @covers \Eufony\Config\Config::get
     */
    public function testGetNonExistingRequired(): void {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessageMatches('/Undefined configuration parameter/');
        Config::get('TEST_NON_EXISTING', required: true);
    }

    /**
     * @covers \Eufony\Config\Config::get
     */
    public function testGetWithExpectedValues(): void {
        $this->expectNotToPerformAssertions();
        Config::get('TEST_STRING', expected: ['hello', 'world']);
    }

    /**
     * @covers \Eufony\Config\Config::get
     */
    public function testGetWithUnexpectedValues(): void {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessageMatches("/Unexpected value 'hello' for configuration parameter/");
        Config::get('TEST_STRING', expected: ['foo', 'bar']);
    }

    /**
     * @covers \Eufony\Config\Config::get
     */
    public function testGetWithExpectedTypes(): void {
        $this->assertIsString(Config::get('TEST_STRING', expected: 'string'));
        $this->assertIsInt(Config::get('TEST_INT', expected: 'int'));
        $this->assertIsInt(Config::get('TEST_INT', expected: 'integer'));
        $this->assertIsFloat(Config::get('TEST_FLOAT', expected: 'float'));
        $this->assertIsFloat(Config::get('TEST_FLOAT', expected: 'double'));
        $this->assertIsBool(Config::get('TEST_BOOL_ONE', expected: 'bool'));
        $this->assertIsBool(Config::get('TEST_BOOL_TWO', expected: 'boolean'));
        $this->assertIsBool(Config::get('TEST_BOOL_THREE', expected: 'bool'));
        $this->assertIsBool(Config::get('TEST_BOOL_FOUR', expected: 'bool'));
    }

    /**
     * @covers \Eufony\Config\Config::get
     */
    public function testGetWithUnexpectedTypes(): void {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessageMatches('/Unexpected type for configuration parameter/');
        Config::get('TEST_STRING', expected: 'int');
    }

    /**
     * @covers \Eufony\Config\Config::get
     */
    public function testGetWithInvalidTypes(): void {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("Unrecognized expected type");
        Config::get('TEST_STRING', expected: 'array');
    }

}
