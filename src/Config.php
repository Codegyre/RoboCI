<?php
namespace Codegyre\RoboCI;

/**
 * Class Config
 *
 * Contains build defaults that can be overridden to customize build
 *
 */
final class Config
{
    const TRAVIS_CONFIG_FILE = '.travis.yml';
    const ROBOCI_CONFIG_FILE = '.roboci.yml';
    const ROBOCI_ENV_CONFIG_FILE = 'env.yml';

    const TRAVIS_USER = 'travis';

    static $buildDir = "recipes/base";
    static $runDir = ".roboci";
    static $containerWorkDir = "/home/travis/builds";

    static $runImage = "roboci_run";
    static $buildImage = "roboci_build";
    static $baseImage = "ubuntu:12.04";

    static $defaultImage = 'davert/roboci-php';
    static $defaultServices = ['mysql', 'postgresql'];

    const RUN_SCRIPT = 'run.sh';
    const START_SCRIPT = 'start.sh';
    const LINK_SCRIPT = 'link.sh';

    static $travisRecipes = [
        'travis_build_environment',
        'git::ppa',
        'mercurial',
        'bazaar',
        'subversion',
        'scons',
        'unarchivers',
        'md5deep',
        'dictionaries',
        'libqt4',
        'libgdbm',
        'libncurses',
        'libossp-uuid',
        'libffi',
        'ragel',
        'imagemagick',
        'mingw32',
        'libevent',
        'java',
        'sqlite',
        'python',
        'python::pip',
        'nodejs::multi',
        'xserver',
        'firefox::tarball',
        'chromium',
        'phantomjs::tarball',
        'emacs::nox',
        'vim'
    ];

    static $attributes = [
        'travis_build_environment' => [
            'user' => 'travis',
            'group' => 'travis',
            'home' => '/home/travis/',
            'update_hosts' => false,
        ]
    ];

    static function getDefaultRecipesDir()
    {
        return __DIR__.'/../recipes';
    }

    static function getUserRecipesDir()
    {
        return self::$runDir.'/_recipes';
    }

    /**
     * @param $recipe
     * @return string $recipePath
     */
    static function getRecipeDir($recipe)
    {
        $userRecipe = Config::getUserRecipesDir() . "/$recipe";
        if (file_exists($userRecipe . '/Dockerfile')) {
            return $userRecipe;
        }

        $defaultRecipe = Config::getDefaultRecipesDir() . "/$recipe";
        if (file_exists($defaultRecipe . '/Dockerfile')) {
            return $defaultRecipe;
        }
    }
}