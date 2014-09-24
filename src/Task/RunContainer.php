<?php
namespace Codegyre\RoboCI\Task;

use Codegyre\RoboDocker\Task\Run;

class RunContainer extends Run
{
    protected $linkingScripts = [];
    protected $services = [];

    public function linkServices($services)
    {
        foreach ($services as $linkedService) {
            $this->option('link', $linkedService);
        }

        return $this;
    }
}