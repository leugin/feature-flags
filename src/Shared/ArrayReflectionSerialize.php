<?php
declare(strict_types=1);

namespace Leugin\FeatureFlags\Shared;

use Illuminate\Support\Str;

trait ArrayReflectionSerialize
{


    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->toArray());
    }

    public function offsetGet($offset)
    {
        return $this->toArray()[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $k = "set".ucfirst($offset);
        if (method_exists($this, $k)) {
            $this->{$k}($value);
        }
    }

    public function offsetUnset($offset)
    {
        $k = "set".ucfirst($offset);
        if (method_exists($this, $k)) {
            $this->{$k}(null);
        }
    }

    public function toArray(): array
    {
        $rf = (new \ReflectionClass($this))->getProperties();
        $props = [];
        foreach ($rf as $value) {
            $k = strtolower(Str::snake($value->getName()));
            $getMethod = "get".ucfirst($value->getName());
            if (method_exists($this, $getMethod)) {
                $v = $this->{$value->getName()};
                if (is_object($v) && method_exists($v, 'toArray')) {
                    $v = $v->toArray();
                }
                $props[$k] = $v;

            }

        }
        return $props;
    }

    public function empty(): bool
    {
//        $all = $this->toArray();
//        return collect($all)->filter(function ($value){
//                return is_null($value);
//            })->count() == 0;
    }

}