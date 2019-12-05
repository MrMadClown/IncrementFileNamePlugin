<?php

namespace MrMadClown\IncrementFileNames;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;

final class Plugin implements PluginInterface
{
    /** @var FilesystemInterface */
    protected $filesystem;

    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->filesystem->addPlugin(new IncrementingWrite());
        $this->filesystem->addPlugin(new IncrementingWriteStream());
    }

    public function getMethod()
    {
        return 'iWrite';
    }

    public function handle($path, $contents, array $config = [])
    {
        return \is_resource($contents)
            ? $this->filesystem->incrementingWriteStream($path, $contents, $config)
            : $this->filesystem->incrementingWrite($path, $contents, $config);
    }
}
