<?php

namespace FMUP\Dispatcher;

class Post extends \FMUP\Dispatcher
{
    public function defaultPlugins()
    {
        $this->addPlugin(new Plugin\Render());
        return $this;
    }
}
