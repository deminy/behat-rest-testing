<?php
/**
 * @author   Taylor Otwell <taylorotwell@gmail.com>
 * @author   Demin Yin <deminy@deminy.net>
 * @license  MIT license
 */

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\ClosuredContextInterface;
use Behat\Behat\Context\Step;
use Guzzle\Http\Url;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Symfony\Component\Finder\Finder;

/**
 * Rest context.
 */
class RestContext extends BehatContext implements ClosuredContextInterface
{
    const METHOD_DELETE = 'DELETE';
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var Guzzle\Service\Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $requestMethod;

    /**
     * Used for debugging purpose only.
     * @var string
     */
    protected $requestUrl;

    /**
     * @var Guzzle\Http\Message\Response
     */
    protected $response;

    /**
     * Data decoded from HTTP response.
     * @var mixed
     */
    protected $responseData;

    /**
     * @var boolean
     */
    protected $responseIsJson;

    /**
     * @var \Exception
     */
    protected $responseDecodeException;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array  $parameters  Context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        if (empty($parameters)) {
            throw new \InvalidArgumentException('Parameters not loaded!');
        }

        $this->parameters = $parameters;
        $this->client	  = new Guzzle\Service\Client;
    }

    /**
     * Returns array of step definition files (*.php).
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    public function getStepDefinitionResources()
    {
        $path = $this->getResourcePath('steps') ?: (__DIR__ . '/../steps');

        return $this->getFiles($path);
    }

    /**
     * Returns array of hook definition files (*.php).
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    public function getHookDefinitionResources()
    {
        $path = $this->getResourcePath('hooks') ?: (__DIR__ . '/../support');

        return $this->getFiles($path);
    }

    /**
     * @Given /^that I want to (delete|remove) an? /
     */
    public function thatIWantToDelete()
    {
        $this->requestMethod = self::METHOD_DELETE;
    }

    /**
     * @Given /^that I want to ((find|look for) an?|check) /
     */
    public function thatIWantToFind()
    {
        $this->requestMethod = self::METHOD_GET;
    }

    /**
     * @Given /^that I want to (add|create|make) an? (new )?/
     */
    public function thatIWantToMakeANew()
    {
        $this->requestMethod = self::METHOD_POST;
    }

    /**
     * @Given /^that I want to (change|update) (an?|that) /
     */
    public function thatIWantToUpdate()
    {
        $this->requestMethod = self::METHOD_PUT;
    }

    /**
     * @When /^I request "([^"]*)"$/
     */
    public function iRequest($pageUrl)
    {
        $this->responseData = $this->responseDecodeException = null;
        $this->responseIsJson = false;

        $url = Url::factory($this->getParameter('base_url'))->combine($pageUrl);

        switch (strtoupper($this->requestMethod)) {
            case self::METHOD_GET:
            case self::METHOD_DELETE:
                $url->setQuery($url->getQuery()->merge($this->getMainContext()->getData()));
                $body = null;
                break;
            case self::METHOD_POST:
            case self::METHOD_PUT:
                $body = http_build_query($this->getMainContext()->getData());
                break;
            default:
                throw new \Exception('Unsupported RESTful request method: ' . $this->requestMethod);
                break;
        }

        $this->requestUrl = (string) $url;
        $method = strtolower($this->requestMethod);

        /**
         * @see http://guzzlephp.org/tour/http.html?highlight=badresponseexception#dealing-with-errors
         */
        try {
            $this->response = $this->client
                ->$method($this->requestUrl, null, $body)
                ->send()
            ;
        } catch (BadResponseException $e) {
            $this->response = $e->getResponse();
        } catch (ServerErrorResponseException $e) {
            $this->response = $e->getResponse();
        }

        $this->processResponse();
    }

    /**
     * This public method is also for other context(s) to process REST API call and inject response into this context.
     *
     * @param \Guzzle\Http\Message\Response $response
     * @return void
     */
    public function processResponse(\Guzzle\Http\Message\Response $response = null)
    {
        if ($response) {
            $this->response = $response;
        }

        return $this->processResponseBody($this->response->getBody(true));
    }

    /**
     * This public method is also for other context(s) to process REST API call and inject JSON response into this context.
     *
     * @param string $jsonData
     * @return void
     */
    public function processResponseBody($jsonData)
    {
        try {
            $this->responseData = $this->decodeJson($jsonData);
            $this->responseIsJson = true;
        } catch (\Exception $e) {
            $this->responseData = $jsonData;
            $this->responseIsJson = false;
            $this->responseDecodeException = $e;
        }
    }

    /**
     * @Then /^the response is JSON$/
     */
    public function theResponseIsJson()
    {
        if (!$this->responseIsJson) {
            throw new \Exception(
                "Response was not JSON\n" . $this->responseDecodeException->getMessage() . "\n" . $this->response
            );
        }
    }

    /**
     * @Then /^the response is not JSON$/
     */
    public function theResponseIsNotJson()
    {
        if ($this->responseIsJson) {
            throw new \Exception("Response was JSON\n" . $this->response);
        }
    }

    /**
     * @Given /^the response should contain field "([^"]*)"$/
     */
    public function theResponseHasAField($name)
    {
        if ($this->responseIsJson) {
            if (!($this->responseData instanceof stdClass) || !property_exists($this->responseData, $name)) {
                throw new \Exception('Field "' . $name . '" is not set!');
            }
        } else {
            return new Step\Then('the response is JSON');
        }
    }

    /**
     * @Then /^in the response there is no field called "([^"]*)"$/
     */
    public function theResponseShouldNotHaveAField($name)
    {
        if ($this->responseIsJson) {
            if (($this->responseData instanceof stdClass) && property_exists($this->responseData, $name)) {
                throw new \Exception('Field "' . $name . '" should not be there!');
            }
        } else {
            return new Step\Then('the response is JSON');
        }
    }

    /**
     * @Then /^field "([^"]+)" in the response should be "([^"]*)"$/
     */
    public function valueOfTheFieldEquals($fieldName, $fieldValue)
    {
        if ($this->responseIsJson) {
            if (!($this->responseData instanceof stdClass) || !isset($this->responseData->$fieldName)) {
                return new Step\Then(sprintf('the response should contain field "%s"', $fieldName));
            }

            if ($this->responseData->$fieldName != $fieldValue) {
                throw new \Exception(
                    sprintf(
                        'Field value mismatch! (given: "%s", match: "%s")',
                        $fieldValue,
                        $this->responseData->$fieldName
                    )
                );
            }
        } else {
            return new Step\Then('the response is JSON');
        }
    }

    /**
     * @Then /^the response should contain "([^"]*)"$/
     */
    public function theResponseShouldContain($str)
    {
        if (!$this->responseIsJson) {
            if (strpos($this->responseData, $str) === false) {
                throw new \Exception(sprintf('String "%s" not found.', $str));
            }
        } else {
            throw new \Exception('Response should not be a JSON message.');
        }
    }

    /**
     * @Then /^field "([^"]+)" in the response should be an? (int|integer) "([^"]*)"$/
     *
     * @todo Need to be better designed.
     */
    public function fieldIsOfTypeWithValue($fieldName, $type, $fieldValue)
    {
        if ($this->responseIsJson) {
            if (!($this->responseData instanceof stdClass) || !isset($this->responseData->$fieldName)) {
                return new Step\Then(sprintf('the response should contain field "%s"', $fieldName));
            }

            switch (strtolower($type)) {
                case 'int':
                case 'integer':
                    if (!preg_match('/^(0|[1-9]\d*)$/', $fieldValue)) {
                        throw new \Exception(
                            sprintf(
                                'Field "%s" is not of the correct type: %s!',
                                $fieldName,
                                $type
                            )
                        );
                    }
                    // TODO: We didn't check if the value is as expected here.
                    break;
                default:
                    throw new \Exception('Unsupported data type: ' . $type);
                    break;
            }
        } else {
            return new Step\Then('the response is JSON');
        }
    }

    /**
     * @Then /^the response status code should be (\d+)$/
     */
    public function theResponseStatusCodeShouldBe($httpStatus)
    {
        if (((string) $this->response->getStatusCode()) !== $httpStatus) {
            throw new \Exception(
                sprintf(
                    'HTTP code does not match %s (actual: %s)',
                    $httpStatus,
                    $this->response->getStatusCode()
                )
            );
        }
    }

    /**
     * @Given /^the response should be "([^"]*)"$/
     */
    public function theResponseShouldBe($string)
    {
        $data = $this->response->getBody(true);

        if ($string != $data) {
            throw new \Exception(
                sprintf("Unexpected response.\nExpected response:%s\nActual response:\n%s" . $string, $data)
            );
        }
    }

    /**
     * @Then /^echo last response$/
     */
    public function echoLastResponse()
    {
        $this->printDebug($this->requestUrl . "\n\n" . $this->response);
    }

    /**
     * Return the response object.
     *
     * This public method is also for other context(s) to get and process REST API response.
     *
     * @return Guzzle\Http\Message\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Return the response data.
     *
     * This public method is also for other context(s) to get and process REST API response.
     *
     * @return mixed
     */
    public function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * This public method is also for other context(s) to set parameter(s) into this context.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Get context parameter.
     *
     * @param   string  $name  Parameter name.
     *
     * @return  mixed
     */
    protected function getParameter($name)
    {
        return array_key_exists($name, $this->parameters) ? $this->parameters[$name] : null;
    }

    /**
     * Returns path that points to specified resources.
     *
     * @param   string  $type  Resource type. Either 'boostrap', 'steps' or 'hooks'.
     *
     * @return string Return path back, or NULL if not defined.
     *
     * @throws \RuntimeException
     */
    protected function getResourcePath($type)
    {
        $paths = $this->getParameter('paths');

        if (array_key_exists($type, $paths)) {
            $pathBase = array_key_exists('base', $paths) ? $paths['base'] : '';
            $pathType = $paths[$type];

            // Check if it's an absolute path.
            if (substr($pathType, 0, 1) == '/') {
                if (empty($pathBase)) {
                    return $pathType;
                } else {
                    throw new \RuntimeException(
                        sprintf('You may only use relative path for type "%s" when base path is presented.', $type)
                    );
                }
            } else {
                // TODO: check if there is a trailing directory separator in the base path.
                return $paths['base'] . '/' . $pathType;
            }
        }

        return null;
    }

    /**
     * Get files of certain type under specified directory.
     *
     * @param   string  $dir  A directory.
     * @param   string  $ext  File extension.
     *
     * @return  array
     *
     * @throws \InvalidArgumentException
     */
    protected function getFiles($dir, $ext = 'php')
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('Given path "%s" is not a directory.', $dir));
        }

        if (!is_readable($dir)) {
            throw new \InvalidArgumentException(sprintf('Given path "%s" is not readable.', $dir));
        }

        if (!preg_match('/^[0-9a-z]+$/i', $ext)) {
            throw new \InvalidArgumentException(
                sprintf('Given file extension "%s" is invalid (may only contain digits and/or letters).', $dir)
            );
        }

        $finder = new Finder;

        return $finder->files()->name('*.' . $ext)->in($dir);
    }

    /**
     * Decode JSON string.
     *
     * @param   string  $string  A JSON string.
     *
     * @return  mixed
     *
     * @see http://www.php.net/json_last_error
     * @throws \Exception
     */
    protected function decodeJson($string)
    {
        $json = json_decode($string);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $json;
                break;
            case JSON_ERROR_DEPTH:
                $message = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $message = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $message = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $message = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $message = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $message = 'Unknown error';
                break;
        }

        throw new \Exception('JSON decoding error: ' . $message);
    }
}
