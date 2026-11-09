<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2020-10-28
 * Time: 15:59
 */

namespace App\Form\Core\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IconButtonExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [ButtonType::class];
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['icon_before'] = $options['icon_before'] ?? '';
        $view->vars['icon_after'] = $options['icon_after'] ?? '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'icon_before' => null,
            'icon_after' => null
        ]);
    }

}
