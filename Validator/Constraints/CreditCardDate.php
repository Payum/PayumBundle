<?php
/**
 * @author Marc Pantel <pantel.m@gmail.com>
 */

namespace Payum\Bundle\PayumBundle\Validator\Constraints;

use DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * CreditCardDate
 */
class CreditCardDate extends Constraint
{
    public $minMessage = 'validator.credit_card.invalidDate';

    public $invalidMessage = 'validator.credit_card.invalidDate';

    public $min;

    public function __construct(
        string|array|null $min = null,
        ?string $minMessage = null,
        ?string $invalidMessage = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        // Handle BC: if $min is an array, it's old-style options
        if (is_array($min)) {
            $options = $min;
            $minValue = $options['min'] ?? null;
            $minMessage = $options['minMessage'] ?? $minMessage;
            $invalidMessage = $options['invalidMessage'] ?? $invalidMessage;
            parent::__construct($options, $groups, $payload);
        } else {
            $minValue = $min;
            parent::__construct([], $groups, $payload);
        }

        if ($minValue !== null) {
            $this->min = $minValue;
        }
        if ($minMessage !== null) {
            $this->minMessage = $minMessage;
        }
        if ($invalidMessage !== null) {
            $this->invalidMessage = $invalidMessage;
        }

        if (null === $this->min) {
            throw new MissingOptionsException('Either option "min" must be given for constraint ' . self::class, ['min']);
        }

        $this->min = new DateTime($this->min);
        $this->min->modify('last day of this month');
    }
}
