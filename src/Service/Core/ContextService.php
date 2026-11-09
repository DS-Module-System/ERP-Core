<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Core;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ContextService extends AbstractExtension
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public function isModalRequest(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return false;
        }

        if ($request->isXmlHttpRequest()) {
            return true;
        }

        if (!$request->headers->has('X-Requested-With')) {
            return false;
        }

        return str_contains(strtolower($request->headers->get('X-Requested-With')), strtolower('DSModalRequest'));
    }

    public function isJavascriptRequest(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return false;
        }

        if ($request->isXmlHttpRequest()) {
            return true;
        }

        if (!$request->headers->has('X-Requested-With')) {
            return false;
        }

        return str_contains(strtolower($request->headers->get('X-Requested-With')), strtolower('DSJSRequest'));
    }

    public function getFunctions(): array {
        return [
            new TwigFunction('is_modal_request', $this->isModalRequest(...)),
            new TwigFunction('is_javascript_request', $this->isJavascriptRequest(...)),
        ];
    }
}
