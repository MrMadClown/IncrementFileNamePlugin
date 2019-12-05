<?php

namespace MrMadClown\IncrementFileNames;

final class IncrementingWriteStream extends BasePlugin
{
    public function getMethod()
    {
        return 'incrementingWriteStream';
    }

    public function handle($path, $contents, array $config = [])
    {
        $path = $this->getIncrementedPath($path);

        return $this->filesystem->writeStream($path, $contents, $config);
    }
}
