<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-22
 * Time: 17:36
 */

namespace App\Form\Core\DefaultForm;

use App\DataTransformer\Core\FileToIntegerTransformer;
use App\Entity\Core\FileAssociation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class FileType extends AbstractType
{
    public function __construct(private readonly FileToIntegerTransformer $fileToIntegerTransformer)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->fileToIntegerTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'invalid_message' => 'The selected issue does not exist',
        ]);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}