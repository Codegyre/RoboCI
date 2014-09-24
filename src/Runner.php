<?php
namespace Codegyre\RoboCI;

use Codegyre\RoboCI\ConfigParser\RoboEnv;
use Codegyre\RoboCI\Task\RunContainer;
use Codegyre\RoboDocker\DockerTasks;
use Robo\Task\FileSystem;

class Runner
{
    use FileSystem;
    use DockerTasks;
    use Tasks;

    protected $env;
    protected $dir = '';
    protected $envConfig;
    protected $build;
    protected $services = [];

    public function __construct($env)
    {
        $this->env = $env;
        $this->envConfig = new RoboEnv($env);
        $this->build = uniqid();
        $this->dir = Config::$runDir . "/$env/";

        if (!file_exists($this->dir)) throw new Exception("Roboci environment directory {$this->dir} not prepared!");
    }

    public function buildImage()
    {
        $preserveDockerFile = file_exists('Dockerfile');
        $fs = $this->taskFileSystemStack();
        if ($preserveDockerFile) {
            $fs->remove('Dockerfile.saved')->rename('Dockerfile', 'Dockerfile.saved');
        }
        $fs->copy($this->dir . "Dockerfile", "Dockerfile")
            ->run();

        // load start script in shell
        $this->taskWriteToFile('Dockerfile')
            ->append()
            ->line("RUN cat {$this->dir}".Config::START_SCRIPT." >> /home/travis/.bashrc")
            ->run();

        $res = $this->taskDockerBuild()
            ->tag(Config::$runImage)
            ->run();

        $fs = $this->taskFileSystemStack()->remove('Dockerfile');
        if ($preserveDockerFile) {
            $fs->rename('Dockerfile.saved', 'Dockerfile');
        }
        $fs->run();
        return $res;
    }

    public function runServices()
    {
        foreach ($this->envConfig->services() as $service) {
            $container = 'robo_service_' . $service . $this->build;
            $this->services[$container.':'.$service] = $this->taskRunService($service)
                ->name($container)
                ->run();
        }
    }

    /**
     * @return RunContainer
     */
    public function getContainerRunner()
    {
        return $this->taskRunContainer(Config::$runImage)
            ->name('robo_build_'.$this->build)
            ->env('TRAVIS_BUILD_NUMBER', $this->build)
            ->linkServices(array_keys($this->services))
            ->containerWorkdir(Config::$containerWorkDir)
            ->privileged();
    }

    public function getRunCommand()
    {
        $start = $this->getStartCommand();
        $command = "";
        if (file_exists($this->dir.Config::RUN_SCRIPT)) {
            $command = '/bin/bash '.$this->dir.Config::RUN_SCRIPT;
        }
        if ($start) $command = "$start && $command";
        return $command;
    }

    public function getStartCommand()
    {
        if (file_exists($this->dir.Config::START_SCRIPT)) {
            return '/bin/bash '.$this->dir.Config::START_SCRIPT;
        }
        return "";
    }

    public function getServices()
    {
        return $this->services;
    }

    public function stopServices()
    {
        foreach ($this->services as $service) {
            if (!$service instanceof \Codegyre\RoboDocker\Result) continue;
            $this->taskDockerStop($service)->run();
        }        
    }
}