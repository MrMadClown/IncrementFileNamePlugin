<?php

use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;

/**
 * @BeforeMethods({"init"})
 */
class PathDeterminationBench
{
    /** @var Filesystem */
    private $fs;

    public function init(array $params)
    {
        $writes = $params['writes'];
        $writeIntoFS = function (int $to): void {
            for ($i = 1; $i < $to; $i++) {
                $this->fs->write(sprintf('test/160mm_%d.pdf', $i), $i);
            }
        };

        $adapter = new MemoryAdapter();
        $this->fs = new Filesystem($adapter);
        if ($writes) $writeIntoFS($writes);
    }

    public function fillFileSystem()
    {
        yield 'first' => ['path' => 'test/160mm.pdf', 'writes' => null];
        yield 'minimal' => ['path' => 'test/160mm.pdf', 'writes' => 10];
        yield 'medium' => ['path' => 'test/160mm.pdf', 'writes' => 50];
        yield 'large' => ['path' => 'test/160mm.pdf', 'writes' => 100];
    }

    /** @ParamProviders({"fillFileSystem"}) */
    public function benchRegexMap(array $params)
    {
        $path = $params['path'];
        $pathInfo = pathinfo($path);
        $dirname = $pathInfo['dirname'] ?? '';
        $dirname = $dirname !== '.' ? $dirname : '';
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? '';
        $extensionWithDot = $extension ? '.' . $extension : '';

        $pattern = sprintf('/%s_(\d+)%s/',
            preg_quote($filename),
            preg_quote($extensionWithDot)
        );

        $contents = $this->fs->listContents($dirname);

        $files = array_map(static function (array $content) use ($pattern) {
            preg_match($pattern, $content['basename'], $matches);

            return ((int)($matches[1] ?? 0));
        }, $contents);

        $counter = 1;
        if (count($files) > 0) {
            sort($files);

            $counter = end($files) + 1;
        }

        $path = sprintf('%s_%d%s',
            $dirname . DIRECTORY_SEPARATOR . $filename,
            $counter,
            $extensionWithDot
        );
    }

    /** @ParamProviders({"fillFileSystem"}) */
    public function benchLoop(array $params)
    {
        $path = $params['path'];
        $pathInfo = pathinfo($path);
        $dirname = ($pathInfo['dirname'] ?? '') !== '.' ?: '';

        $extension = $pathInfo['extension'] ?? '';
        $extensionWithDot = $extension ? '.' . $extension : '';

        $filename = $pathInfo['filename'];

        $pattern = sprintf('%s_%s%s',
            $dirname . DIRECTORY_SEPARATOR . $filename,
            '%d',
            $extensionWithDot
        );

        $counter = 0;
        while ($this->fs->has($path)) {
            $path = sprintf($pattern, ++$counter);
        }
    }
}
