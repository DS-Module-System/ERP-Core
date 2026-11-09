# Data URL List Module - Quick Start Guide

## Бързо стартиране

### 1. Основен синтаксис

```html
<div class="api-v1" data-url-list="/api/endpoint"></div>
```

### 2. JavaScript функции

```javascript
// Зареждане на всички api-v1 елементи
loadApiV1Content();

// Зареждане на конкретен елемент
loadApiV1Content(document.querySelector('.my-element'));

// Отваряне на модален прозорец
loadContentInModal('/api/create', 'Създаване');

// Обновяване на съдържание
refreshApiV1Content();
```

### 3. Контролер endpoint

```php
#[Route(path: '/api/data', name: 'api_data_')]
class ApiDataController extends CoreBaseController
{
    #[Route(path: '', name: 'list')]
    public function list(Request $request): Response
    {
        return $this->render('api_data/index.html.twig', [
            'rows' => $rows,
            'form' => $form
        ]);
    }
}
```

### 4. Шаблон за AJAX

```twig
{# api_data/index.html.twig #}
{# Не използвай extends за AJAX заявки #}

{% import 'core/default_templates/data_table_macro.html.twig' as data_table %}

{{ data_table.table(rows, {
    "newLink": data_table.new_btn('api_data_create'),
    "cols" : {
        "tableAction": {
            "label": "core.module.actionTitle",
            "edit": path('api_data_edit', {"id": -1}),
        },
        "name": {
            "label": "api_data.name",
        },
    }
}) }}
```

## Примери за използване

### Delivery Items

```twig
{# В delivery/form.html.twig #}
<div class="api-v1" data-url-list="/delivery-items/{{ delivery.id }}"></div>
```

### Invoice Items

```twig
{# В invoice/form.html.twig #}
<div class="api-v1" data-url-list="/invoice-items/{{ invoice.id }}"></div>
```

### Client Orders

```twig
{# В client/form.html.twig #}
<div class="api-v1" data-url-list="/client-orders/{{ client.id }}"></div>
```

## Модални форми

### Автоматично отваряне

```javascript
// Автоматично се отваря модален прозорец за /create и /edit URL-и
loadContentInModal('/delivery-items/1/create', 'Създаване на артикул');
```

### Ръчно отваряне

```html
<button onclick="loadContentInModal('/api/create', 'Създаване')">
    Създай нов
</button>
```

## Error Handling

### Автоматична обработка

Модулът автоматично показва грешки и бутон "Опитай отново":

```html
<div class="alert alert-danger">
    <strong>Грешка при зареждане:</strong><br>
    ${error.message}
    <button class="btn btn-sm btn-outline-danger retry-button">
        <i class="fas fa-redo"></i>Опитай отново
    </button>
</div>
```

## Loading States

### Автоматичен spinner

```html
<div class="text-center py-4">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Зареждане...</span>
    </div>
    <div class="mt-2 text-muted">Зареждане на съдържание...</div>
</div>
```

## Best Practices

### 1. Правилни URL-и

```html
<!-- ✅ Правилно -->
<div class="api-v1" data-url-list="/delivery-items/{{ delivery.id }}"></div>

<!-- ❌ Грешно -->
<div class="api-v1" data-url-list="/delivery-items/1"></div>
```

### 2. Security

```php
// Проверка на permissions
#[IsGranted('ROLE_MODULE_VIEW')]
public function list(Request $request): Response
{
    // Проверка на достъп
    if (!$this->isGranted('ROLE_MODULE_VIEW')) {
        throw $this->createAccessDeniedException();
    }
    
    return $this->render('module/index.html.twig', [
        'rows' => $rows
    ]);
}
```

### 3. AJAX проверка

```php
public function list(Request $request): Response
{
    // Проверка дали заявката е AJAX
    if ($request->isXmlHttpRequest()) {
        return $this->render('module/index.html.twig', [
            'rows' => $rows
        ]);
    }
    
    // Обикновена заявка
    throw $this->createNotFoundException();
}
```

## Troubleshooting

### Често срещани проблеми

| Проблем | Причина | Решение |
|---------|---------|---------|
| Съдържанието не се зарежда | Неправилен URL | Проверете URL-а в `data-url-list` |
| Модалът не се отваря | Липсва Bootstrap JS | Включете Bootstrap JavaScript |
| Формата не се изпраща | Липсва CSRF token | Добавете CSRF защита |
| Грешки в конзолата | JavaScript грешки | Проверете конзолата за грешки |

### Debug режим

```javascript
// Проверка на наличните елементи
console.log('API v1 elements:', document.querySelectorAll('.api-v1').length);

// Проверка на URL-ите
document.querySelectorAll('.api-v1').forEach(el => {
    console.log('URL:', el.getAttribute('data-url-list'));
});
```

## Интеграция с нови модули

### 1. Създаване на контролер

```php
#[Route(path: '/my-module-items/{parentId}', name: 'my_module_item_')]
class MyModuleItemController extends CoreBaseController
{
    #[Route(path: '', name: 'list')]
    public function list(Request $request, int $parentId): Response
    {
        return $this->render('my_module_item/index.html.twig', [
            'rows' => $rows,
            'form' => $form
        ]);
    }
}
```

### 2. Създаване на шаблон

```twig
{# templates/my_module_item/index.html.twig #}
{% import 'core/default_templates/data_table_macro.html.twig' as data_table %}

{{ data_table.table(rows, {
    "newLink": data_table.new_btn('my_module_item_create', {"parentId": app.request.get('parentId')}),
    "cols" : {
        "tableAction": {
            "label": "core.module.actionTitle",
            "edit": path('my_module_item_edit', {"id": -1, "parentId": app.request.get('parentId')}),
        },
        "name": {
            "label": "my_module_item.name",
        },
    }
}) }}
```

### 3. Интеграция в основен шаблон

```twig
{# templates/my_module/form.html.twig #}
{% if is_edit %}
    <div class="card mt-4">
        <div class="card-header">
            {{ 'my_module.items'|dtrans }}
        </div>
        <div class="card-body">
            <div class="api-v1" data-url-list="/my-module-items/{{ entity.id }}"></div>
        </div>
    </div>
{% endif %}
```

## Заключение

`data-url-list` модулът предоставя прост и ефективен начин за създаване на динамични интерфейси. Следвайки тези основни принципи, можете бързо да интегрирате AJAX функционалност във вашите модули. 