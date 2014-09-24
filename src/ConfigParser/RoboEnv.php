<?php
namespace Codegyre\RoboCI\ConfigParser;

use Codegyre\RoboCI\Config;
use Codegyre\RoboCI\Exception;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class RoboEnv
{
    protected $config = [];

    function __construct($env)
    {
        $file = Config::$runDir.DIRECTORY_SEPARATOR.$env.DIRECTORY_SEPARATOR.Config::ROBOCI_ENV_CONFIG_FILE;
        if (!file_exists($file)) {
            throw new Exception("$file does not exist in this dir, can't execute RoboCI");
        }
        $this->config = Yaml::parse($file);
    }

    function services()
    {
        return isset($this->config['services']) ? $this->config['services'] : [];
    }
}