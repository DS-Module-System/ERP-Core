<?php

namespace App\Twig\Core;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UtilsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('instanceOf', [$this, 'isInstanceOf']),
            new TwigFunction('is_edit_route', [$this, 'isEditRoute']),
        ];
    }

    public function isInstanceOf(mixed $var, mixed $instance): bool
    {
        return $var instanceof $instance;
    }

    public function isEditRoute(string $route, string $editStr = ''): bool
    {
        if(empty($editStr)) {
            $editStr = '_edit';
        }

        return str_contains($route, $editStr);
    }
}