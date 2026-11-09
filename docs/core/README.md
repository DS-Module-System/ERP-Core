# ERP Core Module - Documentation

Welcome to the ERP Core Module documentation. This directory contains comprehensive guides for the core functionality of the ERP system, including the data-url-list module for dynamic content loading.

## Documentation Index

### 📖 [Data URL List Module](data-url-list-module.md)
Complete documentation for the data-url-list JavaScript functionality that enables dynamic content loading via AJAX requests. Includes API reference, usage examples, and best practices.

### ⚡ [Data URL List Quick Start](data-url-list-quick-start.md)
Quick start guide for implementing dynamic content loading with the data-url-list module. Includes basic syntax, examples, and troubleshooting.

### 🔧 [Module Integration Guide](module_integration_guide.md)
Guide for integrating new modules with the core system architecture.

## Core Module Overview

The ERP Core Module provides the foundation for all other modules in the system:

- **Base Classes**: CoreBaseController, CoreEntity, CoreRepository
- **Default Templates**: Reusable Twig templates for forms, tables, and layouts
- **JavaScript Utilities**: Dynamic content loading, modal forms, AJAX handling
- **Security**: Role-based access control and authentication
- **File Management**: File upload and association system
- **Translation**: Multi-language support infrastructure

## Key Features

### 1. Dynamic Content Loading (data-url-list)

The `data-url-list` module provides JavaScript functionality for loading content dynamically:

```html
<div class="api-v1" data-url-list="/api/endpoint"></div>
```

**Features:**
- AJAX content loading without page refresh
- Modal form support
- Automatic error handling
- Loading states with spinners
- Retry functionality

**JavaScript API:**
```javascript
// Load content in all api-v1 elements
loadApiV1Content();

// Load content in specific element
loadApiV1Content(document.querySelector('.my-element'));

// Open modal with content
loadContentInModal('/api/create', 'Create New');

// Refresh loaded content
refreshApiV1Content();
```

### 2. Base Controller System

All module controllers extend `CoreBaseController` for consistent CRUD operations:

```php
#[Route(path: '/module', name: 'module_')]
class ModuleController extends CoreBaseController
{
    protected string $entityClass = Entity::class;
    protected string $formClass = EntityForm::class;
    protected string $searchFormClass = EntitySearchForm::class;
    protected string $moduleTemplateName = 'module';
    
    // Standard CRUD methods are automatically available
}
```

### 3. Entity System

All entities implement the `CoreEntity` interface:

```php
#[ORM\Entity(repositoryClass: EntityRepository::class)]
class Entity implements CoreEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;
    
    #[ORM\ManyToOne(targetEntity: BaseUser::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?BaseUser $createdBy = null;
    
    // Constructor with default values
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }
}
```

### 4. Default Templates

Reusable Twig templates for consistent UI:

- **Form Templates**: `_form.html.twig`, `_form_modal.html.twig`
- **Table Templates**: `data_table_macro.html.twig`
- **Search Templates**: `form_search.html.twig`
- **Modal Templates**: `modal-form-base.html.twig`

### 5. File Management

Core file handling system with associations:

```php
#[ORM\Entity(repositoryClass: FileRepository::class)]
class File implements CoreEntity
{
    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $filename = null;
    
    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $originalFilename = null;
    
    #[ORM\Column(type: Types::STRING, length: 100)]
    private ?string $mimeType = null;
}
```

## Architecture

### Directory Structure

```
src/
├── Controller/Core/           # Core controllers and base classes
├── Entity/Core/              # Core entities and interfaces
├── Repository/Core/          # Base repository classes
├── Form/Core/                # Base form classes and extensions
├── Service/Core/             # Core services and utilities
├── Twig/Core/                # Twig extensions and functions
├── Security/Core/            # Security services and utilities
└── EventListener/Core/       # Event listeners and subscribers

templates/
├── core/                     # Core templates and macros
│   ├── default_templates/    # Reusable template components
│   ├── form/                 # Form-related templates
│   └── modal-form-base.html.twig
└── {module}/                 # Module-specific templates

assets/
└── app.js                    # Main JavaScript with data-url-list functionality
```

### Core Components

#### 1. CoreBaseController

Base controller providing standard CRUD operations:

```php
abstract class CoreBaseController extends AbstractController
{
    protected string $entityClass;
    protected string $formClass;
    protected string $searchFormClass;
    protected string $moduleTemplateName;
    
    // Standard CRUD methods
    protected function baseList(Request $request, int $page = 1): Response
    protected function baseCreate(Request $request): Response
    protected function baseEdit(Request $request, $id): Response
    protected function baseDeletes(Request $request): Response
}
```

#### 2. CoreEntity Interface

Interface that all entities must implement:

```php
interface CoreEntity
{
    public function getId(): ?int;
    public function getCreatedAt(): ?\DateTimeInterface;
    public function getCreatedBy(): ?BaseUser;
}
```

#### 3. CoreRepository

Base repository with common query methods:

```php
abstract class CoreRepository extends ServiceEntityRepository
{
    abstract public function getPaginatedQuery(array $searchFormData): Query;
    
    public function getPaginatedResults(array $searchFormData, int $page = 1): array
    {
        // Pagination logic
    }
}
```

## Integration Examples

### 1. Using data-url-list in Delivery Module

```twig
{# templates/delivery/form.html.twig #}
{% if is_edit %}
    <div class="card mt-4">
        <div class="card-header">
            {{ 'delivery.items'|dtrans }}
        </div>
        <div class="card-body">
            <div class="api-v1" data-url-list="/delivery-items/{{ delivery.id }}"></div>
        </div>
    </div>
{% endif %}
```

### 2. Controller for AJAX Content

```php
#[Route(path: '/delivery-items/{deliveryId}', name: 'delivery_item_')]
class DeliveryItemController extends CoreBaseController
{
    #[Route(path: '', name: 'list')]
    public function list(Request $request, int $deliveryId): Response
    {
        $delivery = $this->em->getRepository(Delivery::class)->find($deliveryId);
        if (!$delivery) {
            throw $this->createNotFoundException('Delivery not found');
        }

        $this->additionalData['delivery'] = $delivery;
        $this->appendSearchFormData['deliveryId'] = $delivery->getId();

        return $this->baseList($request, $request->get('page', 1));
    }
}
```

### 3. AJAX Template

```twig
{# templates/delivery_item/index.html.twig #}
{# No extends for AJAX requests #}

{% import 'core/default_templates/data_table_macro.html.twig' as data_table %}

{{ data_table.table(rows, {
    "newLink": data_table.new_btn('delivery_item_create', {"deliveryId": app.request.get('deliveryId')}),
    "cols" : {
        "tableAction": {
            "label": "core.module.actionTitle",
            "edit": path('delivery_item_edit', {"id": -1, "deliveryId": app.request.get('deliveryId')}),
        },
        "product.name": {
            "label": "delivery_item.product",
        },
        "quantity": {
            "label": "delivery_item.quantity",
            "type": "number",
        },
    }
}) }}
```

## Security

### Role-Based Access Control

```php
#[IsGranted('ROLE_MODULE_VIEW')]
public function list(Request $request): Response
{
    // Controller logic
}

#[IsGranted('ROLE_MODULE_CREATE')]
public function create(Request $request): Response
{
    // Create logic
}
```

### CSRF Protection

```php
// In forms
$form = $this->createForm(EntityForm::class, $entity, [
    'csrf_protection' => true
]);

// For AJAX endpoints
$form = $this->createForm(EntityForm::class, $entity, [
    'csrf_protection' => false
]);
```

## Best Practices

### 1. Entity Design

- Always implement `CoreEntity` interface
- Use proper Doctrine attributes
- Include `createdAt` and `createdBy` fields
- Use nullable types where appropriate

### 2. Controller Structure

- Extend `CoreBaseController` for new modules
- Use standard CRUD method names
- Implement proper security checks
- Handle AJAX requests appropriately

### 3. Template Organization

- Use default templates for consistency
- Don't use `extends` for AJAX templates
- Use translation keys for all text
- Follow Bootstrap 5.3 conventions

### 4. JavaScript Integration

- Use `data-url-list` for dynamic content
- Implement proper error handling
- Use loading states for better UX
- Follow the established API patterns

## Troubleshooting

### Common Issues

#### 1. data-url-list not working

**Check:**
- URL is correct and accessible
- Controller exists and returns proper response
- JavaScript is loaded
- No console errors

#### 2. Modal forms not opening

**Check:**
- Bootstrap JavaScript is included
- URL contains `/create` or `/edit`
- Controller supports modal requests

#### 3. Form submission errors

**Check:**
- CSRF token is included
- Form validation passes
- Controller handles POST requests properly

### Debug Mode

```javascript
// Enable debug logging
console.log('API v1 elements found:', document.querySelectorAll('.api-v1').length);
console.log('Loading content for:', element.getAttribute('data-url-list'));
```

## Performance Optimization

### 1. Database Queries

- Use Doctrine QueryBuilder for complex queries
- Implement proper indexing
- Avoid N+1 query problems

### 2. Caching

- Use Symfony Cache component
- Cache expensive operations
- Implement proper cache invalidation

### 3. JavaScript

- Use lazy loading for content
- Implement proper error handling
- Minimize DOM manipulation

## Conclusion

The Core Module provides a solid foundation for building ERP modules with:

- Consistent architecture and patterns
- Reusable components and templates
- Dynamic content loading capabilities
- Security and performance optimizations

By following the established patterns and using the provided base classes, you can quickly build robust and maintainable modules that integrate seamlessly with the ERP system. 