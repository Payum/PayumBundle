<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Fixture;

final class StatusListener
{
    /**
     * @var list<object>
     */
    public static array $events = [];

    public function __invoke(object $event): void
    {
        self::$events[] = $event;
    }
}
