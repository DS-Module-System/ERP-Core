# Data URL List Module - Documentation

## Общо описание

`data-url-list` модулът е JavaScript функционалност, която позволява динамично зареждане на съдържание в HTML елементи чрез AJAX заявки. Този модул е част от Core функционалността на ERP системата и се използва за създаване на интерактивни интерфейси без презареждане на страницата.

## Архитектура

### Основни компоненти

1. **loadApiV1Content()** - Основна функция за зареждане на съдържание
2. **setupApiV1Buttons()** - Настройване на бутони за AJAX взаимодействие
3. **loadContentInModal()** - Зареждане на съдържание в модален прозорец
4. **refreshApiV1Content()** - Обновяване на заредено съдържание

### Файлова структура

```
assets/
└── app.js                    # Основен JavaScript файл с API v1 функционалност
templates/
├── core/
│   └── modal-form-base.html.twig  # Базов шаблон за модални форми
└── {module}/
    └── form.html.twig        # Шаблони използващи data-url-list
```

## Използване

### Основен синтаксис

```html
<div class="api-v1" data-url-list="/api/endpoint"></div>
```

### Пример за използване в Delivery модула

```twig
{# templates/delivery/form.html.twig #}
{% if is_edit %}
    <div class="card mt-4">
        <div class="card-header">
            {{ 'delivery.cardTitle'|dtrans }}
        </div>
        <div class="card-body">
            <div class="api-v1" data-url-list="/delivery-items/{{ delivery.id }}"></div>
        </div>
    </div>
{% endif %}
```

### Контролер endpoint

```php
#[Route(path: '/delivery-items/{deliveryId}', name: 'delivery_item_')]
class DeliveryItemController extends CoreBaseController
{
    #[Route(path: '', name: 'list')]
    public function list(Request $request, int $deliveryId): Response
    {
        // Връща HTML съдържание за AJAX заявката
        return $this->render('delivery_item/index.html.twig', [
            'rows' => $rows,
            'form' => $form
        ]);
    }
}
```

## JavaScript API

### loadApiV1Content(targetElement = null)

Основна функция за зареждане на съдържание.

**Параметри:**
- `targetElement` (optional): Конкретен DOM елемент за зареждане. Ако не е посочен, зарежда всички `.api-v1` елементи.

**Пример:**
```javascript
// Зареждане на всички api-v1 елементи
loadApiV1Content();

// Зареждане на конкретен елемент
const element = document.querySelector('.my-api-element');
loadApiV1Content(element);
```

### setupApiV1Buttons(container)

Настройва бутони в контейнера за AJAX взаимодействие.

**Параметри:**
- `container`: DOM елемент, в който да се търсят бутони

**Функционалност:**
- Автоматично настройва линкове и бутони с `href` или `data-ajax-url`
- Отваря `/create` и `/edit` URL-и в модални прозорци
- Предотвратява дублиране на event listeners

### loadContentInModal(url, title = 'Form')

Зарежда съдържание в модален прозорец.

**Параметри:**
- `url`: URL за зареждане
- `title`: Заглавие на модалния прозорец

**Пример:**
```javascript
loadContentInModal('/delivery-items/1/create', 'Създаване на артикул');
```

### refreshApiV1Content(targetElement = null)

Обновява заредено съдържание.

**Параметри:**
- `targetElement` (optional): Конкретен елемент за обновяване

## HTML атрибути

### data-url-list

Указва URL за зареждане на съдържание.

```html
<div class="api-v1" data-url-list="/api/data"></div>
```

### data-ajax-url

Алтернативен атрибут за AJAX URL на бутони.

```html
<button data-ajax-url="/api/action">Действие</button>
```

### data-ajax-handled

Вътрешен атрибут, който предотвратява дублиране на event listeners.

## Модални форми

### Автоматично създаване

Модулът автоматично създава модални прозорци за форми:

```javascript
// Автоматично се създава модален прозорец
loadContentInModal('/delivery-items/1/create');
```

### Структура на модала

```html
<div id="api-v1-modal" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Заглавие</h5>
                <button type="button" class="btn-close"></button>
            </div>
            <div class="modal-body">
                <!-- Заредено съдържание -->
            </div>
        </div>
    </div>
</div>
```

## Обработка на форми

### Автоматична обработка

Модулът автоматично обработва форми в модални прозорци:

1. **Submit**: Автоматично се изпраща чрез AJAX
2. **Success**: Модалът се затваря и съдържанието се обновява
3. **Error**: Показва се съобщение за грешка

### setupModalFormHandling(modal, customUrl = null)

Настройва обработката на форми в модал.

**Параметри:**
- `modal`: DOM елемент на модала
- `customUrl` (optional): Персонализиран URL за submit

## Error Handling

### Сетка за грешки

При грешка се показва:

```html
<div class="alert alert-danger">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <div>
            <strong>Грешка при зареждане:</strong><br>
            ${error.message}
        </div>
    </div>
    <div class="mt-2">
        <button type="button" class="btn btn-sm btn-outline-danger retry-button">
            <i class="fas fa-redo me-1"></i>Опитай отново
        </button>
    </div>
</div>
```

### Retry функционалност

Бутонът "Опитай отново" автоматично презарежда съдържанието.

## Loading States

### Spinner при зареждане

```html
<div class="text-center py-4">
    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Зареждане...</span>
    </div>
    <div class="mt-2 text-muted">Зареждане на съдържание...</div>
</div>
```

## Интеграция с други модули

### Delivery модул

```twig
{# В delivery form.html.twig #}
<div class="api-v1" data-url-list="/delivery-items/{{ delivery.id }}"></div>
```

### Invoice модул

```twig
{# В invoice form.html.twig #}
<div class="api-v1" data-url-list="/invoice-items/{{ invoice.id }}"></div>
```

### Client модул

```twig
{# В client form.html.twig #}
<div class="api-v1" data-url-list="/client-orders/{{ client.id }}"></div>
```

## Best Practices

### 1. Използване на правилни URL-и

```html
<!-- Правилно -->
<div class="api-v1" data-url-list="/delivery-items/{{ delivery.id }}"></div>

<!-- Грешно -->
<div class="api-v1" data-url-list="/delivery-items/1"></div>
```

### 2. Контролер структура

```php
#[Route(path: '/delivery-items/{deliveryId}', name: 'delivery_item_')]
class DeliveryItemController extends CoreBaseController
{
    #[Route(path: '', name: 'list')]
    public function list(Request $request, int $deliveryId): Response
    {
        // Проверка дали заявката е AJAX
        if ($request->isXmlHttpRequest()) {
            return $this->render('delivery_item/index.html.twig', [
                'rows' => $rows,
                'form' => $form
            ]);
        }
        
        // Обикновена заявка - redirect или error
        throw $this->createNotFoundException();
    }
}
```

### 3. Шаблон структура

```twig
{# delivery_item/index.html.twig #}
{# Не използвай extends за AJAX заявки #}
{# {% extends 'base.html.twig' %} #}

{% import 'core/default_templates/data_table_macro.html.twig' as data_table %}

{{ data_table.table(rows, {
    "newLink": data_table.new_btn('delivery_item_create', {"deliveryId": app.request.get('deliveryId')}),
    "cols" : {
        "tableAction": {
            "label": "core.module.actionTitle",
            "edit": path('delivery_item_edit', {"id": -1, "deliveryId": app.request.get('deliveryId')}),
        },
        // ... други колони
    }
}) }}
```

### 4. Security

```php
// Проверка на permissions
#[IsGranted('ROLE_DELIVERY_VIEW')]
public function list(Request $request, int $deliveryId): Response
{
    // Проверка дали потребителят има достъп до конкретната доставка
    $delivery = $this->em->getRepository(Delivery::class)->find($deliveryId);
    if (!$delivery || !$this->isGranted('ROLE_DELIVERY_VIEW', $delivery)) {
        throw $this->createAccessDeniedException();
    }
    
    // ... останалата логика
}
```

## Troubleshooting

### Често срещани проблеми

#### 1. Съдържанието не се зарежда

**Причина:** Неправилен URL или липсващ контролер
**Решение:** Проверете URL-а и съществуването на контролера

#### 2. Модалът не се отваря

**Причина:** Липсва Bootstrap JavaScript
**Решение:** Уверете се, че Bootstrap JS е включен

#### 3. Формата не се изпраща

**Причина:** Липсва CSRF token
**Решение:** Добавете CSRF защита в формите

#### 4. Грешки в конзолата

**Причина:** JavaScript грешки
**Решение:** Проверете конзолата за грешки и поправете ги

### Debug режим

```javascript
// Включване на debug режим
console.log('API v1 elements found:', document.querySelectorAll('.api-v1').length);
console.log('Loading content for:', element.getAttribute('data-url-list'));
```

## Performance оптимизации

### 1. Lazy Loading

Зареждайте съдържание само когато е необходимо:

```javascript
// Зареждане при видимост
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            loadApiV1Content(entry.target);
            observer.unobserve(entry.target);
        }
    });
});

document.querySelectorAll('.api-v1').forEach(el => observer.observe(el));
```

### 2. Caching

Кеширайте заредено съдържание:

```javascript
const cache = new Map();

function loadApiV1ContentWithCache(element) {
    const url = element.getAttribute('data-url-list');
    
    if (cache.has(url)) {
        element.innerHTML = cache.get(url);
        return;
    }
    
    // Зареждане и кеширане
    fetch(url).then(response => response.text()).then(html => {
        cache.set(url, html);
        element.innerHTML = html;
    });
}
```

## Заключение

`data-url-list` модулът предоставя мощен и гъвкав начин за създаване на динамични интерфейси в ERP системата. Той позволява:

- Динамично зареждане на съдържание без презареждане на страницата
- Интерактивни модални форми
- Автоматична обработка на грешки
- Интеграция с всички модули на системата

Следвайки best practices и правилната структура, можете да създадете богати потребителски интерфейси, които подобряват потребителското изживяване. 