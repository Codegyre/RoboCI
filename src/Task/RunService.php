<?php
namespace Codegyre\RoboCI\Task;

use Codegyre\RoboDocker\Result as DockerResult;
use Codegyre\RoboDocker\Task\Run;
use Robo\Output;
use Robo\Task\Shared\TaskInterface;

class RunService implements TaskInterface
{
    protected $service;
    protected $name;
    use Output;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function run()
    {
        $image = "roboci_service_".$this->service;
        $images = [];
        exec('docker images | grep '.$image, $images);

        if (empty($images)) {
            $this->printTaskInfo("Service {$this->service} does not exist, building...");
            (new BuildRecipe($this->service))
                ->tag($image)
                ->run();
        }

        $this->printTaskInfo("Running <info>{$this->service}</info> service container");
        return (new Run($image))
            ->option('-d')
            ->name($this->name)
            ->run();
    }
}