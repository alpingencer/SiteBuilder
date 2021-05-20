<?php

namespace Eufony\Tests\FileSystem;

use Eufony\Utils\Config\Config;
use Eufony\Utils\FileSystem\IOException;
use Eufony\Utils\FileSystem\Path;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Eufony\Utils\FileSystem\Path
 * @uses   \Eufony\Utils\Config\Config
 */
class PathTest extends TestCase {

    /**
     * @covers \Eufony\Utils\FileSystem\Path::isAbsolute
     */
    public function testIsAbsolute(): void {
        $this->assertTrue(Path::isAbsolute('/foo/bar'));
        $this->assertFalse(Path::isAbsolute('foo/bar'));
    }

    /**
     * @covers \Eufony\Utils\FileSystem\Path::full
     */
    public function testFull(): void {
        $app_dir = Config::get('APP_DIR');
        $this->assertEquals($app_dir . '/foo/bar', Path::full('foo/bar'));
        $this->assertEquals('/foo/bar', Path::full('/foo/bar'));
    }

    /**
     * @covers \Eufony\Utils\FileSystem\Path::real
     */
    public function testRealValid(): void {
        $app_dir = Config::get('APP_DIR');
        $this->assertEquals($app_dir . '/tests/assets/sample.txt', Path::real('tests/assets/symlink.txt'));
        $this->assertEquals($app_dir . '/tests/assets/sample.txt', Path::real('tests/assets/sample.txt'));
    }

    /**
     * @covers \Eufony\Utils\FileSystem\Path::real
     */
    public function testRealInvalid(): void {
        $this->expectException(IOException::class);
        $this->expectExceptionMessageMatches('/Path does not exist/');
        Path::real('foo/bar');
    }

    /**
     * @covers \Eufony\Utils\FileSystem\Path::sanitized
     */
    public function testSanitized(): void {
        $this->assertEquals('foo/bar', Path::sanitized('./../foo/bar//../bar/./baz/..///'));
        $this->assertEquals('/foo/bar', Path::sanitized('/./../foo/bar//../bar/./baz/..///'));
    }

}
