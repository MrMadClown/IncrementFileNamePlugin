<?php

namespace MrMadClown\IncrementFileNames\Tests;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Memory\MemoryAdapter;
use MrMadClown\IncrementFileNames\IncrementingWrite;
use MrMadClown\IncrementFileNames\IncrementingWriteStream;
use MrMadClown\IncrementFileNames\Plugin;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtMostCount;
use PHPUnit\Framework\MockObject\Matcher\MethodName;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    public function contentProvider()
    {
        yield ['incrementingWrite', 'some-string-content'];
        yield ['incrementingWriteStream', fopen('php://memory', 'r')];
    }

    /** @dataProvider contentProvider */
    public function testPlugin(string $expectedMethod, $content)
    {
        $fsMock = static::createMock(Filesystem::class);
        $fsMock
            ->expects($this->once())
            ->method('__call')
            ->with($expectedMethod, ['path', $content, []]);

        $plugin = new Plugin();
        $plugin->setFilesystem($fsMock);

        $plugin->handle('path', $content);
    }

    public function pathProvider()
    {
        yield [
            'some-path.ext',
            3,
            [
                'some-path.ext',
                'some-path_1.ext',
                'some-path_2.ext',
            ],
        ];
        yield [
            'some-path',
            3,
            [
                'some-path',
                'some-path_1',
                'some-path_2',
            ],
        ];
    }

    /** @dataProvider pathProvider */
    public function testIncrementingWrite(string $path, int $writes, array $expectedPaths)
    {
        $adapter = new MemoryAdapter();
        $fs = new Filesystem($adapter);
        $fs->addPlugin(new IncrementingWrite());

        for ($i = 0; $i < $writes; $i++) {
            $fs->incrementingWrite($path, 'content');
        }

        static::assertExpectedFilesArePresent($fs->listContents(), $expectedPaths);
    }

    /** @dataProvider pathProvider */
    public function testIncrementingWriteStream(string $path, int $writes, array $expectedPaths)
    {
        $adapter = new MemoryAdapter();
        $fs = new Filesystem($adapter);
        $fs->addPlugin(new IncrementingWriteStream());

        for ($i = 0; $i < $writes; $i++) {
            $fs->incrementingWriteStream($path, fopen('php://memory', 'r'));
        }

        static::assertExpectedFilesArePresent($fs->listContents(), $expectedPaths);
    }

    protected static function assertExpectedFilesArePresent(array $fsContent, array $expectedPaths): void
    {
        foreach ($expectedPaths as $expectedPath) {
            static::assertCount(
                1,
                \array_filter(
                    $fsContent,
                    static function (array $content) use ($expectedPath): bool {
                        return $content['path'] === $expectedPath;
                    }
                )
            );
        }
    }
}
