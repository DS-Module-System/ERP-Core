<?php

namespace App\Service\Core;

use Symfony\Contracts\Translation\TranslatorInterface;

final class DomainTranslationService
{
    public function __construct(
        private TranslatorInterface $translator,
        private string $projectDir
    ) {
    }

    /**
     * Translate a key using domain-based format: "domain.key"
     * 
     * @param string $key The key in format "domain.key" (e.g., "invoice.title")
     * @param array $parameters Parameters for translation
     * @param string|null $locale Locale to use, defaults to current locale
     * @return string Translated text
     */
    public function translate(string $key, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        if (!str_contains($key, '.')) {
            // If no domain specified, fall back to regular translation
            return $this->translator->trans($key, $parameters, null, $locale);
        }

        [$domain, $translationKey] = explode('.', $key, 2);

        if($domain === 'core') {
            $domain = null;
        }
        
        // Use the domain as the translation domain
        return $this->translator->trans($translationKey, $parameters, $domain, $locale);
    }

    /**
     * Check if a domain translation file exists
     */
    public function domainExists(string $domain, ?string $locale = null): bool
    {
        $locale = $locale ?? $this->translator->getLocale();
        $translationFile = $this->projectDir . '/translations/' . $domain . '.' . $locale . '.yaml';
        
        return file_exists($translationFile);
    }

    /**
     * Get all available domains
     */
    public function getAvailableDomains(?string $locale = null): array
    {
        $locale = $locale ?? $this->translator->getLocale();
        $translationsDir = $this->projectDir . '/translations/';
        $domains = [];
        
        if (!is_dir($translationsDir)) {
            return $domains;
        }
        
        $files = glob($translationsDir . '*.' . $locale . '.yaml');
        
        foreach ($files as $file) {
            $filename = basename($file);
            $domain = str_replace('.' . $locale . '.yaml', '', $filename);
            if (!in_array($domain, $domains)) {
                $domains[] = $domain;
            }
        }
        
        return $domains;
    }
} 