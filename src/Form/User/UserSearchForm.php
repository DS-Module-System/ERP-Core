<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-22
 * Time: 10:07
 */

namespace App\Form\User;


use App\Entity\User\UserGroup;
use App\Form\Core\DefaultForm\SearchForm;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserSearchForm extends SearchForm
{

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $router
    ) {

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('email', TextType::class, [
            'label' => "email"
        ]);
        $builder->add('user_group', EntityType::class, [
            'label' => "userGroup",
            'class' => UserGroup::class,
            'choice_label' => 'name',
            'placeholder' => "chooseGroup",
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $resolver->setDefault('action', $this->router->generate($request->get('_route'),
                array_merge($request->get('_route_params'), ['page' => 1])));
        }
        $resolver->setDefaults([
            'translation_domain' => 'user',
        ]);

    }

}