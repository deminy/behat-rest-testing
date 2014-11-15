<?php
/**
 * @author   Taylor Otwell <taylorotwell@gmail.com>
 * @author   Demin Yin <deminy@deminy.net>
 * @license  MIT license
 */

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\Step;
use Guzzle\Http\Url;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;

/**
 * Rest context.
 */
class RestContext extends BehatContext
{
    const METHOD_DELETE = 'DELETE';
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';

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
     * Specifies if the response data should be an associative array or a nested stdClass object hierarchy.
     *
     * @var bool
     */
    protected $associative;

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
     * @param array $parameters Context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->client      = new Guzzle\Service\Client;
        $this->associative = (array_key_exists('associative', $parameters) ? $parameters['associative'] : true);
    }

    /**
     * @Given /^that I want to (delete|remove) an? /
     * @return void
     */
    public function thatIWantToDelete()
    {
        $this->requestMethod = self::METHOD_DELETE;
    }

    /**
     * @Given /^that I want to ((find|look for) an?|check) /
     * @return void
     */
    public function thatIWantToFind()
    {
        $this->requestMethod = self::METHOD_GET;
    }

    /**
     * @Given /^that I want to (add|create|make) an? (new )?/
     * @return void
     */
    public function thatIWantToMakeANew()
    {
        $this->requestMethod = self::METHOD_POST;
    }

    /**
     * @Given /^that I want to (change|update) (an?|that) /
     * @return void
     */
    public function thatIWantToUpdate()
    {
        $this->requestMethod = self::METHOD_PUT;
    }

    /**
     * @When /^I request "([^"]*)"$/
     * @param string $pageUrl
     * @return void
     * @throws \Exception
     */
    public function iRequest($pageUrl)
    {
        $this->responseData = $this->responseDecodeException = null;
        $this->responseIsJson = false;

        $url = Url::factory($this->getMainContext()->getParameter('base_url'))->combine($pageUrl);

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
    }

    /**
     * This public method is also for other context(s) to process REST API call and inject response into this context.
     *
     * @param \Guzzle\Http\Message\Response $response You may use this parameter to inject modified responses from other
     *                                                context.
     * @param boolean $asJson Process the response as JSON or not.
     * @return void
     */
    public function processResponse(\Guzzle\Http\Message\Response $response = null, $asJson = true)
    {
        if (!empty($response)) {
            $this->response = $response;
        }

        return $this->processResponseBody($this->response->getBody(true), $asJson);
    }

    /**
     * Process response body. This method may also be used by other context(s) to process REST API call and inject
     * response body into this context by using 2nd parameter $asJson.
     *
     * @param string $jsonData
     * @param boolean $asJson
     * @return void
     */
    protected function processResponseBody($jsonData, $asJson = true)
    {
        if ($asJson) {
            try {
                $this->responseData            = $this->decodeJson($jsonData);
                $this->responseIsJson          = true;
                $this->responseDecodeException = null;
            } catch (\Exception $e) {
                $this->responseData            = $jsonData;
                $this->responseIsJson          = false;
                $this->responseDecodeException = $e;
            }
        } else {
            $this->responseData            = $jsonData;
            $this->responseIsJson          = false;
            $this->responseDecodeException = null;
        }
    }

    /**
     * @Then /^the response is( not)? JSON$/
     * @param string $notJson
     * @return void
     * @throws \Exception
     */
    public function theResponseIsJson($notJson = '')
    {
        $this->processResponse();

        if (strpos($notJson, 'not') === false) {
            if (!$this->responseIsJson) {
                $message = "Response was not JSON\n";
                if (!empty($this->responseDecodeException)) {
                    $message .= $this->responseDecodeException->getMessage();
                }

                throw new \Exception($message . "\n" . $this->response);
            }
        } else {
            if ($this->responseIsJson) {
                throw new \Exception("Response was JSON\n" . $this->response);
            }
        }

    }

    /**
     * @Given /^the response should contain field "([^"]*)"$/
     * @param string $name
     * @return void
     * @throws \Exception
     */
    public function theResponseHasAField($name)
    {
        if ($this->responseIsJson) {
            if ($this->associative) {
                if (!(is_array($this->responseData)) || !array_key_exists($name, $this->responseData)) {
                    throw new \Exception('Field "' . $name . '" is not set!');
                }
            } else {
                if (!($this->responseData instanceof stdClass) || !property_exists($this->responseData, $name)) {
                    throw new \Exception('Field "' . $name . '" is not set!');
                }
            }
        } else {
            return new Step\Then('the response is JSON');
        }
    }

    /**
     * @Then /^in the response there is no field called "([^"]*)"$/
     * @param string $name
     * @return void
     * @throws \Exception
     */
    public function theResponseShouldNotHaveAField($name)
    {
        if ($this->responseIsJson) {
            if ($this->associative) {
                if (is_array($this->responseData) && array_key_exists($name, $this->responseData)) {
                    throw new \Exception('Field "' . $name . '" should not be there!');
                }
            } else {
                if (($this->responseData instanceof stdClass) && property_exists($this->responseData, $name)) {
                    throw new \Exception('Field "' . $name . '" should not be there!');
                }
            }
        } else {
            return new Step\Then('the response is JSON');
        }
    }

    /**
     * @Then /^field "([^"]+)" in the response should be "([^"]*)"$/
     * @param string $fieldName
     * @param string $fieldValue
     * @return void
     * @throws \Exception
     */
    public function valueOfTheFieldEquals($fieldName, $fieldValue)
    {
        if ($this->responseIsJson) {
            if (new Step\Given("the response should contain field \"{$fieldName}\"")) {
                if ($this->associative) {
                    if ($this->responseData[$fieldName] != $fieldValue) {
                        throw new \Exception(
                            sprintf(
                                'Field value mismatch! (given: "%s", match: "%s")',
                                $fieldValue,
                                $this->responseData[$fieldName]
                            )
                        );
                    }
                } else {
                    if ($this->responseData->$fieldName != $fieldValue) {
                        throw new \Exception(
                            sprintf(
                                'Field value mismatch! (given: "%s", match: "%s")',
                                $fieldValue,
                                $this->responseData->$fieldName
                            )
                        );
                    }
                }
            }
        } else {
            return new Step\Then('the response is JSON');
        }
    }

    /**
     * @Then /^the response should contain "([^"]*)"$/
     * @param string $str
     * @return void
     * @throws \Exception
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
     * @param string $fieldName
     * @param string $type
     * @param string $fieldValue
     * @return void
     * @throws \Exception
     * @todo Need to be better designed.
     */
    public function fieldIsOfTypeWithValue($fieldName, $type, $fieldValue)
    {
        if ($this->responseIsJson) {
            if (new Step\Given("the response should contain field \"{$fieldName}\"")) {
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
            }
        } else {
            return new Step\Then('the response is JSON');
        }
    }

    /**
     * @Then /^the response status code should be (\d+)$/
     * @param int $httpStatus
     * @return void
     * @throws \Exception
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
     * @param string $string
     * @return void
     * @throws \Exception
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
     * @return void
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
     * Decode JSON string.
     *
     * @param string $string A JSON string.
     * @return mixed
     * @throws \Exception
     * @see http://www.php.net/json_last_error
     */
    protected function decodeJson($string)
    {
        $json = json_decode($string, $this->associative);

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
