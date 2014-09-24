<?php
namespace Codegyre\RoboCI\Command\Travis;

use Codegyre\RoboCI\Config;
use Codegyre\RoboCI\ConfigParser\Travis as TravisConfig;
use Symfony\Component\Yaml\Yaml;

/**
 * Tasks to launch Travis-CI like instances for PHP projects using Docker containers.
 * This allows you to run CI builds for any PHP project containing `.travis.yml` definitions locally, without using Travis CI
 * Useful for debugging failing builds and testing.
 *
 * This contains tasks for:
 *
 * * provisioning and creating images using Travis Cookbooks.
 * * creating Dockerfile from .travis.yml config
 * * running containers using settings from`.travis.yml` and predefined image
 *
 */
trait Prepare
{
    /**
     * Creates Dockerfile in .roboci dir in order to create container
     */
    public function ciTravisPrepare()
    {
        $config = new TravisConfig();
        foreach ($config['php'] as $php) {
            $generator = new EnvGenerator($config, $php);
            $generator->createDockerFile();
            $generator->createStartScript();
            $generator->createRunScript();
            $generator->createEnvConfig();

        }
        $this->say("Make sure it is available when executing travis:run");
    }

}

class EnvGenerator
{
    use \Robo\Task\FileSystem;
    use \Robo\Output;
    use \Codegyre\RoboCI\Tasks;

    protected $php;

    /**
     * @var TravisConfig
     */
    protected $config;

    public function __construct(TravisConfig $config, $php)
    {
        $this->php = $php;
        $this->config = $config;
        if (!file_exists(Config::$runDir."/$php")) {
            @mkdir(Config::$runDir."/$phpVersion");
        }
    }

    public function createDockerFile()
    {
        $phpVersion = $this->php;
        // create Dockerfile
        $filename = Config::$runDir."/$phpVersion/Dockerfile";

        $dockerFile = $this->taskWriteToFile($filename)
            ->line('FROM '.Config::$defaultImage)
            ->line('WORKDIR '.Config::$containerWorkDir)
            ->line('USER root')
            ->line('RUN phpenv global '.$phpVersion)
            ->line('RUN ["/bin/bash", "-l", "-c", "eval \"$(phpenv init -)\""]')
            ->line('ENV TRAVIS_PHP_VERSION '. $phpVersion)
            ->line('ENV TRAVIS true')
            ->line('ENV CONTINUOUS_INTEGRATION true')
            ->line('ENV TRAVIS_BUILD_DIR /home/travis/builds/current')
            ->line('ENV TRAVIS_BUILD_NUMBER 0')
            ->line('ENV TRAVIS_OS_NAME linux');

        foreach ($this->config->install() as $command) {
            $command = str_replace('"', '\"', $command);
            $dockerFile->line('RUN ["/bin/bash", "-l", "-c", "'.$command.'"]');
        }

        $dockerFile->line('ADD . '.Config::$containerWorkDir)
            ->line('USER root')
            ->line('RUN chown -R '.Config::TRAVIS_USER.' '.Config::$containerWorkDir) // https://github.com/docker/docker/issues/1295
            ->line('USER travis')
            ->line('ENV HOME /home/travis')
            ->line('ENV PATH $PATH:/home/travis/.phpenv/bin')
            ->run();

        $this->say("Dockerfile for running Travis saved to $filename");
    }
    
    public function createStartScript()
    {
        $php = $this->php;
        $startScript = $this->taskCreateStartScript(Config::$runDir."/$php")
            ->line('echo "------[ RUNNING START SCRIPT ]-----"')
            ->comment("switching PHP version")
            ->line('phpenv global '.$php)
            ->line('eval "$(phpenv init -)"')
            ->line('php -v');

        foreach ($this->config->services() as $service) {
            $startScript->linkService($service);
        }

        $startScript
            ->comment("before_script section .travis.yml")
            ->lines($this->config->beforeScript())
            ->run();
    }

    public function createRunScript()
    {
        $this->taskCreateRunScript(Config::$runDir."/".$this->php)
            ->scripts($this->config->scripts())
            ->run();
    }

    public function createEnvConfig()
    {
        $this->taskWriteToFile(Config::$runDir."/".$this->php.DIRECTORY_SEPARATOR.Config::ROBOCI_ENV_CONFIG_FILE)
            ->line(Yaml::dump(['services' => $this->config->services()]))
            ->run();
    }
}