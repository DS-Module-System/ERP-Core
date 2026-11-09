<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-02
 * Time: 14:23
 */

namespace App\Form\User;

use App\Entity\User\BaseUser;
use App\Form\Core\DefaultForm\EditForm;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class MeForm extends EditForm
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('email', EmailType::class, [
                'label' => "email",
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3]),
                    new Email(['mode'=>'strict']),
                ],
                'empty_data' => '',
            ])
            ->add('isEnableTwoFa', CheckboxType::class, [
                'label' => "enableTwoFa",
                'label_attr' => [
                    'data-on-label' => $this->translator->trans('layout.yesTitle'),
                    'data-off-label' => $this->translator->trans('layout.noTitle'),
                ],
                'attr' => [
                    'data-switch' => 'success',
                ]
            ])
            ->add('name', TextType::class, [
                'label' => "name",
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3]),
                ],
                'empty_data' => '',
            ])
            ->add('family', TextType::class, [
                'label' => "family",
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3]),
                ],
                'empty_data' => '',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BaseUser::class,
            'translation_domain' => 'user',
        ]);
    }
}