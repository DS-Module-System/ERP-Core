<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-02
 * Time: 14:23
 */

namespace App\Form\User;

use App\Entity\User\BaseUser;
use App\Entity\User\UserGroup;
use App\Form\Core\DefaultForm\EditForm;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserForm extends EditForm
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
            ->add('password', PasswordType::class, [
                'label' => "password",
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                ],
                'empty_data' => '',
            ])
            ->add('userGroups', EntityType::class, [
                'class' => UserGroup::class,
                'label' => "userGroup",
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => true,
                'label_attr' => [
                    'class'=>'custom-checkboxes',
                ],
                'constraints' => [
                    new Count(['min' => 1])
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => "isActive",
                'label_attr' => [
                    'data-on-label' => $this->translator->trans('layout.yesTitle'),
                    'data-off-label' => $this->translator->trans('layout.noTitle'),
                ],
                'attr' => [
                    'data-switch' => 'success',
                ]
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
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                ],
            ])
            ->add('family', TextType::class, [
                'label' => "family",
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BaseUser::class,
            'translation_domain' => 'user',
        ]);
    }
}