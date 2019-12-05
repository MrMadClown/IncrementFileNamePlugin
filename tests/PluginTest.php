<?php

namespace MrMadClown\IncrementFileNames\Tests;

use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use MrMadClown\IncrementFileNames\IncrementingWrite;
use MrMadClown\IncrementFileNames\IncrementingWriteStream;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
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

        $contents = collect($adapter->listContents());

        foreach ($expectedPaths as $expectedPath) {
            static::assertNotNull($contents->firstWhere('path', $expectedPath));
        }
    }

    /** @dataProvider pathProvider */
    public function testIncrementingWriteStream(string $path, int $writes, array $expectedPaths)
    {
        $adapter = new MemoryAdapter();
        $fs = new Filesystem($adapter);
        $fs->addPlugin(new IncrementingWriteStream());

        for ($i = 0; $i < $writes; $i++) {
            $fs->incrementingWriteStream($path, fopen('php://memory','r'));
        }

        $contents = collect($adapter->listContents());

        foreach ($expectedPaths as $expectedPath) {
            static::assertNotNull($contents->firstWhere('path', $expectedPath));
        }
    }
}
