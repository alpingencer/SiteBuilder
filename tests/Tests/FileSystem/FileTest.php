<?php

namespace Eufony\Tests\FileSystem;

use Eufony\Utils\FileSystem\File;
use Eufony\Utils\FileSystem\IOException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Eufony\Utils\FileSystem\File
 * @uses   \Eufony\Utils\FileSystem\Directory
 * @uses   \Eufony\Utils\FileSystem\Path
 * @uses   \Eufony\Utils\Config\Config
 */
class FileTest extends TestCase {

    /**
     * @covers \Eufony\Utils\FileSystem\File::exists
     */
    public function testExists(): void {
        $this->assertTrue(File::exists('tests/assets/sample.txt'));
        $this->assertFalse(File::exists('tests/assets'));
        $this->assertFalse(File::exists('tests/assets/foo/bar.txt'));
    }

    /**
     * @covers \Eufony\Utils\FileSystem\File::touch
     * @covers \Eufony\Utils\FileSystem\File::remove
     */
    public function testTouchAndRemove(): void {
        $file = 'tests/tmp/test.txt';
        $this->assertFalse(File::exists($file));
        File::touch($file);
        $this->assertTrue(File::exists($file));
        File::remove($file);
        $this->assertfalse(File::exists($file));
    }

    /**
     * @covers \Eufony\Utils\FileSystem\File::read
     */
    public function testReadValid(): void {
        $this->assertEquals("Hello, world!\n", File::read('tests/assets/sample.txt'));
    }

    /**
     * @covers \Eufony\Utils\FileSystem\File::read
     */
    public function testReadNonexistentFile(): void {
        $this->expectException(IOException::class);
        $this->expectExceptionMessageMatches('/File not found/');
        File::read('tests/assets/foo/bar.txt');
    }

    /**
     * @covers \Eufony\Utils\FileSystem\File::read
     */
    public function testReadProtectedFile(): void {
        $this->expectException(IOException::class);
        $this->expectExceptionMessageMatches('/File is unreadable/');
        File::read('tests/assets/protected.txt');
    }

    /**
     * @covers \Eufony\Utils\FileSystem\File::write
     */
    public function testWriteValid(): void {
        $file = 'tests/tmp/test.txt';
        $this->assertFalse(File::exists($file));
        File::write($file, "Hello, world!\n");
        $this->assertEquals("Hello, world!\n", File::read($file));
        File::remove($file);
    }

    /**
     * @covers \Eufony\Utils\FileSystem\File::write
     */
    public function testWriteInvalid(): void {
        $this->expectException(IOException::class);
        $this->expectExceptionMessageMatches('/File is unwritable/');
        $content = "This file was created while running some PHPUnit tests.\nIf you're seeing this, you can safely remove it.";
        File::write('/delete_me.txt', $content);
    }

}
