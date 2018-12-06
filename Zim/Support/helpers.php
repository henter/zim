<?php
namespace Zim\Support;

/**
 * Assign high numeric IDs to a config item to force appending.
 *
 * @param  array  $array
 * @return array
 */
function append_config(array $array)
{
    $start = 9999;

    foreach ($array as $key => $value) {
        if (is_numeric($key)) {
            $start++;

            $array[$start] = Arr::pull($array, $key);
        }
    }

    return $array;
}

/**
 * Add an element to an array using "dot" notation if it doesn't exist.
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $value
 * @return array
 */
function array_add($array, $key, $value)
{
    return Arr::add($array, $key, $value);
}

/**
 * Collapse an array of arrays into a single array.
 *
 * @param  array  $array
 * @return array
 */
function array_collapse($array)
{
    return Arr::collapse($array);
}

/**
 * Divide an array into two arrays. One with keys and the other with values.
 *
 * @param  array  $array
 * @return array
 */
function array_divide($array)
{
    return Arr::divide($array);
}

/**
 * Flatten a multi-dimensional associative array with dots.
 *
 * @param  array   $array
 * @param  string  $prepend
 * @return array
 */
function array_dot($array, $prepend = '')
{
    return Arr::dot($array, $prepend);
}

/**
 * Get all of the given array except for a specified array of keys.
 *
 * @param  array  $array
 * @param  array|string  $keys
 * @return array
 */
function array_except($array, $keys)
{
    return Arr::except($array, $keys);
}

/**
 * Return the first element in an array passing a given truth test.
 *
 * @param  array  $array
 * @param  callable|null  $callback
 * @param  mixed  $default
 * @return mixed
 */
function array_first($array, callable $callback = null, $default = null)
{
    return Arr::first($array, $callback, $default);
}

/**
 * Flatten a multi-dimensional array into a single level.
 *
 * @param  array  $array
 * @param  int  $depth
 * @return array
 */
function array_flatten($array, $depth = INF)
{
    return Arr::flatten($array, $depth);
}

/**
 * Remove one or many array items from a given array using "dot" notation.
 *
 * @param  array  $array
 * @param  array|string  $keys
 * @return void
 */
function array_forget(&$array, $keys)
{
    Arr::forget($array, $keys);
}

/**
 * Get an item from an array using "dot" notation.
 *
 * @param  \ArrayAccess|array  $array
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
function array_get($array, $key, $default = null)
{
    return Arr::get($array, $key, $default);
}

/**
 * Check if an item or items exist in an array using "dot" notation.
 *
 * @param  \ArrayAccess|array  $array
 * @param  string|array  $keys
 * @return bool
 */
function array_has($array, $keys)
{
    return Arr::has($array, $keys);
}

/**
 * Return the last element in an array passing a given truth test.
 *
 * @param  array  $array
 * @param  callable|null  $callback
 * @param  mixed  $default
 * @return mixed
 */
function array_last($array, callable $callback = null, $default = null)
{
    return Arr::last($array, $callback, $default);
}

/**
 * Get a subset of the items from the given array.
 *
 * @param  array  $array
 * @param  array|string  $keys
 * @return array
 */
function array_only($array, $keys)
{
    return Arr::only($array, $keys);
}

/**
 * Pluck an array of values from an array.
 *
 * @param  array   $array
 * @param  string|array  $value
 * @param  string|array|null  $key
 * @return array
 */
function array_pluck($array, $value, $key = null)
{
    return Arr::pluck($array, $value, $key);
}

/**
 * Push an item onto the beginning of an array.
 *
 * @param  array  $array
 * @param  mixed  $value
 * @param  mixed  $key
 * @return array
 */
function array_prepend($array, $value, $key = null)
{
    return Arr::prepend($array, $value, $key);
}

/**
 * Get a value from the array, and remove it.
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
function array_pull(&$array, $key, $default = null)
{
    return Arr::pull($array, $key, $default);
}

/**
 * Get a random value from an array.
 *
 * @param  array  $array
 * @param  int|null  $num
 * @return mixed
 */
function array_random($array, $num = null)
{
    return Arr::random($array, $num);
}

/**
 * Set an array item to a given value using "dot" notation.
 *
 * If no key is given to the method, the entire array will be replaced.
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $value
 * @return array
 */
function array_set(&$array, $key, $value)
{
    return Arr::set($array, $key, $value);
}

/**
 * Sort the array by the given callback or attribute name.
 *
 * @param  array  $array
 * @param  callable|string|null  $callback
 * @return array
 */
function array_sort($array, $callback = null)
{
    return Arr::sort($array, $callback);
}

/**
 * Recursively sort an array by keys and values.
 *
 * @param  array  $array
 * @return array
 */
function array_sort_recursive($array)
{
    return Arr::sortRecursive($array);
}

/**
 * Filter the array using the given callback.
 *
 * @param  array  $array
 * @param  callable  $callback
 * @return array
 */
function array_where($array, callable $callback)
{
    return Arr::where($array, $callback);
}

/**
 * If the given value is not an array, wrap it in one.
 *
 * @param  mixed  $value
 * @return array
 */
function array_wrap($value)
{
    return Arr::wrap($value);
}

/**
 * Determine if the given value is "blank".
 *
 * @param  mixed  $value
 * @return bool
 */
function blank($value)
{
    if (is_null($value)) {
        return true;
    }

    if (is_string($value)) {
        return trim($value) === '';
    }

    if (is_numeric($value) || is_bool($value)) {
        return false;
    }

    return empty($value);
}

/**
 * Convert a value to camel case.
 *
 * @param  string  $value
 * @return string
 */
function camel_case($value)
{
    return Str::camel($value);
}

/**
 * Get the class "basename" of the given object / class.
 *
 * @param  string|object  $class
 * @return string
 */
function class_basename($class)
{
    $class = is_object($class) ? get_class($class) : $class;

    return basename(str_replace('\\', '/', $class));
}

/**
 * Returns all traits used by a class, its parent classes and trait of their traits.
 *
 * @param  object|string  $class
 * @return array
 */
function class_uses_recursive($class)
{
    if (is_object($class)) {
        $class = get_class($class);
    }

    $results = [];

    foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
        $results += trait_uses_recursive($class);
    }

    return array_unique($results);
}

/**
 * Create a collection from the given value.
 *
 * @param  mixed  $value
 * @return \Zim\Support\Collection
 */
function collect($value = null)
{
    return new Collection($value);
}

/**
 * Fill in data where it's missing.
 *
 * @param  mixed   $target
 * @param  string|array  $key
 * @param  mixed  $value
 * @return mixed
 */
function data_fill(&$target, $key, $value)
{
    return data_set($target, $key, $value, false);
}

/**
 * Get an item from an array or object using "dot" notation.
 *
 * @param  mixed   $target
 * @param  string|array  $key
 * @param  mixed   $default
 * @return mixed
 */
function data_get($target, $key, $default = null)
{
    if (is_null($key)) {
        return $target;
    }

    $key = is_array($key) ? $key : explode('.', $key);

    while (! is_null($segment = array_shift($key))) {
        if ($segment === '*') {
            if ($target instanceof Collection) {
                $target = $target->all();
            } elseif (! is_array($target)) {
                return value($default);
            }

            $result = [];

            foreach ($target as $item) {
                $result[] = data_get($item, $key);
            }

            return in_array('*', $key) ? Arr::collapse($result) : $result;
        }

        if (Arr::accessible($target) && Arr::exists($target, $segment)) {
            $target = $target[$segment];
        } elseif (is_object($target) && isset($target->{$segment})) {
            $target = $target->{$segment};
        } else {
            return value($default);
        }
    }

    return $target;
}

/**
 * Set an item on an array or object using dot notation.
 *
 * @param  mixed  $target
 * @param  string|array  $key
 * @param  mixed  $value
 * @param  bool  $overwrite
 * @return mixed
 */
function data_set(&$target, $key, $value, $overwrite = true)
{
    $segments = is_array($key) ? $key : explode('.', $key);

    if (($segment = array_shift($segments)) === '*') {
        if (! Arr::accessible($target)) {
            $target = [];
        }

        if ($segments) {
            foreach ($target as &$inner) {
                data_set($inner, $segments, $value, $overwrite);
            }
        } elseif ($overwrite) {
            foreach ($target as &$inner) {
                $inner = $value;
            }
        }
    } elseif (Arr::accessible($target)) {
        if ($segments) {
            if (! Arr::exists($target, $segment)) {
                $target[$segment] = [];
            }

            data_set($target[$segment], $segments, $value, $overwrite);
        } elseif ($overwrite || ! Arr::exists($target, $segment)) {
            $target[$segment] = $value;
        }
    } elseif (is_object($target)) {
        if ($segments) {
            if (! isset($target->{$segment})) {
                $target->{$segment} = [];
            }

            data_set($target->{$segment}, $segments, $value, $overwrite);
        } elseif ($overwrite || ! isset($target->{$segment})) {
            $target->{$segment} = $value;
        }
    } else {
        $target = [];

        if ($segments) {
            data_set($target[$segment], $segments, $value, $overwrite);
        } elseif ($overwrite) {
            $target[$segment] = $value;
        }
    }

    return $target;
}

/**
 * Determine if a given string ends with a given substring.
 *
 * @param  string  $haystack
 * @param  string|array  $needles
 * @return bool
 */
function ends_with($haystack, $needles)
{
    return Str::endsWith($haystack, $needles);
}

/**
 * Gets the value of an environment variable.
 *
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
function env($key, $default = null)
{
    $value = getenv($key);

    if ($value === false) {
        return value($default);
    }

    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return;
    }

    if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
        return substr($value, 1, -1);
    }

    return $value;
}

/**
 * Determine if a value is "filled".
 *
 * @param  mixed  $value
 * @return bool
 */
function filled($value)
{
    return ! blank($value);
}

/**
 * Get the first element of an array. Useful for method chaining.
 *
 * @param  array  $array
 * @return mixed
 */
function head($array)
{
    return reset($array);
}

/**
 * Convert a string to kebab case.
 *
 * @param  string  $value
 * @return string
 */
function kebab_case($value)
{
    return Str::kebab($value);
}

/**
 * Get the last element from an array.
 *
 * @param  array  $array
 * @return mixed
 */
function last($array)
{
    return end($array);
}

/**
 * Get an item from an object using "dot" notation.
 *
 * @param  object  $object
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
function object_get($object, $key, $default = null)
{
    if (is_null($key) || trim($key) == '') {
        return $object;
    }

    foreach (explode('.', $key) as $segment) {
        if (! is_object($object) || ! isset($object->{$segment})) {
            return value($default);
        }

        $object = $object->{$segment};
    }

    return $object;
}

/**
 * Provide access to optional objects.
 *
 * @param  mixed  $value
 * @param  callable|null  $callback
 * @return mixed
 */
function optional($value = null, callable $callback = null)
{
    if (is_null($callback)) {
        return new Optional($value);
    } elseif (! is_null($value)) {
        return $callback($value);
    }
}

/**
 * Replace a given pattern with each value in the array in sequentially.
 *
 * @param  string  $pattern
 * @param  array   $replacements
 * @param  string  $subject
 * @return string
 */
function preg_replace_array($pattern, array $replacements, $subject)
{
    return preg_replace_callback($pattern, function () use (&$replacements) {
        foreach ($replacements as $key => $value) {
            return array_shift($replacements);
        }
    }, $subject);
}

/**
 * Retry an operation a given number of times.
 *
 * @param  int  $times
 * @param  callable  $callback
 * @param  int  $sleep
 * @return mixed
 *
 * @throws \Exception
 */
function retry($times, callable $callback, $sleep = 0)
{
    $times--;

    beginning:
    try {
        return $callback();
    } catch (\Exception $e) {
        if (! $times) {
            throw $e;
        }

        $times--;

        if ($sleep) {
            usleep($sleep * 1000);
        }

        goto beginning;
    }
}

/**
 * Convert a string to snake case.
 *
 * @param  string  $value
 * @param  string  $delimiter
 * @return string
 */
function snake_case($value, $delimiter = '_')
{
    return Str::snake($value, $delimiter);
}

/**
 * Determine if a given string starts with a given substring.
 *
 * @param  string  $haystack
 * @param  string|array  $needles
 * @return bool
 */
function starts_with($haystack, $needles)
{
    return Str::startsWith($haystack, $needles);
}

/**
 * Return the remainder of a string after a given value.
 *
 * @param  string  $subject
 * @param  string  $search
 * @return string
 */
function str_after($subject, $search)
{
    return Str::after($subject, $search);
}

/**
 * Get the portion of a string before a given value.
 *
 * @param  string  $subject
 * @param  string  $search
 * @return string
 */
function str_before($subject, $search)
{
    return Str::before($subject, $search);
}

/**
 * Determine if a given string contains a given substring.
 *
 * @param  string  $haystack
 * @param  string|array  $needles
 * @return bool
 */
function str_contains($haystack, $needles)
{
    return Str::contains($haystack, $needles);
}

/**
 * Cap a string with a single instance of a given value.
 *
 * @param  string  $value
 * @param  string  $cap
 * @return string
 */
function str_finish($value, $cap)
{
    return Str::finish($value, $cap);
}

/**
 * Determine if a given string matches a given pattern.
 *
 * @param  string|array  $pattern
 * @param  string  $value
 * @return bool
 */
function str_is($pattern, $value)
{
    return Str::is($pattern, $value);
}

/**
 * Limit the number of characters in a string.
 *
 * @param  string  $value
 * @param  int     $limit
 * @param  string  $end
 * @return string
 */
function str_limit($value, $limit = 100, $end = '...')
{
    return Str::limit($value, $limit, $end);
}

/**
 * Generate a more truly "random" alpha-numeric string.
 *
 * @param  int  $length
 * @return string
 *
 * @throws \RuntimeException
 */
function str_random($length = 16)
{
    return Str::random($length);
}

/**
 * Replace a given value in the string sequentially with an array.
 *
 * @param  string  $search
 * @param  array   $replace
 * @param  string  $subject
 * @return string
 */
function str_replace_array($search, array $replace, $subject)
{
    return Str::replaceArray($search, $replace, $subject);
}

/**
 * Replace the first occurrence of a given value in the string.
 *
 * @param  string  $search
 * @param  string  $replace
 * @param  string  $subject
 * @return string
 */
function str_replace_first($search, $replace, $subject)
{
    return Str::replaceFirst($search, $replace, $subject);
}

/**
 * Replace the last occurrence of a given value in the string.
 *
 * @param  string  $search
 * @param  string  $replace
 * @param  string  $subject
 * @return string
 */
function str_replace_last($search, $replace, $subject)
{
    return Str::replaceLast($search, $replace, $subject);
}

/**
 * Generate a URL friendly "slug" from a given string.
 *
 * @param  string  $title
 * @param  string  $separator
 * @param  string  $language
 * @return string
 */
function str_slug($title, $separator = '-', $language = 'en')
{
    return Str::slug($title, $separator, $language);
}

/**
 * Begin a string with a single instance of a given value.
 *
 * @param  string  $value
 * @param  string  $prefix
 * @return string
 */
function str_start($value, $prefix)
{
    return Str::start($value, $prefix);
}

/**
 * Convert a value to studly caps case.
 *
 * @param  string  $value
 * @return string
 */
function studly_case($value)
{
    return Str::studly($value);
}

/**
 * Call the given Closure with the given value then return the value.
 *
 * @param  mixed  $value
 * @param  callable|null  $callback
 * @return mixed
 */
function tap($value, $callback = null)
{
    if (is_null($callback)) {
        return new HigherOrderTapProxy($value);
    }

    $callback($value);

    return $value;
}

/**
 * Throw the given exception if the given condition is true.
 *
 * @param  mixed  $condition
 * @param  \Throwable|string  $exception
 * @param  array  ...$parameters
 * @return mixed
 *
 * @throws \Throwable
 */
function throw_if($condition, $exception, ...$parameters)
{
    if ($condition) {
        throw (is_string($exception) ? new $exception(...$parameters) : $exception);
    }

    return $condition;
}

/**
 * Throw the given exception unless the given condition is true.
 *
 * @param  mixed  $condition
 * @param  \Throwable|string  $exception
 * @param  array  ...$parameters
 * @return mixed
 * @throws \Throwable
 */
function throw_unless($condition, $exception, ...$parameters)
{
    if (! $condition) {
        throw (is_string($exception) ? new $exception(...$parameters) : $exception);
    }

    return $condition;
}

/**
 * Convert a value to title case.
 *
 * @param  string  $value
 * @return string
 */
function title_case($value)
{
    return Str::title($value);
}

/**
 * Returns all traits used by a trait and its traits.
 *
 * @param  string  $trait
 * @return array
 */
function trait_uses_recursive($trait)
{
    $traits = class_uses($trait);

    foreach ($traits as $trait) {
        $traits += trait_uses_recursive($trait);
    }

    return $traits;
}

/**
 * Transform the given value if it is present.
 *
 * @param  mixed  $value
 * @param  callable  $callback
 * @param  mixed  $default
 * @return mixed|null
 */
function transform($value, callable $callback, $default = null)
{
    if (filled($value)) {
        return $callback($value);
    }

    if (is_callable($default)) {
        return $default($value);
    }

    return $default;
}

/**
 * Return the default value of the given value.
 *
 * @param  mixed  $value
 * @return mixed
 */
function value($value)
{
    return $value instanceof \Closure ? $value() : $value;
}

/**
 * Return the given value, optionally passed through the given callback.
 *
 * @param  mixed  $value
 * @param  callable|null  $callback
 * @return mixed
 */
function with($value, callable $callback = null)
{
    return is_null($callback) ? $value : $callback($value);
}
