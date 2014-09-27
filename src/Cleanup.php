<?php
namespace Codegyre\RoboCI;

use Codegyre\RoboDocker\DockerTasks;
use Robo\Task\Exec;

class Cleanup
{
    use Exec;
    use \Robo\Output;
    use DockerTasks;
    
    protected $containers = [];

    public function retrieveContainers()
    {
        $this->printTaskInfo("Getting all robo_* containers");
        $res = $this->taskExec('docker ps -a | grep robo_')->printed(false)->run();
        if (!$res->wasSuccessful()) {
            $this->printTaskInfo("No containers matched");
            return;
        }
        $containerLines = explode("\n", $res->getMessage());
        foreach ($containerLines as $container) {
            $data = explode(' ', $container);
            $id = trim(reset($data));
            if (!$id) continue;
            $this->containers[] = $id;
        }
        $this->printTaskInfo("Containers are: <info>".implode(', ', $this->containers)."</info>");
    }

    public function stopContainers()
    {
        foreach ($this->containers as $container) {
            $this->taskDockerStop($container)->run();
        }
    }

    public function removeContainers()
    {
        foreach ($this->containers as $container) {
            $this->taskDockerRemove($container)->run();
        }
    }


} 