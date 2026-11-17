<?php
/**
 * @author Marc Pantel <pantel.m@gmail.com>
 */

namespace Payum\Bundle\PayumBundle\Validator\Constraints;

use DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * CreditCardDateValidator
 *
 * Validate if the Credit Card is not expired
 */
class CreditCardDateValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (! ($value instanceof DateTime)) {
            $this->context->buildViolation($constraint->invalidMessage, [
                '{{ value }}' => $value,
            ])
                ->addViolation();

            return;
        }

        /**
         * The Credit Card is not expired until last day of the month
         */
        $value->modify('last day of this month');

        if (null !== $constraint->min && $value < $constraint->min) {
            $this->context->buildViolation($constraint->minMessage)
                ->atPath('expireAt')
                ->addViolation();
        }
    }
}
