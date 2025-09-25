/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

/** Лимит элементов коллекции */
LimitNewOrderService = 5;

/** Глобальный объект для отслеживания удаленных элементов коллекции */
DeletedNewOrderServiceItems = new Map();

/** Глобальный индекс текущего элемента коллекции */
IndexOrderService = 0;

executeFunc(function newAddOrderService()
{
    if(typeof htmx === "undefined" || htmx === null)
    {
        return false;
    }

    /**
     * Кнопка добавления формы с элементом коллекции
     * */

    let addElementCollectionFormAButton = document.getElementById("collapse_new_order_form_serv");

    if(addElementCollectionFormAButton === "undefined" || addElementCollectionFormAButton === null)
    {
        return false;
    }

    const addOrderServiceForm = document.forms.add_order_service_to_order_form;

    /** Повторный запрос, если форма не подгрузилась при загрузке страницы */
    if(typeof addOrderServiceForm === "undefined" || addOrderServiceForm === null)
    {
        htmx.ajax('GET', addElementCollectionFormAButton.getAttribute('hx-get'), {target: addElementCollectionFormAButton});
        return false;
    }

    /**
     * Кнопка скытия collapse
     */

    const hideCollapseAButton = addOrderServiceForm.querySelector('[data-bs-dismiss="collapse"]');

    if(typeof hideCollapseAButton === "undefined" || hideCollapseAButton === null)
    {
        return false;
    }

    hideCollapseAButton.addEventListener("click", function()
    {
        let collapse = bootstrap.Collapse.getOrCreateInstance(addElementCollectionFormAButton);
        collapse.hide(); // Принудительно скрыть
    });

    /**
     * Срываем collapse когда перемещаем курсор к заголовкам навигации
     */

    document.querySelector('.modal-header').addEventListener("mouseenter", (e) =>
    {
        if(!addElementCollectionFormAButton.classList.contains("show"))
        {
            return
        } else
        {
            let collapse = bootstrap.Collapse.getOrCreateInstance(addElementCollectionFormAButton);
            collapse.hide();
        }
    });

    /**
     * Начальный select для изменения формы с элементом коллекции
     */

    const orderService = document.getElementById(addOrderServiceForm.name + '_serv');

    if(typeof orderService === "undefined" || orderService === null)
    {
        return false;
    }

    orderService.addEventListener('change', function(event)
    {
        changeOrderService(addOrderServiceForm);
    })

    return true;
}, 800);

/** Изменение формы услуг */
async function changeOrderService(form)
{
    const data = new FormData(form);

    // Удаляем токен из формы
    data.delete(form.name + '[_token]');

    // Удаляем поля, которые формируются динамически на форме
    data.delete(form.name + '[name]');
    data.delete(form.name + '[price]');
    //data.delete(form.name + '[period]');
    data.delete(form.name + '[time]');

    await fetch(form.action, {
        method: form.method,
        cache: 'no-cache',
        credentials: 'same-origin',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
        body: data,
    })

        .then((response) =>
        {
            if(response.status !== 200)
            {
                return false;
            }

            return response.text();
        })

        .then((data) =>
        {
            if(data)
            {

                /** @type {Document} */
                const result = parseFormData(data);

                let nameFormField = result.getElementById('add_order_service_to_order_form_name');

                /** Обнуляем форму при выборе некорректного сервиса (без uuid) */
                if(nameFormField.value === '')
                {
                    document.getElementById('field_name') && (document.getElementById('field_name').innerHTML = '');
                    document.getElementById('field_price') && (document.getElementById('field_price').innerHTML = '');
                    document.getElementById('field_date') && (document.getElementById('field_date').innerHTML = '');
                    document.getElementById('field_period') && (document.getElementById('field_period').innerHTML = '');

                    document.getElementById('add_order_service_to_order_form_order_service_add')
                    && (document.getElementById('add_order_service_to_order_form_order_service_add').remove());

                    return;
                }

                if(nameFormField && nameFormField.value !== '')
                {
                    document.getElementById('field_name').replaceWith(result.getElementById('field_name'))
                }

                let priceFormField = result.getElementById('add_order_service_to_order_form_price');

                if(priceFormField && priceFormField.type !== "hidden")
                {
                    fieldReplace(document.getElementById('field_price'), result.getElementById('field_price'))
                }

                let dateFormField = result.getElementById('add_order_service_to_order_form_date')

                if(dateFormField && dateFormField.type !== "hidden")
                {
                    fieldReplace(document.getElementById('field_date'), result.getElementById('field_date'))
                    initDatapicker(dateFormField)
                }

                let periodFormField = result.getElementById('add_order_service_to_order_form_period');

                if(periodFormField && periodFormField.type !== "hidden")
                {
                    fieldReplace(document.getElementById('field_period'), result.getElementById('field_period'))

                    const period = document.getElementById("add_order_service_to_order_form_period")

                    /** Отправляем все поля формы при изменении периода */
                    period.addEventListener('change', function(event)
                    {
                        let form = this.closest("form");

                        changeOrderService(form);
                    })
                }

                /** Кнопки управления в форме с элементом коллекции */
                const service_buttons = document.getElementById("service_buttons")

                /** Кнопка добавления элемента коллеции в заказ - динамическая, появляется после подгрузки всех валидных полей формы */
                const addButton = result.getElementById("add_order_service_to_order_form_order_service_add")

                if(
                    addButton
                    && !document.getElementById("add_order_service_to_order_form_order_service_add")
                    && false === periodFormField.classList.contains('is-invalid')
                )
                {
                    /** Вставляем кнопку в форму с параметрами услуги */
                    service_buttons.append(addButton)

                    addButton.addEventListener("click", function(event)
                    {
                        let form = this.closest("form");

                        addOrderService(form)
                    });
                }

            }
        });
}

/** Добавление услуги в заказ */
function addOrderService(form)
{
    /** элементы для управления коллекцией */
    const collectionControls = document.getElementById('service_collcetion_controls');

    /** название формы, куда будет добавляться элемент коллекции */
    const formNameToAdd = collectionControls.querySelector('a').dataset.form;

    /** блок с элементами коллекции */
    const serviceCollection = document.getElementById('service_сollection_new_order');

    /** кнопка добавления элемента коллекции */
    const addCollectionButton = document.getElementById(`add_item_` + formNameToAdd);

    /** @type {number} */
    IndexOrderService = addCollectionButton.dataset.index;

    /** проверка на наличие ранее удаленных элементов */
    if(DeletedNewOrderServiceItems.size > 0)
    {
        let last = '';

        /** получаем последний индекс удаленного элемента */
        DeletedNewOrderServiceItems.forEach(function(value)
        {
            last = value;
        });

        /**
         * @type {number}
         * меняем глобальный индекс текущего элемента коллекции на индекс удаленного элемента */
        IndexOrderService = last

        /** удаляем элемент из хранилища удаленных элементов */
        DeletedNewOrderServiceItems.delete('key' + last)
    }

    /**
     * @type {string}
     * id прототипа из кнопки добавления элемента в коллекцию
     * */
    const prototypeName = addCollectionButton.dataset.prototype;

    /**
     * @type {HTMLDivElement}
     * элемент с прототипом
     * */
    const prototypeElement = document.getElementById(prototypeName);

    /**
     * @type {string}
     *  контент прототипа
     * */
    let prototypeContent = prototypeElement.dataset.prototype;


    /**
     * @type {number}
     * увеличиваем индекс элемента коллекции
     * */
    let index = parseInt(addCollectionButton.dataset.index) + 1;

    /** добавляем текущее значение к кнопке для отслеживания увеличения элементов коллекции */
    addCollectionButton.setAttribute('data-index', index)

    /** ограничение максимального количество элементов коллекции */
    if(parseInt(addCollectionButton.dataset.index) > LimitNewOrderService)
    {
        addCollectionButton.setAttribute('data-index', LimitNewOrderService)
        return;
    }

    /** Добавление индекса для элементов коллекции по заполнителю из формы prototype_name => '__service__' */
    prototypeContent = prototypeContent.replace(/__service__/g, IndexOrderService);

    /** @type {Document} */
    const result = parseFormData(prototypeContent);

    /**
     * @type {HTMLDivElement}
     * элемент с прототипом и индексами для элемента коллекции по заполнителю из формы prototype_name => '__service__'
     * */
    const prototypItem = result.getElementById('prototypeItem').querySelector('.item-service');

    let prototypId = prototypItem.querySelector(`.` + formNameToAdd + `_` + `${IndexOrderService}` + `_serv`)
    let prototypName = prototypItem.querySelector(`.` + formNameToAdd + `_` + `${IndexOrderService}` + `_name`)
    let prototypDate = prototypItem.querySelector(`.` + formNameToAdd + `_` + `${IndexOrderService}` + `_date`)
    let prototypPeriod = prototypItem.querySelector(`.` + formNameToAdd + `_` + `${IndexOrderService}` + `_period`)
    let prototypPrice = prototypItem.querySelector(`.` + formNameToAdd + `_` + `${IndexOrderService}` + `_money`)

    /** массив с данными из формы */
    const formData = Object.fromEntries(new FormData(form).entries());

    const formId = formData["add_order_service_to_order_form[serv]"]
    const formName = formData["add_order_service_to_order_form[name]"]
    const formDate = formData["add_order_service_to_order_form[date]"]
    const formPeriod = formData["add_order_service_to_order_form[period]"].split("_")
    const formPeriodUid = formPeriod[0] // uuid
    const formPeriodText = formPeriod[1] // text
    const formPrice = formData["add_order_service_to_order_form[price]"]

    /**
     * Price
     */

    const minPrice = form.elements["add_order_service_to_order_form[price]"].dataset.min

    prototypPrice.setAttribute('data-min', minPrice)

    /**
     * Name
     */
    let prototypeIdParentTd = prototypId.closest('td')
    prototypeIdParentTd.append(formName);

    /**
     * Period
     */
    let prototypePeriodParentTd = prototypPeriod.closest('td')
    prototypePeriodParentTd.append(formPeriodText);

    /**
     * Date
     */
    let prototypeDateParentTd = prototypDate.closest('td')
    prototypeDateParentTd.append(formDate);

    /** Замена значений в прототипе */
    prototypId.value = formId
    prototypName.value = formName
    prototypDate.value = formDate
    prototypPrice.value = formPrice
    prototypPeriod.value = formPeriodUid
    prototypPeriod.textContent = formPeriodText

    /** Проверка уникальности услуги в коллекции */
    if(false === isOrderServiceUnique(formId, formDate, formPeriodUid))
    {
        let noticeToast =
            '{ "type":"danger" , ' +
            '"header":"Ошибка при добавлении услуги в заказ"  , ' +
            `"message" : "Услуга с названием ` + formName + `, датой ` + formDate + `, периодом ` + formPeriodText + ` уже добавлена" }`;

        createToast(JSON.parse(noticeToast));
        return;
    }

    /** вставляем элемент в коллекцию */
    serviceCollection.append(prototypItem);

    if(document.body.contains(prototypItem))
    {
        deleteOrderServiceItem(prototypItem)
        changeOrderServicePriceByClick(prototypItem)
    }

    /** Закрываем Collapse после добавления */
    let Collapse = document.getElementById('add_item_new_order_form_serv')

    if(Collapse)
    {
        Collapse.click()
    }
}

/** Проверка уникальности услуги в коллекции */
function isOrderServiceUnique(servValue, dateValue, periodValue)
{

    const rows = document.querySelectorAll("#service_сollection_new_order tr");

    for(const row of rows)
    {

        const servInput = row.querySelector('input[name*="[serv]"]');
        const dateInput = row.querySelector('input[name*="[date]"]');
        let periodEl = row.querySelector('select[name*="[period]"]');
        if(!periodEl)
        {
            periodEl = row.querySelector('input[name*="[period]"]');
        }

        if(!servInput || !dateInput || !periodEl)
        {
            continue;
        }

        const existingServ = servInput.value;
        const existingDate = dateInput.value;
        const existingPeriod = periodEl.value;

        if(
            existingServ === servValue &&
            existingDate === dateValue &&
            existingPeriod === periodValue
        )
        {
            return false;
        }
    }

    return true;
}

/** Кнопки изменения цены */
function changeOrderServicePriceByClick(element)
{
    element.querySelectorAll('.order-service-price-minus').forEach(function(btn)
    {
        btn.addEventListener('click', function()
        {
            let priceInput = document.getElementById(this.dataset.id);

            let result = parseFloat(priceInput.value.replace(",", "."));
            result = result - (this.dataset.step ? this.dataset.step * 1 : 1);

            /** ограниечение по data-min в input */
            if(priceInput.dataset.min)
            {
                let min = parseFloat(priceInput.dataset.min.replace(",", "."));

                if(result < min)
                {
                    return;
                }
            }

            if(result <= 0)
            {
                return;
            }

            priceInput.value = result;

        });

    });

    element.querySelectorAll('.order-service-price-plus').forEach(function(btn)
    {

        btn.addEventListener('click', function()
        {
            let priceInput = document.getElementById(this.dataset.id);

            let result = parseFloat(priceInput.value.replace(",", "."));
            result = result + (this.dataset.step ? this.dataset.step * 1 : 1);

            if(priceInput.dataset.max && result > priceInput.dataset.max)
            {
                return;
            }

            /** изменение value у input */
            priceInput.value = result;
        });

    });
}

/** Кнопки удаления элемента коллекции */
function deleteOrderServiceItem(element)
{
    element.querySelectorAll('.del-item-service').forEach(function(btn)
    {
        btn.addEventListener('click', function()
        {
            /**
             * @type {string}
             * */
            let deteteItemId = btn.id.replace(/delete-/g, '');

            /**
             * @type {HTMLDivElement}
             * элемент для удаления
             * */
            const itemForDelete = document.getElementById(deteteItemId);

            /**
             * @type {string}
             * индекс для удаления
             * */
            const deleteIndex = btn.id.replace(/delete-order_form_service-/g, '');
            // const deleteIndex = parseInt(btn.id.match(/\d+/));

            /** добавляем индекс удаленного элемента для отслеживания */
            DeletedNewOrderServiceItems.set('key' + deleteIndex, deleteIndex)

            /** если элемент удалился - получаем текущий индекс коллекции и уменьшаем его в кнопке добавления элементов */
            if(itemForDelete)
            {

                let addBtn = document.getElementById('add_item_new_order_form_serv');

                const newIndex = parseInt(addBtn.dataset.index) - 1;

                addBtn.setAttribute('data-index', newIndex)

                itemForDelete.remove()
            }
        });
    });
}

/** Инициализация Datapicker + отправка формы при выборе даты */
function initDatapicker(field)
{
    const datepicker = MCDatepicker.create({
        el: '#' + field.id,
        bodyType: 'modal', // ‘modal’, ‘inline’, or ‘permanent’.
        autoClose: false,
        closeOndblclick: true,
        closeOnBlur: false,
        customOkBTN: 'OK',
        customClearBTN: datapickerLang[$locale].customClearBTN,
        customCancelBTN: datapickerLang[$locale].customCancelBTN,
        firstWeekday: datapickerLang[$locale].firstWeekday,
        dateFormat: 'DD.MM.YYYY',
        customWeekDays: datapickerLang[$locale].customWeekDays,
        customMonths: datapickerLang[$locale].customMonths,
        minDate: new Date(),
    });

    const addOrderServiceForm = document.forms.add_order_service_to_order_form;

    datepicker.onSelect(function(date, formatedDate)
    {
        changeOrderService(addOrderServiceForm);
    });
}

/** Парсинг данных формы из ответа сервера */
function parseFormData(data)
{
    const parser = new DOMParser();
    return parser.parseFromString(data, 'text/html');
}

/** Плавная замена элемента формы */
function fieldReplace(oldField, newField)
{
    newField.classList.add("fade");

    oldField.replaceWith(newField)

    void newField.offsetWidth;
    newField.classList.add("show");
}