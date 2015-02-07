<?php

/**
 * @author   Demin Yin <deminy@deminy.net>
 * @license  MIT license
 */

namespace Behat\RestTestingExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Behat\RestTestingContext\BaseContext;
use Behat\RestTestingContext\RestContext;
use Behat\WebApiExtension\Context\WebApiContext;

/**
 * RestTesting-aware contexts initializer.
 *
 * Make RestTestingContext accessable in all RestTestingContext contexts.
 */
class RestTestingAwareInitializer implements ContextInitializer
{
    /**
     * Initializes provided context.
     *
     * @param Context $context
     * @return void
     */
    public function initializeContext(Context $context)
    {
        if ($context instanceof WebApiContext) {
            BaseContext::setWebApiContext($context);
        } elseif ($context instanceof RestContext) {
            BaseContext::setRestContext($context);
        }
    }
}
