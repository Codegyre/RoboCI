<?php
namespace Codegyre\RoboCI\Command\Travis;

use Codegyre\RoboCI\Task\BuildRecipe;
use Codegyre\RoboDocker\Task\Run as DockerRun;
use Robo\Result;
use Codegyre\RoboCI\Config;

trait Build
{
    /**
     * Provisions new container using Chef cookbooks from Travis CI
     * Takes very looooooooooong time, better to use created image
     *
     * @param array $opts
     */
    public function ciTravisBuild($opts = ['name' => null, 'recipes' => false, 'image' => false])
    {
        if (!$opts['image']) $opts['image'] = Config::$baseImage;
        return (new Builder)->execute($opts);
    }

}

class Builder
{
    use \Robo\Task\Exec;
    use \Robo\Task\FileSystem;
    use \Robo\Output;
    use \Codegyre\RoboDocker\DockerTasks;

    function execute($opts)
    {
        $answer = $this->ask("Bulding Docker container takes some time, you can use already provisioned image instead.\nDo you want to continue? (y/n)");
        if (trim(strtolower($answer)) != 'y') return;

        Result::$stopOnFail = true;

        $recipes = $opts['recipes'] ?: implode(',', Config::$travisRecipes);

        (new BuildRecipe('base'))
            ->tag('roboci_base')
            ->run();

        $res = (new DockerRun('base'))
            ->option('-i')
            ->option('--privileged')
            ->exec('/usr/bin/chef-solo -j /travis.json -o ' . $recipes)
            ->run();

        $this->taskDeleteDir(Config::$buildDir)->run();
        $data = $res->getData();
        $this->yell("Container built in successfully. Run `docker commit {$data['cid']} yourname/imagename` to save it");
    }
}