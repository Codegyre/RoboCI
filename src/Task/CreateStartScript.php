<?php
namespace Codegyre\RoboCI\Task;

use Codegyre\RoboCI\Config;
use Robo\Output;
use Robo\Result;
use Robo\Task\Shared\TaskInterface;

class CreateStartScript implements TaskInterface
{
    use Output;
    protected $filename;
    protected $body = "#!/bin/bash\n";

    public function __construct($dir)
    {
        if (!file_exists($dir)) {
            @mkdir($dir);
        }
        $this->filename = $dir.DIRECTORY_SEPARATOR.Config::START_SCRIPT;
    }

    public function line($line)
    {
        $this->body .= $line ."\n";
        return $this;
    }

    public function lines(array $lines)
    {
        $this->body .= implode("\n", $lines)."\n";
        return $this;
    }

    public function comment($line)
    {
        return $this->line("# ".$line);
    }

    public function linkService($service)
    {
        $linkDir = Config::getRecipeDir($service);
        if (!file_exists($linkDir.DIRECTORY_SEPARATOR.Config::LINK_SCRIPT)) return $this;
        $this->comment("linking $service service");
        $linkScript = file_get_contents($linkDir.DIRECTORY_SEPARATOR.Config::LINK_SCRIPT);
        return $this->line($linkScript);
    }

    public function run()
    {
        $this->printTaskInfo("Writing {$this->filename}");
        $res = file_put_contents($this->filename, $this->body);
        if ($res === false) return Result::error($this, "File {$this->filename} couldnt be created");
        return Result::success($this);
    }
} 