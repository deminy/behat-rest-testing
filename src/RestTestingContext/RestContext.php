<?php

/**
 * @author   Demin Yin <deminy@deminy.net>
 * @license  MIT license
 */

namespace Behat\RestTestingContext;

use Behat\Behat\Context\Step;

/**
 * Rest context.
 */
class RestContext extends BaseContext
{
    /**
     * @Given /^the response should contain field "([^"]*)"$/
     * @param string $name
     * @return void
     * @throws \Exception
     */
    public function theResponseHasAField($name)
    {
        $message = sprintf("Field %s not found in response.", $name);
        if (!array_key_exists($name, $this->getResponseData())) {
            throw new \Exception($message);
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
        $message = sprintf("Field %s should not have been found in response, but was.", $name);
        if (array_key_exists($name, $this->getResponseData())) {
            throw new \Exception($message);
        }
    }

    /**
     * @Then /^field "([^"]+)" in the response should be "([^"]*)"$/
     * @param string $name
     * @param string $value
     * @return void
     * @throws \Exception
     */
    public function valueOfTheFieldEquals($name, $value)
    {
        $this->theResponseHasAField($name);
        $message = sprintf("%s was expected for %s, but %s found instead", $value, $name, $this->responseData[$name]);
        if ($this->responseData[$name] != $value) {
            throw new \Exception($message);
        }
    }

    /**
     * @Then /^field "([^"]+)" in the response should be an? (int|integer) "([^"]*)"$/
     * @param string $name
     * @param string $type
     * @param string $value
     * @return void
     * @throws \Exception
     * @todo Need to be better designed.
     */
    public function fieldIsOfTypeWithValue($name, $type, $value)
    {
        $this->valueOfTheFieldEquals($name, $value);

        switch (strtolower($type)) {
            case 'int':
            case 'integer':
                if (!preg_match('/^(0|[1-9]\d*)$/', $value)) {
                    throw new \Exception(
                        sprintf(
                            'Field "%s" is not of the correct type: %s!',
                            $name,
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

    /**
     * @Given /^the response should be "([^"]*)"$/
     * @param string $string
     * @return void
     * @throws \Exception
     */
    public function theResponseShouldBe($string)
    {
        $body = $this->getResponseBody();
        $message = sprintf("%s was expected for response body, but %s found instead", $string, $body);
        if ($body !== $string) {
            throw new \Exception($message);
        }
    }

    /**
     * @return string
     */
    public function getResponseBody()
    {
        return (string) $this->getResponse()->getBody();
    }

    /**
     * @return mixed
     */
    public function getResponseData()
    {
        return $this->decodeJson($this->getResponseBody());
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
        $json = json_decode($string, true);

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
