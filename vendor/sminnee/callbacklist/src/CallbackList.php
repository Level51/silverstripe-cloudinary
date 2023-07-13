<?php declare(strict_types = 1);

namespace Sminnee\CallbackList;

// @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint

/**
 * Manages a list of callbacks that can be called sequentially with {@link call()}
 */
class CallbackList
{
    /** @var array<callable> */
    private $callbacks = [];

    /**
     * Add a callback to the end of the list
     *
     * @param callable $callback The callback to call. The arguments from {@link call()} will be passed
     * @param string $name An optional name to pass to get() and remove() in future
     */
    public function add(callable $callback, ?string $name = null): void
    {
        if ($name !== null) {
            $this->callbacks[$name] = $callback;
        } else {
            $this->callbacks[] = $callback;
        }
    }


    /**
     * Remove a named callback.
     *
     * @param string $name The name originally passed in add()
     * @return bool True if successfully removed, false if not found
     */
    public function remove(string $name): bool
    {
        if (!\array_key_exists($name, $this->callbacks)) {
            return false;
        }

        unset($this->callbacks[$name]);

        return true;
    }


    /**
     * Clear the callback list
     */
    public function clear(): void
    {
        $this->callbacks = [];
    }


    /**
     * Get a named callback
     *
     * @param string $name name originally passed in add()
     * @return callable The callback
     */
    public function get(string $name): ?callable
    {
        if (!\array_key_exists($name, $this->callbacks)) {
            return null;
        }

        return $this->callbacks[$name];
    }


    /**
     * Get all callbacks
     *
     * @return array<callable>
     */
    public function getAll(): array
    {
        return \array_values($this->callbacks);
    }


    /**
     * Call all the callbacks, passing the given arguments to each of them
     *
     * @param mixed ...$args
     * @return array<mixed>
     */
    public function call(...$args): array
    {
        /** @var array<mixed> $results */
        $results = [];
        foreach ($this->callbacks as $callback) {
            $results[] = \call_user_func_array($callback, $args);
        }

        return $results;
    }


    /**
     * Treat CallbackList as a callable
     *
     * @param mixed ...$args
     * @return array<mixed>
     */
    public function __invoke(...$args): array
    {
        return \call_user_func_array([$this, 'call'], $args);
    }
}
