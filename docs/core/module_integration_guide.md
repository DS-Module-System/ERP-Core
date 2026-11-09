# Module Integration Guide

This guide demonstrates how to integrate modules with each other using the relationship between Invoice and Bank Account modules as an example. The guide covers different types of relationships and integration patterns.

## Overview

Module integration allows different modules to work together by establishing relationships between their entities. This creates a more cohesive system where data can be shared and referenced across modules.

## Types of Module Relationships

### 1. Many-to-One Relationship (Most Common)
- One entity from Module A can be associated with multiple entities from Module B
- Example: One Bank Account can be used by multiple Invoices

### 2. One-to-Many Relationship
- One entity from Module A can have multiple related entities from Module B
- Example: One Invoice can have multiple Invoice Items

### 3. Many-to-Many Relationship
- Multiple entities from Module A can be associated with multiple entities from Module B
- Example: Products and Categories

## Step-by-Step Integration Process

### Step 1: Define Entity Relationships

#### 1.1 Update the Target Entity (Module A)

Add the relationship property to the entity that will reference another module:

```php
// src/Entity/Invoice/Invoice.php
use App\Entity\BankAccount\BankAccount;

class Invoice
{
    // ... existing properties ...

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?BankAccount $bankAccount = null;

    // ... existing methods ...

    public function getBankAccount(): ?BankAccount
    {
        return $this->bankAccount;
    }

    public function setBankAccount(?BankAccount $bankAccount): self
    {
        $this->bankAccount = $bankAccount;
        return $this;
    }
}
```

#### 1.2 Update the Source Entity (Module B) - Optional

If you want bidirectional relationship, add the inverse side:

```php
// src/Entity/BankAccount/BankAccount.php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class BankAccount
{
    // ... existing properties ...

    #[ORM\OneToMany(mappedBy: 'bankAccount', targetEntity: Invoice::class)]
    private Collection $invoices;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
    }

    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
            $invoice->setBankAccount($this);
        }
        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            if ($invoice->getBankAccount() === $this) {
                $invoice->setBankAccount(null);
            }
        }
        return $this;
    }
}
```

### Step 2: Update Forms

#### 2.1 Add Entity Field to Form

```php
// src/Form/Invoice/InvoiceForm.php
use App\Entity\BankAccount\BankAccount;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;

class InvoiceForm extends EditForm
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            // ... existing fields ...
            ->add('bankAccount', EntityType::class, [
                'class' => BankAccount::class,
                'label' => 'bankAccount',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'choice_label' => function (BankAccount $entity) {
                    return $entity->getName();
                },
                'placeholder' => 'Избери',
                'attr' => [
                    'class' => 'select2',
                ]
            ])
        ;
    }
}
```

#### 2.2 Update Search Form (Optional)

```php
// src/Form/Invoice/InvoiceSearchForm.php
use App\Entity\BankAccount\BankAccount;

class InvoiceSearchForm extends SearchForm
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        $builder
            // ... existing fields ...
            ->add('bankAccount', EntityType::class, [
                'class' => BankAccount::class,
                'label' => 'bankAccount',
                'required' => false,
                'choice_label' => function (BankAccount $entity) {
                    return $entity->getName();
                },
                'placeholder' => 'Всички банкови сметки',
                'attr' => [
                    'class' => 'select2',
                ]
            ])
        ;
    }
}
```

### Step 3: Update Repository Queries

#### 3.1 Add Search Conditions

```php
// src/Repository/Invoice/InvoiceRepository.php
public function getPaginatedQuery(array $searchFormData = []): Query
{
    $qb = $this->createQueryBuilder('t');

    // ... existing conditions ...

    if (!empty($searchFormData['bankAccount'])) {
        $qb->andWhere("t.bankAccount = :bankAccount")
           ->setParameter('bankAccount', $searchFormData['bankAccount']);
    }

    return $qb->getQuery();
}
```

### Step 4: Update Templates

#### 4.1 Add Field to Form Template

```twig
{# templates/invoice/form_fields.html.twig #}
{{ form_table.tr(form.bankAccount) }}
```

#### 4.2 Add Column to List Template

```twig
{# templates/invoice/index.html.twig #}
{{ data_table.table(rows, {
    "newLink": data_table.new_btn('invoice_create'),
    "cols" : {
        "tableAction": {
            "label": "core.module.actionTitle",
            "edit": path('invoice_edit', {"id": -1}),
        },
        "number": {
            "label": "invoice.number",
        },
        "client.name": {
            "label": "invoice.client",
        },
        "bankAccount.name": {
            "label": "invoice.bankAccount",
        },
        "totalPrice": {
            "label": "invoice.totalPrice",
        },
    }
}) }}
```

#### 4.3 Display Related Data in Templates

```twig
{# templates/invoice/template.html.twig #}
<div class="bankdetailsData">
    <table>
        <tr>
            <td>{{ 'invoice.bank'|dtrans }}</td>
            <td>{{ invoice.bankAccount ? invoice.bankAccount.bankName : '' }}</td>
        </tr>
        <tr>
            <td>{{ 'client.address'|dtrans }}</td>
            <td>{{ invoice.bankAccount and invoice.bankAccount.address ? invoice.bankAccount.address : '' }}</td>
        </tr>
        <tr>
            <td>IBAN:</td>
            <td>{{ invoice.bankAccount ? invoice.bankAccount.iban : '' }}</td>
        </tr>
        <tr>
            <td>SWIFT:</td>
            <td>{{ invoice.bankAccount ? invoice.bankAccount.swift : '' }}</td>
        </tr>
    </table>
</div>
```

### Step 5: Update Translations

#### 5.1 Add Translation Keys

```yaml
# translations/invoice.en.yaml
bankAccount: 'Bank Account'
bank: 'Bank'
bankDetails: 'Bank Details'
```

```yaml
# translations/bank_account.en.yaml
invoices: 'Invoices'
```

### Step 6: Generate and Run Migrations

```bash
# Generate migration for the new relationship
php bin/console make:migration

# Apply the migration
php bin/console doctrine:migrations:migrate
```

## Advanced Integration Patterns

### 1. Conditional Relationships

```php
// Only show bank account field for certain payment methods
public function buildForm(FormBuilderInterface $builder, array $options)
{
    parent::buildForm($builder, $options);

    $builder
        ->add('paymentMethod', EnumType::class, [
            'class' => InvoicePaymentMethod::class,
            'label' => 'paymentMethod',
        ])
        ->add('bankAccount', EntityType::class, [
            'class' => BankAccount::class,
            'label' => 'bankAccount',
            'required' => false,
            'choice_label' => function (BankAccount $entity) {
                return $entity->getName();
            },
        ])
    ;

    // Add form event listener for conditional logic
    $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();

        if (isset($data['paymentMethod']) && $data['paymentMethod'] === 'Bank') {
            $form->get('bankAccount')->setRequired(true);
        }
    });
}
```

### 2. Dynamic Loading with AJAX

```php
// src/Controller/Invoice/InvoiceController.php
#[Route(path: '/api/bank-accounts', name: 'api_bank_accounts')]
public function getBankAccounts(Request $request): JsonResponse
{
    $bankAccounts = $this->em->getRepository(BankAccount::class)->findAll();
    
    $data = [];
    foreach ($bankAccounts as $bankAccount) {
        $data[] = [
            'id' => $bankAccount->getId(),
            'name' => $bankAccount->getName(),
            'iban' => $bankAccount->getIban(),
        ];
    }
    
    return new JsonResponse($data);
}
```

### 3. Service Layer Integration

```php
// src/Service/Invoice/InvoiceService.php
class InvoiceService
{
    public function __construct(
        private EntityManagerInterface $em,
        private BankAccountService $bankAccountService
    ) {}

    public function createInvoiceWithBankAccount(array $data): Invoice
    {
        $invoice = new Invoice();
        
        // Set basic invoice data
        $invoice->setNumber($data['number']);
        $invoice->setClient($data['client']);
        
        // Set bank account if provided
        if (!empty($data['bankAccountId'])) {
            $bankAccount = $this->bankAccountService->findById($data['bankAccountId']);
            $invoice->setBankAccount($bankAccount);
        }
        
        $this->em->persist($invoice);
        $this->em->flush();
        
        return $invoice;
    }
}
```

## Integration Best Practices

### 1. Use Proper Annotations
- Always use the correct Doctrine annotations
- Include proper cascade options
- Set nullable properties appropriately

### 2. Handle Null Values
```php
// Always check for null values in templates
{{ invoice.bankAccount ? invoice.bankAccount.name : 'N/A' }}

// Use null coalescing operator in PHP
$bankName = $invoice->getBankAccount()?->getName() ?? 'N/A';
```

### 3. Optimize Queries
```php
// Use JOIN to avoid N+1 queries
public function getInvoicesWithBankAccounts(): array
{
    return $this->createQueryBuilder('i')
        ->leftJoin('i.bankAccount', 'b')
        ->addSelect('b')
        ->getQuery()
        ->getResult();
}
```

### 4. Validation
```php
// Add validation constraints
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Expression(
    "this.getPaymentMethod() != 'Bank' or this.getBankAccount() != null",
    message: 'Bank account is required for bank payments'
)]
class Invoice
{
    // ... properties
}
```

### 5. Error Handling
```php
// Handle missing related entities gracefully
public function getBankAccountInfo(): array
{
    $bankAccount = $this->getBankAccount();
    
    if (!$bankAccount) {
        return [
            'name' => 'N/A',
            'iban' => 'N/A',
            'swift' => 'N/A',
        ];
    }
    
    return [
        'name' => $bankAccount->getName(),
        'iban' => $bankAccount->getIban(),
        'swift' => $bankAccount->getSwift(),
    ];
}
```

## Common Integration Scenarios

### 1. User Module Integration
```php
// Many entities can be created by users
#[ORM\ManyToOne]
private ?BaseUser $createdBy = null;

#[ORM\ManyToOne]
private ?BaseUser $updatedBy = null;
```

### 2. File Module Integration
```php
// Many-to-many relationship with files
#[ORM\ManyToMany(targetEntity: File::class)]
#[ORM\JoinTable(name: 'invoice_files')]
private Collection $files;
```

### 3. Settings Module Integration
```php
// Global settings that affect multiple modules
#[ORM\ManyToOne]
private ?Setting $companyInfo = null;
```

## Troubleshooting

### Common Issues

1. **Foreign Key Constraint Violations**
   - Ensure related entities exist before creating relationships
   - Use proper cascade options

2. **Circular Reference Errors**
   - Avoid bidirectional relationships when not needed
   - Use lazy loading for complex relationships

3. **Performance Issues**
   - Use JOIN queries to avoid N+1 problems
   - Implement proper indexing on foreign keys

4. **Form Validation Errors**
   - Ensure all required fields are properly validated
   - Check translation keys exist

### Debug Commands

```bash
# Check entity relationships
php bin/console doctrine:schema:validate

# Generate SQL for relationships
php bin/console doctrine:schema:update --dump-sql

# Clear cache after changes
php bin/console cache:clear
```

## Example: Complete Product-Category Integration

Here's a complete example of integrating a Product module with a Category module:

### Product Entity
```php
// src/Entity/Product/Product.php
#[ORM\Entity]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Category $category = null;

    // ... getters and setters
}
```

### Category Entity
```php
// src/Entity/Category/Category.php
#[ORM\Entity]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Product::class)]
    private Collection $products;

    // ... getters and setters
}
```

### Product Form
```php
// src/Form/Product/ProductForm.php
->add('category', EntityType::class, [
    'class' => Category::class,
    'label' => 'category',
    'choice_label' => 'name',
    'placeholder' => 'Select Category',
])
```

This integration pattern can be applied to any modules that need to work together in your ERP system. 