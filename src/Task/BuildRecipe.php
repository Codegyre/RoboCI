<?php
namespace Codegyre\RoboCI\Task;

use Codegyre\RoboCI\Config;
use Codegyre\RoboDocker\Task\Build;
use Robo\Task\Shared\TaskException;
use Robo\Task\Shared\TaskInterface;

class BuildRecipe extends Build implements TaskInterface
{
    protected $recipe;
    protected $command = 'docker build ';

    public function __construct($recipe)
    {
        $this->recipe = $recipe;
        $this->path = Config::getRecipeDir($recipe);
    }

    public function run()
    {
        if (!$this->path) {
            throw new TaskException($this, "Recipe for {$this->recipe} not found in ".Config::getUserRecipesDir());
        }
        return parent::run();
    }
}