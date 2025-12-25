<?php
declare(strict_types=1);

namespace eLonePath\Test\Utility;

use eLonePath\Utility\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Filesystem::class)]
class FilesystemTest extends TestCase
{
    #[Test]
    public function testDirectoryIsReadable(): void
    {
        $this->expectNotToPerformAssertions();
        Filesystem::directoryIsReadable(sys_get_temp_dir());
    }

    #[Test]
    public function testDirectoryIsReadableWithNoExistingDir(): void
    {
        $this->expectExceptionMessage('Directory `' . sys_get_temp_dir() . DS . 'noExistingDir` is not readable');
        Filesystem::directoryIsReadable(sys_get_temp_dir() . DS . 'noExistingDir');
    }

    #[Test]
    public function testDirectoryIsReadableWithFile(): void
    {
        $this->expectExceptionMessage('`' . __FILE__ . '` is not a directory');
        Filesystem::directoryIsReadable(__FILE__);
    }

    #[Test]
    public function testFileIsReadable(): void
    {
        $this->expectNotToPerformAssertions();
        Filesystem::fileIsReadable(__FILE__);
    }

    #[Test]
    public function testFileIsReadableWithNoExistingFile(): void
    {
        $this->expectExceptionMessage('File `' . sys_get_temp_dir() . DS . 'noExistingFile' . '` is not readable');
        Filesystem::fileIsReadable(sys_get_temp_dir() . DS . 'noExistingFile');
    }

    #[Test]
    public function testFileIsReadableWithDirectory(): void
    {
        $this->expectExceptionMessage('`' . sys_get_temp_dir() . '` is not a file');
        Filesystem::fileIsReadable(sys_get_temp_dir());
    }

    #[Test]
    public function testReadJsonDataFromFile(): void
    {
        $result = Filesystem::readJsonDataFromFile(RESOURCES . DS . 'stories' . DS . 'cave_of_trials' . DS . 'metadata.json');
        $this->assertNotEmpty($result);
    }

    #[Test]
    public function testReadJsonDataOnError(): void
    {
        $this->expectExceptionMessage('Failed to parse JSON data from `' . ROOT . DS . '.gitignore` file: "syntax error".');
        Filesystem::readJsonDataFromFile(ROOT . DS . '.gitignore');
    }

    #[Test]
    public function testReadJsonDataOnErrorDueToInvalidStructure(): void
    {
        $badFile = TMP . DS . 'bad-json-data.json';
        if (!file_exists($badFile)) {
            file_put_contents($badFile, json_encode(true));
        }

        $this->expectExceptionMessage('JSON data in `' . $badFile . '` must be an object or array.');
        Filesystem::readJsonDataFromFile($badFile);
    }
}
