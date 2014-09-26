<?php
/**
 * @author   Taylor Otwell <taylorotwell@gmail.com>
 * @author   Demin Yin <deminy@deminy.net>
 * @license  MIT license
 */
use Behat\Behat\Context\BehatContext;

require_once 'RestContext.php';

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    /**
     * Store data used across different subcontexts and steps.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array  $parameters  Context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->useContext('RestContext', new RestContext($parameters));

        /**
         * You may chain other contexts as sub-contexts of this main context via parameters. In this way all the
         * context classes may communicate with each other.
         */
        if (array_key_exists('subContexts', $parameters) && is_array($parameters['subContexts'])) {
            foreach ($parameters['subContexts'] as $subContext) {
                if (class_exists($subContext)) {
                    $this->useContext($subContext, new $subContext());
                } else {
                    throw new \Exception("Context '{$subContext}' doesn't exist.");
                }
            }
        }
    }

    /**
     * Get data by field name, or return all data if no field name provided.
     *
     * @param   string  $name  Field name.
     *
     * @return  mixed
     *
     * @throws \Exception
     */
    public function getData($name = null)
    {
        if (!isset($name)) {
            return $this->data;
        } elseif (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        throw new \Exception('Requested data not exist.');
    }

    /**
     * Set value on given field name.
     *
     * @param   string  $name   Field name.
     * @param   mixed   $value  Field value.
     *
     * @return  void
     */
    public function setData($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Check if specified field name exists or not.
     *
     * @param   string  $name  Field name.
     *
     * @return  mixed
     */
    public function dataExists($name)
    {
        return array_key_exists($name, $this->data);
    }
}
