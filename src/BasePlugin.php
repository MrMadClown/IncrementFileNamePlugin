<?php

namespace MrMadClown\IncrementFileNames;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;
use function pathinfo;
use function sprintf;

abstract class BasePlugin implements PluginInterface
{
    /** @var FilesystemInterface */
    protected $filesystem;

    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    protected function getIncrementedPath(string $path): string
    {
        $counter = 0;
        $pattern = $this->createFilePathPattern($path);
        while ($this->filesystem->has($path)) {
            $path = sprintf($pattern, ++$counter);
        }

        return $path;
    }

    protected function createFilePathPattern(string $path): string
    {
        $pathInfo = pathinfo($path);
        $dirname = ($pathInfo['dirname'] ?? '') !== '.' ?: '';

        $extension = $pathInfo['extension'] ?? '';
        $extensionWithDot = $extension ? '.' . $extension : '';

        $filename = $pathInfo['filename'];

        return sprintf('%s_%s%s',
            $dirname . DIRECTORY_SEPARATOR . $filename,
            '%d',
            $extensionWithDot
        );
    }
}
