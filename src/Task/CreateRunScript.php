<?php
namespace Codegyre\RoboCI\Task;

use Codegyre\RoboCI\Config;
use Robo\Output;
use Robo\Result;
use Robo\Task\Shared\TaskInterface;

class CreateRunScript implements TaskInterface 
{
    use Output;
    protected $filename;
    protected $body = "#!/bin/bash\n";

    public function __construct($dir)
    {
        if (!file_exists($dir)) {
            @mkdir($dir);
        }
        $this->filename = $dir.DIRECTORY_SEPARATOR.Config::RUN_SCRIPT;
    }    
    
    function script($script)
    {
        $this->body .= 'echo "---------------------------"'."\n";
        $this->body .= 'echo "running '.str_replace('"', '', $script).'"' ."\n";
        $this->body .= 'echo "---------------------------"'."\n";
        $this->body .= $script ."\n";
        return $this;
    }

    public function scripts(array $scripts)
    {
        foreach ($scripts as $script) {
            $this->script($script);
        }
        return $this;
    }

    public function run()
    {
        $this->printTaskInfo("Writing {$this->filename}");
        $res = file_put_contents($this->filename, $this->body);
        if ($res === false) return Result::error($this, "File {$this->filename} couldnt be created");
        return Result::success($this);
    }
} 