<?php
namespace selio;

/**
 * An instance of this class is provided to each of the subscribed callables
 * as a first and only argument when called. Also provides static functionality
 * for event management.
 */
class Event extends Base {
    /**
     * Holds the event data that can be used and
     * manipulated by the subscriptions and then
     * retrieved by the event holder.
     * @var mixed
     */
    public $data;

    /**
     * Holds the subscriptions for the events.
     * @var array
     */
    private static $subscriptions = [];

    /**
     * The event name.
     * @var string
     */
    private $name = null;

    /**
     * The subscription callable that is currently called.
     * @var mixed
     */
    private $subscription = null;

    /**
     * The options used by the event object or provided to the subscriptions.
     * @var array
     */
    private $options = [];


    /**
     * @param string $name The name of the event.
     * @param mixed $data The event data that can be used by the subscriptions
     * and then retrieved by the event holder.
     */
    function __construct(string $name, $data, array $options = []) {
        $this->name = $name;
        $this->data = $data;
        $this->options = $options;
    }

    /**
     * Runs an event.
     * @param string $name The name of the event to run.
     * @param mixed $eventData The event data. An instance of Event can be
     * passed instead thus allowing to use objects extending Selio's Event
     * class when running the event.
     * @param array $eventOptions Options to be set on the Event object.
     */
    public final static function run(string $name, $eventData = null, array $eventOptions = []) : Event {
        $subs = self::$subscriptions[$name] ?? [];

        if(gettype($eventData) === 'object' && $eventData instanceof Event)
            $eventObj = $eventData;
        else
            $eventObj = new Event($name, $eventData, $eventOptions);

        foreach($subs as $subscription)
            $eventObj->call($subscription);

        return $eventObj;
    }

    /**
     * Subscribes a valid callable to an event.
     * @param string $eventName The name of the event to subscribe to.
     * @param string $sub The subscribing callable.
     */
    public final static function subscribe(string $eventName, callable $sub) : callable {
        self::$subscriptions[$eventName][] = $sub;
        return $sub;
    }

    /**
     * Calls a subscribed callable on the current event.
     * @param mixed $subscription The subscription callable.
     */
    public final function call(callable $subscription) {
        $this->subscription = $subscription;
        call_user_func($subscription, $this);
    }

    /**
     * Returns the name of the event.
     * @return string
     */
    public final function getName() : string {
        return $this->name;
    }

    /**
     * Returns the currently called subscription.
     * @return callable
     */
    public final function getSubscription() : callable {
        return $this->subscription;
    }
}