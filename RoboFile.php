<?php
require_once 'vendor/autoload.php';

class RoboFile extends \Robo\Tasks
{
    use Codegyre\RoboCI\Command\Travis\Build;
    use Codegyre\RoboCI\Command\Travis\Prepare;

    // define public methods as commands

    public function changed($change)
    {
        $this->taskChangelog()
            ->version($this->getVersion())
            ->change($change)
            ->run();
    }

    public function release()
    {
        $this->say("Releasing");
       
        $this->taskGitStack()
            ->add('CHANGELOG.md')
            ->commit('updated')
            ->push()
            ->run();

        $this->taskGitHubRelease($this->version())
            ->uri('Codegyre/RoboCI')
            ->run();
    }


    protected function getVersion()
    {
        return trim(file_get_contents('VERSION'));
    }

}