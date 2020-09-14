<?php
/**
 * Staticify.php
 * @author: jmoulin@castelis.com
 */

namespace FMUP\Dispatcher\Plugin;

/**
 * Class Staticify
 * this component is a POST dispatcher
 * it will replace all asset url to attack statics ones
 * This will allow cookie free domain + improve parallels asset download
 *
 * @package FMUP\Dispatcher\Plugin
 */
class Staticify extends \FMUP\Dispatcher\Plugin
{
    const PROTOCOL = '://';

    protected $name = 'Staticify';
    /**
     * Number of static instances
     * @var int
     */
    protected $staticNumber = 3;

    /**
     * Prefix of static instances
     * @var string
     */
    protected $staticPrefix = 'static';

    /**
     * Suffix of static instances
     * @var string
     */
    protected $staticSuffix = '';
    /**
     * SubDomain to replace
     * @var string
     */
    protected $subDomain = 'www';
    /**
     * Domain to replace
     * @var string
     */
    protected $domain = null;

    /**
     * Current asset
     * @var int
     */
    private $currentAsset = 1;

    /**
     * Path between domain and requested asset for relative urls
     * @var string
     */
    private $trailingPath;

    /**
     * Will catch all resources URL
     *
     * @see self::computeAsset
     */
    public function handle()
    {
        $response = $this->getResponse()->getBody();
        $newResponse = $response;
        $isJson = $this->isRequestJson();
        if ($isJson) {
            $response = stripslashes($response);
        }
        $regexps = array(
            '~<[^>]+\ssrc=["\']([^"\']+)["\']~',
            '~<link\s[^>]*href=["\']([^"\']+)["\']~',
        );
        $values = array();
        foreach ($regexps as $exp) {
            preg_match_all($exp, $response, $glob);
            foreach ($glob[1] as $string) {
                $crc = crc32($string);
                if (!isset($values[$crc])) {
                    $newResponse = str_replace(
                        $this->jsonTransform($string, $isJson),
                        $this->computeAsset($string, $isJson),
                        $newResponse
                    );
                    $values[$crc] = 1;
                }
            }
        }
        $this->getResponse()->setBody($newResponse);
    }

    /**
     * Check whether response is json or not
     * @return bool
     * @throws \FMUP\Exception
     */
    private function isRequestJson()
    {
        $isJson = false;
        foreach ($this->getResponse()->getHeaders() as $type => $items) {
            if ($type == \FMUP\Response\Header\ContentType::TYPE) {
                foreach ($items as $item) {
                    if ($item instanceof \FMUP\Response\Header\ContentType &&
                        $item->getMime() == \FMUP\Response\Header\ContentType::MIME_APPLICATION_JSON
                    ) {
                        $isJson = true;
                        break;
                    }
                }
            }
        }
        return $isJson;
    }

    /**
     * add slashes to / character in json mode
     * @param string $string
     * @param bool $isJson
     * @return mixed
     */
    private function jsonTransform($string, $isJson)
    {
        return $isJson ? str_replace('/', '\/', $string) : $string;
    }

    /**
     * Compute which asset for a path and return the full path
     *
     * @param string $path
     * @param bool $isJson Check if url should be encoded
     *
     * @return string
     */
    protected function computeAsset($path, $isJson = false)
    {
        if (strpos($path, self::PROTOCOL) !== false) {
            return $this->jsonTransform($path, $isJson);
        }
        $trailingPath = ($path[0] !== '/') ? $this->getAssetPath() : '';
        $path = $this->getDomain() . $trailingPath . $path;
        $path = str_replace(
            self::PROTOCOL . $this->getSubDomain(),
            self::PROTOCOL . $this->getStaticPrefix() . $this->currentAsset++ . $this->getStaticSuffix(),
            $path
        );
        if ($this->currentAsset > $this->getStaticNumber()) {
            $this->currentAsset = 1;
        }
        return $this->jsonTransform($path, $isJson);
    }

    /**
     * Compute relative path between requested asset and current path on request URI
     * @return string
     * @throws \FMUP\Exception
     */
    private function getAssetPath()
    {
        if (!$this->trailingPath) {
            $request = $this->getRequest();
            /** @var $request \FMUP\Request\Http */
            $uri = $request->getServer(\FMUP\Request\Http::REQUEST_URI);
            $this->trailingPath = ($uri[strlen($uri) - 1] == '/' ? dirname($uri . 'random') : dirname($uri)) . '/';
        }
        return $this->trailingPath;
    }

    /**
     * @param int $number
     * @return $this
     */
    public function setStaticNumber($number = 3)
    {
        $this->staticNumber = (int)$number;
        return $this;
    }

    /**
     * @return int
     */
    public function getStaticNumber()
    {
        return $this->staticNumber;
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function setStaticPrefix($prefix = 'static')
    {
        $this->staticPrefix = (string)$prefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getStaticPrefix()
    {
        return $this->staticPrefix;
    }

    /**
     * @param string $suffix
     * @return $this
     */
    public function setStaticSuffix($suffix = '')
    {
        $this->staticSuffix = (string)$suffix;
        return $this;
    }

    /**
     * @return string
     */
    public function getStaticSuffix()
    {
        return $this->staticSuffix;
    }

    /**
     * @param string $subDomain
     * @return $this
     */
    public function setSubDomain($subDomain = 'www')
    {
        $this->subDomain = (string)$subDomain;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubDomain()
    {
        return $this->subDomain;
    }

    /**
     * @return string
     * @throws \FMUP\Exception
     */
    public function getDomain()
    {
        if ($this->domain === null) {
            /** @var \FMUP\Request\Http $request */
            $request = $this->getRequest();
            $this->domain = $request->getServer(\FMUP\Request\Http::REQUEST_SCHEME)
                . self::PROTOCOL . $request->getServer(\FMUP\Request\Http::HTTP_HOST);
        }
        return $this->domain;
    }

    /**
     * Define domain to use for static assets
     * @param string|null $domain
     * @return $this
     */
    public function setDomain($domain = null)
    {
        $this->domain = $domain === null ? null : (string)$domain;
        return $this;
    }
}
