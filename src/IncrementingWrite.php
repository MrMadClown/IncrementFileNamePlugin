<?php

namespace MrMadClown\IncrementFileNames;

final class IncrementingWrite extends BasePlugin
{
    public function getMethod()
    {
        return 'incrementingWrite';
    }

    public function handle($path, $contents, array $config = [])
    {
        $path = $this->getIncrementedPath($path);

        return $this->filesystem->write($path, $contents, $config);
    }
}
