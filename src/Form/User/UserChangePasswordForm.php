<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-02
 * Time: 14:23
 */

namespace App\Form\User;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use App\Form\Core\DefaultForm\EditForm;

class UserChangePasswordForm extends EditForm
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'password',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min'=>6])
                ],
                'empty_data' => '',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'core.layout.saveBtnTitle',
                'attr' => [
                    'class' => 'btn btn-success',
                ],
                'icon_before' => 'mdi mdi-content-save me-1',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'user',
        ]);
    }

}