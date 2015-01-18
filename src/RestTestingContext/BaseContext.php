<?php

/**
 * @author   Demin Yin <deminy@deminy.net>
 * @license  MIT license
 */

namespace Behat\RestTestingContext;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\RestTestingExtension\Context\RestTestingAwareContext;
use Behat\RestTestingExtension\RestTestingHelper;
use Behat\WebApiExtension\Context\WebApiContext;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Base context.
 */
class BaseContext implements RestTestingAwareContext, SnippetAcceptingContext
{
    /**
     * @var WebApiContext
     */
    protected static $webApiContext;

    /**
     * @var Context[]
     */
    protected static $contexts = array();

    /**
     * Store data used across different contexts and steps.
     *
     * @var array
     */
    protected static $data = array();

    /**
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->addContext();
    }

    /**
     * Get data by field name, or return all data if no field name provided.
     *
     * @param string $name Field name.
     * @return mixed
     * @throws \Exception
     */
    public static function get($name = null)
    {
        if (!isset($name)) {
            return self::$data;
        } else {
            if (static::exists($name)) {
                return self::$data[$name];
            } else {
                throw new \Exception("Requested data field '{$name}' not exist.");
            }
        }
    }

    /**
     * Set value on given field name.
     *
     * @param string $name Field name.
     * @param mixed $value Field value.
     * @return void
     */
    public static function set($name, $value)
    {
        self::$data[$name] = $value;
    }

    /**
     * Check if specified field name exists or not.
     *
     * @param string $name Field name.
     * @return boolean
     */
    public static function exists($name)
    {
        return array_key_exists($name, self::$data);
    }

    /**
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name)
    {
        return self::get($name);
    }

    /**
     * @param string $name
     * @return mixed $value
     * @return $this
     * @throws Exception
     */
    public function __set($name, $value)
    {
        self::set($name, $value);

        return $this;
    }

    /**
     * @param Context $context
     * @return $this
     */
    protected function addContext(Context $context = null)
    {
        $context = $context ?: $this;
        self::$contexts[get_class($context)] = $context;

        return $this;
    }

    /**
     * @param string $name
     * @return Context|null
     */
    protected function getContext($name)
    {
        return (array_key_exists($name, self::$contexts) ? self::$contexts[$name] : null);
    }

    /**
     * @return WebApiContext
     */
    public static function getWebApiContext()
    {
        return self::$webApiContext;
    }

    /**
     * @param WebApiContext $webApiContext
     * @return void
     */
    public static function setWebApiContext(WebApiContext $webApiContext)
    {
        self::$webApiContext = $webApiContext;
    }

    /**
     * @return ResponseInterface
     */
    protected function getResponse()
    {
        return RestTestingHelper::getProperty(self::getWebApiContext(), 'response');
    }
}
