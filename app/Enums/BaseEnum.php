<?php

namespace App\Enums;

use BadMethodCallException;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use JsonSerializable;
use ReflectionClass;
use ReflectionException;

abstract class BaseEnum implements CastsAttributes, JsonSerializable
{
    protected static array $cache = [];

    protected mixed $value = null;

    final public function __construct()
    {
    }

    public function make($value): static
    {
        if ($value instanceof static) {
            $this->value = $value->getValue();

            return $this;
        }

        if ($value !== null && ! $this->isValid($value)) {
            Log::error(sprintf('Value %s is not part of the enum %s', json_encode($value), get_called_class()));
        } else {
            $this->value = $value;
        }

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get array of options with indexes "key", "label"
     */

    public static function options(): array
{
    $result = [];

    foreach (static::toArray() as $key => $value) {
        $result[] = [
            'value' => $value,
            'label' => static::getLabel($value),
        ];
    }

    return $result;
}

    /**
     * Check if is valid enum value
     */
    public static function isValid($value): bool
    {
        return in_array($value, static::toArray(), true);
    }

    public static function toArray(bool $includeDefault = false): array
    {
        $class = get_called_class();
        if (! isset(static::$cache[$class])) {
            try {
                $reflection = new ReflectionClass($class);
                static::$cache[$class] = $reflection->getConstants();
            } catch (ReflectionException $exception) {
                Log::error(sprintf('Error getting constants for class %s: %s', $class, $exception->getMessage()));
                static::$cache[$class] = [];
            }
        }

        $result = static::$cache[$class];

        if (isset($result['__default']) && ! $includeDefault) {
            unset($result['__default']);
        }

        return $result;
    }

    /**
     * Returns the names (keys) of all constants in the Enum class
     */
    public static function keys(): array
    {
        return array_keys(static::toArray());
    }

    /**
     * Returns instances of the Enum class of all Enum constants
     *
     * @return static[] Constant name in key, Enum instance in value
     */
    public static function values(): array
    {
        $values = [];

        foreach (static::toArray() as $key => $value) {
            $values[$key] = (new static())->make($value);
        }

        return $values;
    }

    /**
     * Returns a value when called statically like so: MyEnum::SOME_VALUE() given SOME_VALUE is a class constant
     *
     *
     * @return static
     *
     * @throws BadMethodCallException
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $array = static::toArray();

        if (isset($array[$name]) || array_key_exists($name, $array)) {
            return (new static())->make($array[$name]);
        }

        throw new BadMethodCallException('No static method or enum constant ' . $name . ' in class ' . get_called_class());
    }

    public static function labels(): array
    {
        $result = [];

        foreach (static::toArray() as $value) {
            $result[$value] = static::getLabel($value);
        }

        return $result;
    }

    public static function getLabel(?string $value): ?string
    {
        return translate(ucfirst(str_replace('_', ' ', $value)));
    }

    /**
     * Returns the enum key (i.e. the constant name).
     *
     * @return false|int|string
     */
    public function getKey(): bool|int|string
    {
        return static::search($this->value);
    }

    /**
     * Return key for value
     *
     * @param  string|int  $value
     * @return false|int|string
     */
    public static function search($value): bool|int|string
    {
        return array_search($value, static::toArray(), true);
    }

    public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * Compares one Enum with another.
     *
     * @return bool True if Enums are equal, false if not equal
     */
    final public function equals(?BaseEnum $enum = null): bool
    {
        return $enum !== null && $this->getValue() === $enum->getValue() && get_called_class() === get_class($enum);
    }

    /**
     * Specify data which should be serialized to JSON. This method returns data that can be serialized by json_encode()
     * natively.
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->getValue(),
            'label' => $this->label(),
        ];
    }

    public function label(): ?string
    {
        return self::getLabel($this->getValue());
    }

    public function toHtml()
    {
        return new HtmlString($this->value);
    }

    public function get($model, string $key, $value, array $attributes): self
    {
        return $this->asEnum($value);
    }

    public function set($model, string $key, $value, array $attributes): self
    {
        return $this->asEnum($value);
    }

    protected function asEnum($value): BaseEnum
    {
        if ($value instanceof BaseEnum) {
            return $value;
        }

        return (new static())->make($value);
    }
}
