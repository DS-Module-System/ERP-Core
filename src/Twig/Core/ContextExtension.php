<?php



namespace App\Twig\Core;

use App\Service\Core\ContextService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ContextExtension extends AbstractExtension
{
    public function __construct(private ContextService $contextService)
    {
    }

    public function isModalRequest(): bool
    {
        return $this->contextService->isModalRequest();
    }

    public function isJavascriptRequest(): bool
    {
        return $this->contextService->isJavascriptRequest();
    }

    public function getFunctions(): array {
        return [
            new TwigFunction('is_modal_request', $this->isModalRequest(...)),
            new TwigFunction('is_javascript_request', $this->isJavascriptRequest(...)),
        ];
    }
}
