<?php

use Behat\RestTestingContext\BaseContext;

/**
 * Sample context class.
 */
class SampleContext extends BaseContext
{
    /**
     * @BeforeFeature
     */
    public static function beforeFeature()
    {
        file_put_contents(self::getDataFile(), '');
    }

    /**
     * @AfterFeature
     */
    public static function afterFeature()
    {
        file_put_contents(self::getDataFile(), '');
    }

    /**
     * @BeforeScenario
     */
    public static function beforeScenario()
    {
    }

    /**
     * @AfterScenario
     */
    public static function afterScenario()
    {
    }

    /**
     * @Then /^value "([^"]+)" should be an?( positive)? (int|integer).?$/
     * @param string $value
     * @param string $fieldProperty
     * @param string $fieldType
     * @return void
     * @throws \Exception
     */
    public function typeOfTheFieldIs($value, $fieldProperty, $fieldType)
    {
        switch (strtolower($fieldType)) {
            case 'int':
            case 'integer':
                if (empty($fieldProperty)) {
                    $regex = '/^(0|[1-9]\d*)$/';
                } elseif (strpos($fieldProperty, 'positive') !== false) {
                    $regex = '/^[1-9]\d*$/';
                } else {
                    throw new \Exception('Unsupported field property: ' . $fieldProperty);
                }

                if (! preg_match($regex, $value)) {
                    throw new \Exception(
                        sprintf(
                            'Value "%s" is not of the correct type: %s %s!',
                            $value,
                            $fieldProperty,
                            $fieldType
                        )
                    );
                }
                // TODO: We didn't check if the value is as expected here.
                break;
            default:
                throw new \Exception('Unsupported data type: ' . $fieldType);
                break;
        }
    }

    /**
     * @return string
     */
    protected static function getDataFile()
    {
        return __DIR__ . '/../../www/employees.json';
    }
}
