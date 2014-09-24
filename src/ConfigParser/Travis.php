<?php
namespace Codegyre\RoboCI\ConfigParser;

use Codegyre\RoboCI\Config;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Travis implements \ArrayAccess
{

    protected $config;

    function __construct($dir = '')
    {
        $file = $dir . Config::TRAVIS_CONFIG_FILE;
        if (!file_exists($file)) {
            throw new ParseException($file." does not exist in this dir, can't execute travis");
        }


        $this->config = Yaml::parse($file);

        if (!isset($this->config['php'])) {
            throw new ParseException("No PHP versions in this config, exiting");
        }

        if (!isset($this->config['script'])) {
            throw new ParseException('No scripts defined in .travis.yml to run, exiting');
        }
    }
    
    function install()
    {
        $beforeInstall = isset($this->config['before_install']) ? $this->config['before_install'] : [];
        $install = isset($this->config['install']) ? $this->config['install'] : [];
        return array_merge($beforeInstall, $install);
    }

    function services()
    {
        $services = isset($this->config['services']) ? $this->config['services'] : [];
        return array_merge(Config::$defaultServices, $services);
    }

    function beforeScript()
    {
        return isset($this->config['before_script']) ? $this->config['before_script'] : [];
    }

    function scripts()
    {
        return isset($this->config['script']) ? $this->config['script'] : [];
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return isset($this->config[$offset]) ? $this->config[$offset] : null;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }
}