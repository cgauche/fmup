<?php
namespace FMUP;

use FMUP\Dispatcher\Plugin;
use FMUP\Environment;
use FMUP\Sapi;

class Dispatcher
{
    use Environment\OptionalTrait, Sapi\OptionalTrait;

    const WAY_APPEND = 'WAY_APPEND';
    const WAY_PREPEND = 'WAY_PREPEND';

    /**
     * List of plugins to execute on Dispatch
     * @var array
     */
    private $plugins = array();

    /**
     * List of plugins name
     */
    private $pluginsName = array();

    /**
     * @var boolean
     */
    private $isInitDefaultPlugin = false;

    /**
     * @var Request
     */
    private $originalRequest;

    /**
     * Dispatch routes and return the first available route
     * @param Request $request
     * @param Response $response
     * @return $this
     */
    public function dispatch(Request $request, Response $response)
    {
        $this->setOriginalRequest($request);
        if ($this->canInitDefaultPlugin()) {
            $this->defaultPlugins();
        }
        foreach ($this->plugins as $plugin) {
            /* @var $plugin Plugin */
            $plugin->setRequest($request)->setResponse($response)
                ->setSapi($this->getSapi())->setEnvironment($this->getEnvironment());
            if ($plugin->canHandle()) {
                $plugin->handle();
            }
        }
        return $this;
    }

    /**
     * @return bool
     */
    protected function canInitDefaultPlugin()
    {
        $bool = $this->isInitDefaultPlugin;
        $this->isInitDefaultPlugin = true;
        return !$bool;
    }

    /**
     * Define the original request
     * @param Request $request
     * @return $this
     */
    private function setOriginalRequest(Request $request)
    {
        $this->originalRequest = clone $request;
        return $this;
    }

    /**
     * Retrieve original request (nothing has been modified)
     * @return Request|null
     */
    public function getOriginalRequest()
    {
        return $this->originalRequest;
    }

    /**
     * Clear all routes defined
     * @return $this
     */
    public function clear()
    {
        $this->plugins = array();
        $this->pluginsName = array();
        return $this;
    }

    /**
     * Remove a plugin in stack
     * @param Plugin $plugin
     * @return $this
     */
    public function removePlugin(Plugin $plugin)
    {
        $names = array_flip($this->pluginsName);
        if (isset($names[$plugin->getName()])) {
            $key = $names[$plugin->getName()];
            unset($this->pluginsName[$key]);
            unset($this->plugins[$key]);
        }
        return $this;
    }

    /**
     * Add a plugin in stack
     * @param Plugin $plugin
     * @param string $way
     * @return $this
     */
    public function addPlugin(Plugin $plugin, $way = self::WAY_APPEND)
    {
        $names = array_flip(array_values($this->pluginsName));
        if (isset($names[$plugin->getName()])) {
            $this->plugins[$names[$plugin->getName()]] = $plugin;
        } else {
            if ($way == self::WAY_APPEND) {
                array_push($this->plugins, $plugin);
                array_push($this->pluginsName, $plugin->getName());
            } else {
                array_unshift($this->plugins, $plugin);
                array_unshift($this->pluginsName, $plugin->getName());
            }
        }
        return $this;
    }

    /**
     * Initialize default plugins to define - optional
     * @return $this
     */
    public function defaultPlugins()
    {
        $this->canInitDefaultPlugin(); // if externally called, avoid to re-init plugins and execute them multiple times
        return $this;
    }
}
