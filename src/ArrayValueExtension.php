<?php

namespace App;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ArrayValueExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('has_value', [$this, 'hasValue'])
        ];
    }

    public function hasValue(array $array, string $key): bool
    {
        foreach($array as $value) {
            if($value == $key) {
                return true;
            } else {
                continue;
            }
        }
        return false;
    }

}
