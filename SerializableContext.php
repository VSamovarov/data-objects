<?php

declare(strict_types=1);

/**
 * Manages the context of objects within an array transformation process,
 * allowing for circular reference detection and object tracking.
 *
 * This class is intentionally designed as a non-global context to ensure thread safety
 * in asynchronous and multi-threaded PHP environments. Each transformation process
 * maintains its own isolated context instance.
 *
 * It ensures objects are only processed once to prevent infinite loops caused by
 * circular references. This is especially useful in deep DTO graphs or nested structures.
 *
 * Internally, it uses SplObjectStorage to store references to objects being processed.
 *
 */

namespace Sam\DataObjects;

use Sam\DataObjects\Exceptions\CircularReferenceException;
use WeakMap;

/**
 * Handles object tracking and detection of circular references
 * during array conversion processes. Designed as a non-global context
 * to ensure thread safety in asynchronous and multi-threaded PHP environments.
 */
final class SerializableContext
{
    private WeakMap $visited;

    public function __construct()
    {
        $this->visited = new WeakMap();
    }

    /**
     * Checks if the object is already in the context.
     *
     * @param object $obj
     * @return bool
     */
    public function has(object $obj): bool
    {
        return $this->visited->offsetExists($obj);
    }

    /**
     * Attaches an object to the context.
     *
     * @param object $obj
     */
    public function attach(object $obj): void
    {
        $this->visited->offsetSet($obj, true);
    }

    /**
     * Removing the object from context.
     *
     * @param object $obj
     */
    public function detach(object $obj): void
    {
        $this->visited->offsetUnset($obj);
    }

    /**
     * Starts a circular reference detection cycle.
     * Checks if the object is in the context, if it is, throws an exception, if not, adds it
     * @param object $obj
     * @return void
     * @throws CircularReferenceException
     */
    public function startCircularDetect(object $obj): void
    {
        if ($this->has($obj)) {
            throw new CircularReferenceException('Circular reference detected for ' . get_class($obj));
        } else {
            $this->attach($obj);
        }
    }

    /**
     * Ends a circular reference detection cycle by removing the object from context.
     *
     * @param object $obj The object to be detached from circular detection.
     * @return void
     */
    public function endCircularDetect(object $obj): void
    {
        $this->detach($obj);
    }
}
