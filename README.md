# RoboCI

RoboCI is virtualized environment runner for Continuous Integration servers.
RoboCI is aimed to **run Travis CI builds locally** inside [Docker](http://docker.io) containers as well creating custom build setup.

## RoboCI is used to:

* create virtualized environments with Docker.
* run acceptance, functional, unit, integration tests in isolated containers.
* run Travis CI builds locally or on CI server.
* debug builds inside containers

## Requirements

Requires [Docker](http://docker.io) and [Robo PHP Task Runner](http://robo.li) to be installed.

## Installation

Use Composer

```
{
    "require-dev": {
        "codegyre/robo": "*",
        "codegyre/robo-ci": "@dev"
    }
}

```

Create `RoboFile.php` in the root of your project (if it is not already there), by simply running `robo`.

Attach composer autoloader to include `Codegyre\RoboCI` into your RoboFile:

``` php
<?php
require_once 'vendor/autoload.php';

class RoboFile extends \Robo\Tasks
{    
    use Codegyre\RoboCI\Command\CI;
    use Codegyre\RoboCI\Command\Travis\Prepare;

    // define public methods as commands
}
```

Now when you run `robo` you will see new commands in `ci` namespace.

## RoboCI and PHP builds

### Preparing Environment

RoboCI uses `.travis.yml` as a primary configuration for running travis builds.
By default it will use `davert/roboci-php` image, created from Travis Cookbooks.
It provides default travis environment with PHPs installed.

RoboCI will convert your `.travis.yml` file into its own format (see below) and store it in `.roboci` dir.

```
robo ci:travis-prepare
```

RoboCI creates an environment per PHP version listed in `.travis.yml` config. 
If you have configured php 5.5 in your travis CI you should see `.roboci/5.5` created. 
Travis instructions are parsed and prepared to be executed with Docker:

* `install` and `before_install` sections will be stored to `Dockerfile`, which is used to build docker container.
* `before_script` section will be saved to `start.sh`, which is included into `.bashrc` in order to be executed on container run.
* `script` section will be saved into `run.sh`
* `services` will be stored to `env.yml`. All defined services will be executed before the main container and stopped afterwards.

Unlike OpenVZ, which Travis uses for creating virtualized containers, Docker does not provide true virtualization.
It allows to execute different processes in its own namespaces. Each docker container is designed to run one process. 
It is not a technical but a design limitation. Because of this RoboCI implements its own way of executing services like mysql, postgresql, mongodb, etc. 
Rather then running services inside a build container, it creates linked containers per service. 
This makes behavior of RoboCI to be different then expected from TravisCI.

Take a note, that each time you update `.travis.yml`, you will need to rebuild environments with `robo ci:travis-prepare` command.

#### Optimizations

It is highly recommended that all scripts that all operations that could be cached to be stored into `install` section of TravisCI.
This will allow RoboCI to include them into Dockerfile and make Docker save them in intermediate containers.

If you are installing packages, or running `composer install` in before_script, you can move them to `install` to speed up containers launch.

### Running Build

To run build you can use `ci:run` with environment as parameter. In case you want to run build for php 5.5 you, you should run

```
robo ci:run 5.5
``` 

Much more useful to enter a built container without running tests. This will allow to run specific tests and debug them.
You can call `ci:shell` for that. In case of PHP 5.5 this execute

```
robo ci:shell 5.5
```

### Cleaning Up Old Builds

Main container and service containers may persist. In order to clean them, you can run 

```
robo ci:cleanup
```

## Reference

RoboCI stores build configurations in `.roboci` directory.

```
.roboci/
  environment1/
    Dockerfile <-- build configuration
    start.sh   <-- setup script, excuted on container start
    run.sh     <-- script executed on docker run
    env.yml    <-- lists required services
  environment2/
  environment3/
```

## Services

RoboCI includes MySql, PostgreSql, MongoDb, and RabbitMQ as services. Each service will be executed in its own container and linked into main container.
Service containers are built from recipes. Recipe includes `Dockerfile` and linkage instructions that wll be executed in main container.
Mainly linking requires running `socat` on exposed ports of service container. See examples.

## Customization

### Creating own services

Services are created from recipes. If you want to add a recipe to RoboCI, please send a Pull Request.
If you need your own service, create a `.roboci/_recipe/servicename` directory and put Dockerfile in it.
There you may specify base service image and build instructions. Also you may add linking instructions by creating `link.sh`.
Contents of `link.sh` will be executed in the main container.

By creating your own service you may redefine default ones.

Add additional services into `.roboci/environment/env.yml`.

### Non-Travis Builds

RoboCI can be extended to execute custom builds without using Travis recipes and Travis configuration.
To prepare raw RoboCI environment you should run 

```
robo ci:bootstrap
```

* You will need to prepare own build images and use it in `Dockerfile`.
* Custom services are defined in `env.yml` in `services` section.
* Preperation scripts placed in `start.sh`
* Run script placed in `run.sh`

Execute custom environment as usual:

```
robo ci:run environment
robo ci:shell environment
```


## Credits

Created by Michael Bodnarchuk [@davert](http://twitter.com/davert)
License MIT
