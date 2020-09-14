<?php
namespace FMUP\Cache\Driver;

use FMUP\Cache\CacheInterface;
use FMUP\Cache\Exception;

class Shm implements CacheInterface
{
    const SETTING_NAME = 'SETTING_NAME';
    const SETTING_SIZE = 'SETTING_SIZE';

    private $shmInstance = null;
    private $isAvailable = null;

    /**
     * @var array
     */
    protected $settings = array();

    /**
     * constructor of File
     * @param array $settings
     */
    public function __construct($settings = array())
    {
        $this->setSettings($settings);
    }

    /**
     * Can define settings of the component
     * @param array $settings
     * @return $this
     */
    public function setSettings($settings = array())
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @param string $setting
     * @param mixed $value
     * @return $this
     */
    public function setSetting($setting, $value)
    {
        $this->settings[$setting] = $value;
        return $this;
    }

    /**
     * Get a specific value of setting
     * @param string $setting
     * @return mixed
     */
    public function getSetting($setting)
    {
        return isset($this->settings[$setting]) ? $this->settings[$setting] : null;
    }

    /**
     * Internal method to secure a SHM name
     * @param string $name
     * @return int
     */
    private function secureName($name = null)
    {
        return is_null($name) ? 1 : $this->stringToUniqueId($name);
    }

    /**
     * Convert string to a unique id
     * @param string $string
     * @return int
     */
    private function stringToUniqueId($string)
    {
        if (is_numeric($string)) {
            return (int)$string;
        }
        $length = strlen($string);
        $return = 0;
        for ($i = 0; $i < $length; $i++) {
            $return += ord($string[$i]);
        }
        return (int)$length . '1' . $return;
    }

    /**
     * Get SHM resource
     * @return resource
     * @throws Exception
     */
    private function getShm()
    {
        if (!$this->isAvailable()) {
            throw new Exception('SHM is not available');
        }
        if (!$this->shmInstance) {
            $memorySize = $this->getSetting(self::SETTING_SIZE);
            $shmName = $this->secureName($this->getSetting(self::SETTING_NAME));
            $this->shmInstance = is_numeric($memorySize)
                ? $this->shmAttach($shmName, (int)$memorySize)
                : $this->shmAttach($shmName);
        }
        return $this->shmInstance;
    }

    /**
     * @return resource
     * @codeCoverageIgnore
     */
    protected function shmAttach()
    {
        return call_user_func_array('shm_attach', func_get_args());
    }

    /**
     * @return mixed
     * @codeCoverageIgnore
     */
    protected function shmGetVar()
    {
        return call_user_func_array('shm_get_var', func_get_args());
    }

    /**
     * Retrieve stored value
     * @param string $key
     * @return mixed|null
     * @throws Exception
     */
    public function get($key)
    {
        if (!$this->isAvailable()) {
            throw new Exception('SHM is not available');
        }
        $key = $this->secureName($key);
        return ($this->has($key)) ? $this->shmGetVar($this->getShm(), $key) : null;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    protected function shmHasVar()
    {
        return call_user_func_array('shm_has_var', func_get_args());
    }

    /**
     * Check whether key exists in SHM
     * @param string $key
     * @return bool
     * @throws Exception
     */
    public function has($key)
    {
        if (!$this->isAvailable()) {
            throw new Exception('SHM is not available');
        }
        $key = $this->secureName($key);
        return $this->shmHasVar($this->getShm(), $key);
    }

    /**
     * Remove a stored key if exists
     * @param string $key
     * @return $this
     * @throws Exception
     */
    public function remove($key)
    {
        if (!$this->isAvailable()) {
            throw new Exception('SHM is not available');
        }
        $key = $this->secureName($key);
        if ($this->has($key) && !$this->shmRemoveVar($this->getShm(), $key)) {
            throw new Exception('Unable to delete key from cache Shm');
        }
        return $this;
    }

    /**
     * @param resource $shmResource
     * @param string $key
     * @codeCoverageIgnore
     * @return bool
     */
    protected function shmRemoveVar($shmResource, $key)
    {
        return shm_remove_var($shmResource, $key);
    }

    /**
     * Define a key in SHM
     * @param string $key
     * @param mixed $value
     * @throws Exception
     * @return $this
     */
    public function set($key, $value)
    {
        if (!$this->isAvailable()) {
            throw new Exception('SHM is not available');
        }
        $key = $this->secureName($key);
        if (!$this->shmPutVar($this->getShm(), $key, $value)) {
            throw new Exception('Unable to define key into cache Shm');
        }
        return $this;
    }

    /**
     * @param resource $shmResource
     * @param string $key
     * @param mixed $value
     * @return bool
     * @codeCoverageIgnore
     */
    protected function shmPutVar($shmResource, $key, $value)
    {
        return shm_put_var($shmResource, $key, $value);
    }

    /**
     * Check whether apc is available
     * @return bool
     */
    public function isAvailable()
    {
        if (is_null($this->isAvailable)) {
            $this->isAvailable = function_exists('shm_attach');
        }
        return $this->isAvailable;
    }
}
