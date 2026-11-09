<?php

namespace App\Twig\Core;

use App\Service\Core\DomainTranslationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

final class DomainTranslationExtension extends AbstractExtension
{
    public function __construct(private DomainTranslationService $domainTranslationService)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('dtrans', [$this, 'translateDomain']),
            new TwigFunction('domain_trans', [$this, 'translateDomain']),
            new TwigFunction('domain_exists', [$this, 'domainExists']),
            new TwigFunction('available_domains', [$this, 'getAvailableDomains']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('dtrans', [$this, 'translateDomain']),
            new TwigFilter('domain_trans', [$this, 'translateDomain']),
        ];
    }

    /**
     * Translate a key using domain-based format: "domain.key"
     * 
     * @param string $key The key in format "domain.key" (e.g., "invoice.title")
     * @param array $parameters Parameters for translation
     * @param string|null $locale Locale to use, defaults to current locale
     * @return string Translated text
     */
    public function translateDomain(string $key, ?array $parameters = null, ?string $domain = null,  ?string $locale = null): string
    {
        if($parameters === null) {
            $parameters = [];
        }
        return $this->domainTranslationService->translate($key, $parameters, $domain, $locale);
    }

    /**
     * Check if a domain translation file exists
     */
    public function domainExists(string $domain, ?string $locale = null): bool
    {
        return $this->domainTranslationService->domainExists($domain, $locale);
    }

    /**
     * Get all available domains
     */
    public function getAvailableDomains(?string $locale = null): array
    {
        return $this->domainTranslationService->getAvailableDomains($locale);
    }
} 