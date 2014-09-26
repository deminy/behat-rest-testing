<?php
/**
 * @author   Demin Yin <deminy@deminy.net>
 * @license  MIT license
 */
use Behat\Behat\Context\Step;

/**
 * Convert string "employee ID" to "employeeId".
 *
 * @param   string  $fieldName  Field name.
 *
 * @return  string
 */
function getVarName($fieldName)
{
    return lcfirst(
        implode(
            '',
            array_map(
                function($val) {
                    return ucfirst($val);
                },
                explode(' ', strtolower($fieldName))
            )
        )
    );
}

/**
 * @var Behat\Behat\Definition\Loader\ClosuredDefinitionLoader $steps
 */
$steps->Given(
    '/^that (.*) of the employee is "([^"]*)"$/',
    function(FeatureContext $world, $fieldName, $fieldValue) {
        $world->setData(getVarName($fieldName), $fieldValue);
    }
);

$steps->Given(
    '/^in the response (.*) of the employee is "([^"]*)"$/',
    function(FeatureContext $world, $fieldName, $fieldValue) {
        return array(
            new Step\Then(sprintf('field "%s" in the response should be "%s"', getVarName($fieldName), $fieldValue)),
        );
    }
);

/**
 * A demostration showing how to access sub-context and how to make assertions from under closured context.
 */
$steps->Given(
    '/^in the response there is no field called "([^"]*)"$/',
    function(FeatureContext $world, $fieldName) {
        $responseData = json_decode($world->getSubcontext('RestContext')->getResponse()->getBody(true));
        assertObjectNotHasAttribute($fieldName, $responseData);
    }
);

$steps->Then(
    '/^I\'m changing (.*) of the employee to "([^"]*)"$/',
    function(FeatureContext $world, $fieldName, $fieldValue) {
        $world->setData(getVarName($fieldName), $fieldValue);
    }
);
