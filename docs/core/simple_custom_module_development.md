# Simple Custom Module Development Guide

This guide demonstrates how to create a new module based on the Bank Account module structure using the `CoreBaseController`. The Bank Account module serves as a perfect template for creating simple CRUD modules with minimal code.

## Overview

The `CoreBaseController` provides a complete CRUD functionality out of the box, including:
- List/Index page with search and pagination
- Create new records
- Edit existing records
- Delete records
- Modal form support
- Flash messages
- Translation support

## Module Structure

A complete module consists of the following components:

```
src/
├── Controller/
│   └── YourModule/
│       └── YourModuleController.php
├── Entity/
│   └── YourModule/
│       └── YourModule.php
├── Form/
│   └── YourModule/
│       ├── YourModuleForm.php
│       └── YourModuleSearchForm.php
├── Repository/
│   └── YourModule/
│       └── YourModuleRepository.php
templates/
└── your_module/
    ├── index.html.twig
    └── form.html.twig
translations/
└── your_module.en.yaml
```

## Step-by-Step Implementation

### 1. Create the Entity

Create your entity class in `src/Entity/YourModule/YourModule.php`:

```php
<?php

namespace App\Entity\YourModule;

use App\Repository\YourModule\YourModuleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: YourModuleRepository::class)]
class YourModule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
```

### 2. Create the Repository

Create your repository in `src/Repository/YourModule/YourModuleRepository.php`:

```php
<?php

namespace App\Repository\YourModule;

use App\Entity\YourModule\YourModule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class YourModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, YourModule::class);
    }

    public function save(YourModule $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(YourModule $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getPaginatedQuery(array $searchFormData = [], array $languages = []) 
    {
        $qb = $this->createQueryBuilder('t');
        
        // Add search conditions
        if (!empty($searchFormData['name'])) {
            $qb->andWhere('t.name LIKE :name')
               ->setParameter('name', '%' . $searchFormData['name'] . '%');
        }

        // Add language support if needed
        if (!empty($languages)) {
            foreach ($languages as $language) {
                $qb->leftJoin('t.lngs', $language, "WITH", "{$language}.lng='{$language}'")
                    ->addSelect($language);
            }
        }

        return $qb->getQuery();
    }
}
```

### 3. Create the Form

Create your main form in `src/Form/YourModule/YourModuleForm.php`:

```php
<?php

namespace App\Form\YourModule;

use App\Entity\YourModule\YourModule;
use App\Form\Core\DefaultForm\EditForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class YourModuleForm extends EditForm
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name', TextType::class, [
                'label' => 'name',
                'constraints' => [
                    new Length(['max' => 255]),
                    new NotBlank(),
                ],
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'description',
                'constraints' => [
                    new Length(['max' => 1000]),
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => YourModule::class,
            'translation_domain' => 'your_module',
        ]);
    }
}
```

### 4. Create the Search Form

Create your search form in `src/Form/YourModule/YourModuleSearchForm.php`:

```php
<?php

namespace App\Form\YourModule;

use App\Form\Core\DefaultForm\SearchForm;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class YourModuleSearchForm extends SearchForm 
{
    public function __construct(
        private RequestStack $requestStack,
        private UrlGeneratorInterface $router
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        parent::buildForm($builder, $options);
        
        $builder
            ->add('name', TextType::class, [
                'label' => 'name',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $resolver->setDefault('action', $this->router->generate(
                $request->get('_route'),
                array_merge($request->get('_route_params'), ['page' => 1])
            ));
        }
        $resolver->setDefault('translation_domain', 'your_module');
    }
}
```

### 5. Create the Controller

Create your controller in `src/Controller/YourModule/YourModuleController.php`:

```php
<?php

namespace App\Controller\YourModule;

use App\Controller\Core\CoreBaseController;
use App\Entity\YourModule\YourModule;
use App\Form\YourModule\YourModuleForm;
use App\Form\YourModule\YourModuleSearchForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/your-modules', name: 'your_module_')]
class YourModuleController extends CoreBaseController
{
    protected string $entityClass = YourModule::class;
    protected string $formClass = YourModuleForm::class;
    protected string $searchFormClass = YourModuleSearchForm::class;
    protected string $moduleTemplateName = 'your_module';

    #[Route(path: '', name: 'list')]
    public function list(Request $request): Response
    {
        $page = $request->get('page', 1);
        return $this->baseList($request, $page);
    }

    #[Route(path: '/create', name: 'create')]
    public function create(Request $request): Response
    {
        return $this->baseCreate($request);
    }

    #[Route(path: '/{id}/edit', name: 'edit')]
    public function edit($id, Request $request): Response
    {
        return $this->baseEdit($request, $id);
    }

    #[Route(path: '/deletes', name: 'deletes')]
    public function deletes(Request $request): Response
    {
        return $this->baseDeletes($request);
    }
}
```

### 6. Create Templates

Create the index template in `templates/your_module/index.html.twig`:

```twig
{% extends 'base.html.twig' %}

{% import 'core/default_templates/form_search.html.twig' as form_search %}
{% import 'core/default_templates/data_table_macro.html.twig' as data_table %}

{% block body %}
    {{ form_search.form_search(form, ["name"]) }}

    {{ data_table.table(rows, {
        "newLink": data_table.new_btn('your_module_create'),
        "cols" : {
            "tableAction": {
                "label": "core.module.actionTitle",
                "edit": path('your_module_edit', {"id": -1}),
            },
            "name": {
                "label": "your_module.name",
            },
            "description": {
                "label": "your_module.description",
            },
            "createdAt": {
                "label": "your_module.createdAt",
                "format": "date",
            },
        }
     }) }}
{% endblock %}
```

Create the form template in `templates/your_module/form.html.twig`:

```twig
{% extends is_modal_request() ? 'core/modal-form-base.html.twig' : 'base.html.twig' %}

{% import 'core/default_templates/form_table_macro.html.twig' as form_table %}

{% set is_edit = is_edit_route(app.request.get('_route')) %}

{% set form_btns = form_table.btns('your_module_list', form, is_edit) %}

{% set fields = form_table.generateFormRows(form) %}

{% set headerTitle = '' %}
{% if is_edit == false %}
    {% set headerTitle = 'your_module.createTitle'|trans %}
{% else %}
    {% set headerTitle = 'your_module.editTitle'|trans %}
{% endif %}

{% set formOptions = {
    'template': is_modal_request() ? 'core/default_templates/_form_modal.html.twig' : 'core/default_templates/_form.html.twig',
} %}

{% block body %}
    {% embed formOptions.template with formOptions %}
        {% block head_title %}
            {{ headerTitle }}
        {% endblock %}

        {% block form_fields %}
            {{ fields }}
        {% endblock %}

        {% block form_btns %}
            {{ form_btns }}
        {% endblock %}
    {% endembed %}
{% endblock %}
```

### 7. Create Translations

Create translation file in `translations/your_module.en.yaml`:

```yaml
# Your Module translations
leftMenu.yourModules: 'Your Modules'

name: 'Name'
description: 'Description'
createdAt: 'Created At'

createTitle: 'Create New Item'
editTitle: 'Edit Item'
```

### 8. Create Database Migration

Generate and run the database migration:

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

## CoreBaseController Features

The `CoreBaseController` provides the following methods:

### baseList($request, $page)
- Handles listing with pagination
- Supports search forms
- Supports sorting
- Supports custom callbacks

### baseCreate($request)
- Handles entity creation
- Form validation
- Flash messages
- Modal support
- Custom callbacks support

### baseEdit($request, $id)
- Handles entity editing
- Form validation
- Flash messages
- Modal support
- Custom callbacks support

### baseDeletes($request)
- Handles bulk deletions
- Flash messages
- Error handling

## Customization Options

### Override Default Behavior

You can override any method in your controller:

```php
class YourModuleController extends CoreBaseController
{
    // ... existing code ...

    protected function setDefaultEntityData($row, $additionalData, $request)
    {
        // Set default values for new entities
        $row->setCreatedAt(new \DateTimeImmutable());
        return $row;
    }

    protected function preCreatePersist($row)
    {
        // Custom logic before persisting
        return $row;
    }

    protected function postCreateFlush($row)
    {
        // Custom logic after flushing
    }

    protected function setAdditionalListTemplateData($rows)
    {
        // Add additional data to template
        return [
            'totalCount' => count($rows),
            'customData' => 'value',
        ];
    }
}
```

### Custom Callbacks

You can use callbacks for more complex scenarios:

```php
class YourModuleController extends CoreBaseController
{
    public function __construct(
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        PaginatorInterface $paginator,
        ContextService $contextService,
    ) {
        parent::__construct($em, $translator, $paginator, $contextService);
        
        $this->callbacks = [
            'setListQuery' => function($searchFormData) {
                // Custom query logic
                return $this->em->getRepository($this->entityClass)
                    ->createQueryBuilder('t')
                    ->where('t.active = :active')
                    ->setParameter('active', true)
                    ->getQuery();
            },
            'preCreatePersist' => function($row) {
                // Custom pre-persist logic
                return $row;
            },
        ];
    }
}
```

## Best Practices

1. **Naming Convention**: Use consistent naming for your module (e.g., `your_module`, `YourModule`)
2. **Translation Keys**: Always use translation keys for labels and messages
3. **Validation**: Add proper validation constraints to your forms
4. **Repository Methods**: Implement custom repository methods for complex queries
5. **Templates**: Use the existing template macros for consistency
6. **Security**: Add proper security annotations if needed
7. **Testing**: Create tests for your custom logic

## Example: Product Module

Here's a complete example of a Product module:

### Entity
```php
// src/Entity/Product/Product.php
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    // ... getters and setters
}
```

### Controller
```php
// src/Controller/Product/ProductController.php
#[Route(path: '/products', name: 'product_')]
class ProductController extends CoreBaseController
{
    protected string $entityClass = Product::class;
    protected string $formClass = ProductForm::class;
    protected string $searchFormClass = ProductSearchForm::class;
    protected string $moduleTemplateName = 'product';

    // ... standard CRUD methods
}
```

This approach allows you to create fully functional CRUD modules with minimal code while maintaining consistency across your application. 