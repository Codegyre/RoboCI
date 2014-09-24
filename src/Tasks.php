<?php
namespace Codegyre\RoboCI;

trait Tasks
{
    protected function taskRunContainer($baseImage)
    {
        return new Task\RunContainer($baseImage);
    }

    protected function taskBuildRecipe($name)
    {
        return new Task\BuildRecipe($name);
    }

    protected function taskCreateStartScript($dir)
    {
        return new Task\CreateStartScript($dir);
    }

    protected function taskCreateRunScript($dir)
    {
        return new Task\CreateRunScript($dir);
    }

    protected function taskRunService($name)
    {
        return new Task\RunService($name);
    }
}
