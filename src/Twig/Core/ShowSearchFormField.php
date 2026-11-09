<?php

namespace App\Twig\Core;

use App\Service\Core\DomainTranslationService;
use Symfony\Component\Form\FormView;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ShowSearchFormField extends AbstractExtension {


    public function __construct(private readonly DomainTranslationService $domainTranslationService)
    {

    }

    public function showSearchFormField(FormView $formField): string
    {
        $str = [];
        $vars = $formField->vars;
        $domain = $vars['translation_domain'] ?? 'core';
    //    dump($vars);
        $value = null;
        if((is_string($vars['value']) && $vars['value'] != '') || (is_array($vars['value']) && !empty($vars['value']))) {
            if(isset($vars['choices'])) {
                $tmpStr = [];
                foreach ($vars['choices'] as $choice) {
                    if(is_array($vars['value'])) {
                        if (in_array($choice->value, $vars['value'])) {
                            $tmpStr[] = $this->domainTranslationService->translate("{$domain}.{$choice->label}");
                        }
                    } else {
                        if ($choice->value == $vars['value']) {
                            $tmpStr[] = $this->domainTranslationService->translate("{$domain}.{$choice->label}");
                        }
                    }
                }
                $value = implode(', ', $tmpStr);
            } elseif (preg_match('/^([0-9]{4})-(1[0-2]|0[1-9])-(3[01]|0[1-9]|[12][0-9])[ T](2[0-3]|[01][0-9]):([0-5][0-9]):([0-5][0-9])$/', $vars['value'], $matches)) {
                if (($date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', $matches[0])) !== false) {
                    $value = $date->format('d.m.Y H:i');
                }
            } elseif (preg_match('/^(([12]\d{3})-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))$/', $vars['value'], $matches)) {
                if (($date = \DateTimeImmutable::createFromFormat('Y-m-d', $matches[0])) !== false) {
                    $value = $date->format('d.m.Y');
                }
            } else {
                if(is_numeric($vars['value']) || is_string($vars['value'])) {
                    $value = $vars['value'];
                }
            }

            $label = $this->domainTranslationService->translate("{$domain}.{$vars['label']}");
            if(empty($label)) {
                $label = ucfirst(strtolower(trim(preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], (string) $vars['name']))));
            }

            $str[] = "<strong>{$label}: </strong> {$value}";

            $str[] = '; ';
        }

        return implode('', $str);
    }


    public function getFunctions(): array {
        return [
            new TwigFunction('show_search_form_field', $this->showSearchFormField(...)),
        ];
    }

}
