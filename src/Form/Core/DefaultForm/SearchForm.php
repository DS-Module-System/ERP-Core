<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-22
 * Time: 17:36
 */

namespace App\Form\Core\DefaultForm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod(\Symfony\Component\HttpFoundation\Request::METHOD_GET)
            ->add('search', SubmitType::class, [
                'label' => 'layout.searchBtnTitle',
                'attr' => [
                    'class' => 'btn btn-primary'
                ],
            ]);
    }
}