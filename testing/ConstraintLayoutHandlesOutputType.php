<?php

namespace YoudsFramework\Testing;
use PHPUnit\Framework\Constraint\Constraint;
use YoudsFramework\Layout;

/**
 * Constraint that checks if a View handles an expected Output Type.
 *
 * The View instance is passed to the constructor.
 *
 * @package    agavi
 * @subpackage testing
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class ConstraintLayoutHandlesOutputType extends Constraint
{
    /**
     * @var        Layout The layout instance.
     */
    protected $layoutInstance;

    /**
     * @var        bool Whether generic 'execute' methods should be accepted.
     */
    protected $acceptGeneric;

    /**
     * constructor
     *
     * @param Layout Instance of the Layout to test
     * @param bool      Whether generic execute methods should be accepted.
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     * @since      1.0.0
     */
    public function __construct(Layout $layoutInstance, $acceptGeneric = false)
    {
        $this->viewInstance = $layoutInstance;
        $this->acceptGeneric = $acceptGeneric;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns TRUE if the
     * constraint is met, FALSE otherwise.
     *
     * @param mixed Value or object to evaluate.
     *
     * @return     bool The result of the evaluation.
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     * @since      1.0.7
     */
    public function matches($other) :bool
    {
        $executeMethod = 'execute' . $other;
        if (is_callable(array($this->viewInstance, $executeMethod)) || ($this->acceptGeneric && is_callable(array($this->viewInstance, 'execute')))) {
            return true;
        }

        return false;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return     string The string representation.
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     * @since      1.0.0
     */
    public function toString() :string
    {
        return sprintf(
            '%1$s handles output type',

            get_class($this->viewInstance)
        );
    }

    /**
     * Returns a custom error description.
     *
     * @param mixed  Value or object to evaluate.
     * @param string The original description.
     * @param bool   true if the constraint was negated.
     *
     * @return     string The error description.
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     * @since      1.0.0
     */
    protected function customFailureDescription($other, $description, $not)
    {
        if ($not) {
            return sprintf(
                'Failed asserting that %1$s does not handle output type "%2$s".',
                get_class($this->viewInstance),
                $other
            );
        } else {
            return sprintf(
                'Failed asserting that %1$s handles output type "%2$s".',
                get_class($this->viewInstance),
                $other
            );
        }
    }
}

?>