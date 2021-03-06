<?php
namespace FMUP\Crypt;

class Factory
{
    const DRIVER_MD5 = "Md5";
    const DRIVER_MCRYPT = "MCrypt";

    private static $instance;

    private function __construct()
    {
    }

    /**
     * @codeCoverageIgnore
     */
    private function __clone()
    {
    }

    /**
     * @return self
     */
    final public static function getInstance()
    {
        if (!self::$instance) {
            $class = get_called_class();
            self::$instance = new $class();
        }
        return self::$instance;
    }

    /**
     *
     * @param string $driver
     * @return \FMUP\Crypt\CryptInterface
     * @throws Exception
     */
    final public function create($driver = self::DRIVER_MD5)
    {
        $class = $this->getClassNameForDriver($driver);
        if (!class_exists($class)) {
            throw new Exception('Unable to create ' . $class);
        }
        $instance = new $class();
        if (!$instance instanceof CryptInterface) {
            throw new Exception('Unable to create ' . $class);
        }
        return $instance;
    }

    /**
     * Get full name for class to create
     * @param string $driver
     * @return string
     */
    protected function getClassNameForDriver($driver)
    {
        return __NAMESPACE__ . '\Driver\\' . (string)$driver;
    }
}
