<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-22
 * Time: 17:36
 */

namespace App\Form\Core\DefaultForm;

use App\Entity\Core\FileAssociation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileAssociationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [])
            ->add('position', TextType::class, [])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FileAssociation::class,
            'invalid_message' => 'The selected issue does not exist',
        ]);
    }


}