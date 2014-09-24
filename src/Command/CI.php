<?php
namespace Codegyre\RoboCI\Command;

use Codegyre\RoboCI\Cleanup;
use Codegyre\RoboCI\Config;
use Codegyre\RoboCI\Runner;
use Codegyre\RoboDocker\DockerTasks;
use Codegyre\RoboDocker\Task\Commit;
use Codegyre\RoboDocker\Task\Remove as DockerRemove;
use Codegyre\RoboDocker\Task\Run as DockerRun;
use Codegyre\RoboDocker\Task\Stop;
use Symfony\Component\Yaml\Yaml;

trait CI
{
   use \Robo\Output;

    /**
     * Executes build for specified RoboCI environment
     *
     * @param $environment
     * @param string $args additional arguments
     * @return int
     */
    public function ciRun($environment, $args = '')
    {
        $runner = new Runner($environment);
        $runner->buildImage();
        $runner->runServices();
        $res = $runner->getContainerRunner()
            ->exec($runner->getRunCommand())
            ->args($args)
            ->run();

        $runner->stopServices();

        !$res->getExitCode()
            ? $this->yell('BUILD SUCCESSFUL')
            : $this->say("<error> BUILD FAILED </error>");

        $data = $res->getData();
        $this->say("To enter this container, save it with <info>docker commit {$data['cid']} travis_build_failed<info>");
        $this->say('Then you can run it: <info>docker run -i -t travis_build_failed bash</info>');
    }

    /**
     * Runs interactive bash shell in RoboCI environment
     * @param $environment
     * @param string $args
     */
    public function ciShell($environment, $args = '')
    {
        $runner = new Runner($environment);

        $res = $runner->buildImage();
        if (!$res->wasSuccessful()) return 1;
        $runner->runServices();

        $links = array_keys($runner->getServices());
        $links = implode(' ', array_map(function($l) {return "--link $l";}, $links ));

        $command = (new DockerRun(Config::$runImage))
            ->option('-t')
            ->option('-i')
            ->name('robo_shell_'.uniqid())
            ->arg($links)
            ->arg($args)
            ->exec('bash')
            ->getCommand();

        $this->yell("To enter shell run `$command`\n");
    }

    /**
     * Creates raw RoboCI configuration (not taken from .travis.yml)
     */
    public function ciBootstrap()
    {
        if (file_exists(Config::$runDir)) {
            $this->say(Config::$runDir . " exists, no need to bootstrap");
            return;
        }
        @mkdir(Config::$runDir);

        while ($env = $this->ask("Create new build environment. Enter to exit")) {
            $dir = Config::$runDir."/$env/";
            @mkdir($dir);
            touch($dir.'Dockerfile');
            $this->say($dir."Dockerfile created -> Use it to configure build image");
            touch($dir.'start.sh');
            $this->say($dir."start.sh created. -> executed when container is started");
            touch($dir.'run.sh');
            $this->say($dir."run.sh created. -> executed on build");
            file_put_contents($dir.'env.yml', Yaml::dump(['services' => []]));
            $this->say($dir."env.yml created. -> defines running service");
        }

        $this->yell('RoboCI raw setup is prepared. See'.Config::$runDir);
    }

    /**
     * Stops and removes old RoboCI containers
     */
    public function ciCleanup()
    {
        $cleaner = new Cleanup();
        $cleaner->retrieveContainers();
        $cleaner->stopContainers();
        $cleaner->removeContainers();
    }
} 

