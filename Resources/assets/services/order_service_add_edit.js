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
 *
 */

/** Лимит элементов коллекции */
LimitIEditOrderService = 6;

/** глобальный объект для отслеживания удаленных элементов коллекции */
DeletedIEditOrderService = new Map();

/** глобальный индекс текущего элемента коллекции */
IndexEditOrderService = 0;

executeFunc(function editAddOrderService()
{
    const orderServiceCollection = document.getElementById('service_сollection_edit_order');

    if(typeof orderServiceCollection === "undefined" || orderServiceCollection === null)
    {
        return false;
    }

    if(orderServiceCollection.getAttribute('usage') === null)
    {
        changeOrderServiceSumByInput(orderServiceCollection)
        changeOrderServiceSumByClick(orderServiceCollection)

        deleteOrderServiceItem(orderServiceCollection)
        initDatapickerAll()

        orderServiceCollection.setAttribute('usage', 'true')
        return true;
    }

    const addOrderServiceButton = document.getElementById('add_item_edit_order_form_serv');

    if(typeof addOrderServiceButton === "undefined" || addOrderServiceButton === null)
    {
        return false;
    }

    const addOrderServiceForm = document.forms.add_order_service_to_order_form;

    if(typeof addOrderServiceForm === "undefined" || addOrderServiceForm === null)
    {
        return false;
    }

    let OrderService = document.getElementById(addOrderServiceForm.name + '_serv');

    if(typeof OrderService === "undefined" || OrderService === null)
    {
        return false;
    }

    OrderService.addEventListener('change', function(event)
    {
        changeOrderService(addOrderServiceForm);
    })

    return true;
});

//-------------------------------------

async function changeOrderService(form)
{
    const data = new FormData(form);

    /** Удаляем токен из формы */
    data.delete(form.name + '[_token]');

    /** Удаляем поля, которые формируются динамически на форме */
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

                if(nameFormField && nameFormField.value)
                {
                    document.getElementById('field_name').replaceWith(result.getElementById('field_name'))
                }

                let priceFormField = result.getElementById('add_order_service_to_order_form_price');

                serviceMlfinPrice = priceFormField.dataset.min

                if(priceFormField && priceFormField.type !== "hidden")
                {
                    animateReplace(document.getElementById('field_price'), result.getElementById('field_price'))
                }

                let dateFormField = result.getElementById('add_order_service_to_order_form_date')

                if(dateFormField && dateFormField.type !== "hidden")
                {
                    animateReplace(document.getElementById('field_date'), result.getElementById('field_date'))
                    initDatapickerForField(dateFormField)
                }

                let periodFormField = result.getElementById('add_order_service_to_order_form_period');

                if(periodFormField && periodFormField.type !== "hidden")
                {
                    /** Заменяем блок с полем формы */
                    animateReplace(document.getElementById('field_period'), result.getElementById('field_period'))

                    const period = document.getElementById("add_order_service_to_order_form_period")

                    period.addEventListener('change', function(event)
                    {
                        let form = this.closest("form");
                        changeOrderService(form);
                    })
                }

                const service_buttons = document.getElementById("service_buttons")
                const addButton = result.getElementById("add_order_service_to_order_form_order_service_add")

                if(
                    addButton &&
                    !document.getElementById("add_order_service_to_order_form_order_service_add")
                    && false === periodFormField.classList.contains('is-invalid')
                )
                {
                    service_buttons.append(addButton)

                    addButton.addEventListener("click", function(event)
                    {
                        let form = this.closest("form");

                        submitOrderService(form)
                    });
                }

            }
        });
}

//-------------------------------------

async function submitOrderService(form)
{
    const data = new FormData(form);

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

            const contentType = response.headers.get("content-type");

            if(contentType !== 'application/json')
            {
                return false;
            }

            return response.json();
        })

        .then((result) =>
        {
            if(result.valid === true)
            {
                /** элементы для управлением коллекцией */
                const collectionControls = document.getElementById('service_collcetion_controls');

                /** название формы, куда будет добавляться элемент коллекции */
                const formNameToAdd = collectionControls.querySelector('a').dataset.form;

                /**
                 * блок с элементами коллекции
                 * */
                const serviceCollection = document.getElementById('service_сollection_edit_order');

                /**
                 * кнопка добавления элемента коллекции
                 * */
                const addCollectionButton = document.getElementById(`add_item_` + formNameToAdd);

                if(addCollectionButton)
                {
                    /** @type {number} */
                    IndexEditOrderService = addCollectionButton.dataset.index;
                }

                /** проверка на наличие ранее удаленных элементов */
                if(DeletedIEditOrderService.size > 0)
                {

                    let last = '';

                    /** получаем последний индекс удаленного элемента */
                    DeletedIEditOrderService.forEach(function(value)
                    {
                        last = value;
                    });

                    /**
                     * @type {number}
                     * меняем глобальный индекс текущего элемента коллекции на индекс удаленного элемента */
                    AvitoKitCollectionItemKey = last

                    /** удаляем элемент из хранилища удаленных элементов */
                    DeletedIEditOrderService.delete('key' + last)
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
                if(parseInt(addCollectionButton.dataset.index) > LimitIEditOrderService)
                {
                    addCollectionButton.setAttribute('data-index', LimitIEditOrderService)
                    return;
                }

                /** Добавление индекса для элементов коллекции по заполнителю из формы prototype_name => '__service__' */
                prototypeContent = prototypeContent.replace(/__service__/g, IndexEditOrderService);

                /** @type {Document} */
                const result = parseFormData(prototypeContent);

                /**
                 * @type {HTMLDivElement}
                 * элемент с прототипом и индексами для элемента коллекции по заполнителю из формы prototype_name => '__service__'
                 * */
                const prototypItem = result.getElementById('prototypeItem').querySelector('.item-service');

                let prototypId = prototypItem.querySelector(`.` + formNameToAdd + `_` + `${IndexEditOrderService}` + `_serv`)
                let prototypName = prototypItem.querySelector(`.` + formNameToAdd + `_` + `${IndexEditOrderService}` + `_name`)
                let prototypDate = prototypItem.querySelector(`.` + formNameToAdd + `_` + `${IndexEditOrderService}` + `_date`)
                let prototypPeriod = prototypItem.querySelector(`.` + formNameToAdd + `_` + `${IndexEditOrderService}` + `_period`)
                let prototypPrice = prototypItem.querySelector(`.` + formNameToAdd + `_` + `${IndexEditOrderService}` + `_money`)

                /** Данные формы */
                const formData = Object.fromEntries(data.entries());

                const formId = formData["add_order_service_to_order_form[serv]"]
                const formName = formData["add_order_service_to_order_form[name]"]
                const formDate = formData["add_order_service_to_order_form[date]"]
                const formPeriodUid = formData["add_order_service_to_order_form[period]"] // uuid
                const formPrice = formData["add_order_service_to_order_form[price]"]

                const formPeriodEl = form.elements['add_order_service_to_order_form[period]']
                const selectedPeriod = formPeriodEl.options[formPeriodEl.selectedIndex];
                const formPeriodTime = selectedPeriod.dataset.time // text

                /**
                 * Price
                 */
                prototypPrice.setAttribute('data-min', formPrice)

                /**
                 * Name
                 */
                let idPrototypeParentEl = prototypId.closest('td')
                idPrototypeParentEl.append(formName);

                /**
                 * Period
                 */
                let periodPrototypeParentEl = prototypPeriod.closest('td')
                periodPrototypeParentEl.append(formPeriodTime);

                /**
                 * Date
                 */
                let datePrototypeParentEl = prototypDate.closest('td')
                datePrototypeParentEl.append(formDate);

                prototypId.value = formId
                prototypName.value = formName
                prototypDate.value = formDate
                prototypPrice.value = formPrice
                prototypPeriod.value = formPeriodUid // uuid
                prototypPeriod.textContent = formPeriodTime // text

                if(false === isOrderServiceUnique(formId, formDate, formPeriodUid))
                {
                    /** Закрываем модальное окно */
                    let Modal = document.getElementById("modal");
                    let modalInstance = bootstrap.Modal.getInstance(Modal);
                    modalInstance.hide();

                    let noticeToast =
                        '{ "type":"danger" , ' +
                        '"header":"Ошибка при добавлении услуги в заказ"  , ' +
                        `"message" : "Услуга с названием ` + formName + `, датой ` + formDate + `, периодом ` + formPeriodTime + ` уже добавлена" }`;

                    createToast(JSON.parse(noticeToast));
                    return;
                }

                /** вставляем элемент в коллекцию */
                serviceCollection.append(prototypItem);

                if(document.body.contains(prototypItem))
                {

                    /** Закрываем модальное окно */
                    let Modal = document.getElementById("modal");
                    let modalInstance = bootstrap.Modal.getInstance(Modal);
                    modalInstance.hide();

                    changeOrderServiceSumByInput(prototypItem)
                    changeOrderServiceSumByClick(prototypItem)

                    deleteOrderServiceItem(prototypItem)

                    let result = parseFloat(formPrice.replace(",", "."));

                    modifyTotalOrderSum()
                }
            }

        })
}

//-------------------------------------

function isOrderServiceUnique(servValue, dateValue, periodValue)
{
    const rows = document.querySelectorAll("#service_сollection_edit_order tr");

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

/** Изменяем стоимость по вводу */
function changeOrderServiceSumByInput(element)
{
    element.querySelectorAll('.order-service-price').forEach(function(input)
    {
        let timer;

        input.addEventListener('input', function(event)
        {
            clearTimeout(timer);

            timer = setTimeout(() =>
            {
                modifyTotalOrderSum()

            }, 1000);

        });
    });
}

/** Изменяем стоимость по клику */
function changeOrderServiceSumByClick(element)
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

            modifyTotalOrderSum()


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

            priceInput.value = result;

            modifyTotalOrderSum()

        });

    });
}

/** Модифицируем стоимость за услуги и общую стоимость */
function modifyTotalOrderSum()
{
    let serviceCollection = document.getElementById('service_сollection_edit_order');
    let serviceSumEl = document.getElementById('service_sum');
    let serviceSumResult = 0

    serviceCollection.querySelectorAll('.order-service-price').forEach(function(input)
    {
        let inputPriceInt = parseInt(input.value, 10)
        const priceMin = parseInt(input.dataset.min, 10)

        if(inputPriceInt < priceMin || Number.isNaN(inputPriceInt))
        {
            let noticeToast =
                '{ "type":"danger" , ' +
                '"header":"Ошибка при изменении стоимости"  , ' +
                `"message" : "Минимальная стоимость услуги ` + `${priceMin}` + ` ₽" }`;

            createToast(JSON.parse(noticeToast));

            input.value = priceMin
            inputPriceInt = priceMin
        }

        serviceSumResult += inputPriceInt
    });

    /** Изменение общей стоимости за услуги */
    let serviceSumFormatted = serviceSumResult.toLocaleString("ru-RU") + " ₽";
    serviceSumEl.textContent = serviceSumFormatted

    /** Стоимость Итого */
    let totalAllSumEl = document.getElementById('total_all_sum');

    /** С учетом стоимости продуктов */
    let productSum = document.getElementById('total_product_sum');
    let productSumParse = productSum.textContent.replace(/[^\d]/g, "");
    let productSumInt = parseInt(productSumParse, 10);

    let serviceSumParse = serviceSumEl.textContent.replace(/[^\d]/g, "");
    let serviceSumInt = parseInt(serviceSumParse, 10);

    const totalAllSumRes = productSumInt + serviceSumInt

    const totalSumFormatted = totalAllSumRes.toLocaleString("ru-RU") + " ₽";
    totalAllSumEl.textContent = totalSumFormatted
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
            DeletedIEditOrderService.set('key' + deleteIndex, deleteIndex)

            /** если элемент удалился - получаем текущий индекс коллекции и уменьшаем его в кнопке добавления элементов */
            if(itemForDelete)
            {

                let addBtn = document.getElementById('add_item_edit_order_form_serv');
                const newIndex = parseInt(addBtn.dataset.index) - 1;

                addBtn.setAttribute('data-index', newIndex)

                /** Удаляем элемент */
                itemForDelete.remove()

                modifyTotalOrderSum()

            }
        });
    });
}

/** Инициализация Datapicker + отправка формы при выборе даты */
function initDatapickerForField(field)
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

/** Инициализация Datapicker + обновление поля с периодами */
function initDatapickerAll()
{
    const rows = document.querySelectorAll("#service_сollection_edit_order tr");

    for(const row of rows)
    {
        const dateInput = row.querySelector('input[name*="[date]"]');

        if(!dateInput)
        {
            continue;
        }

        let x9y7P90sZsRepeat = 100;

        setTimeout(function x9y7P90sZs()
        {
            if(x9y7P90sZs >= 1000)
            { return; }

            if(typeof MCDatepicker === 'object')
            {
                const [day, month, year] = dateInput.value.split('.');
                $selectedDate = new Date(+year, month - 1, +day);

                let currentDate = new Date();
                const nextDay = new Date(currentDate.setDate(currentDate.getDate()));

                currentDate = new Date();
                const limitDay = new Date(currentDate.setDate(currentDate.getDate() + 7));

                const datepicker = MCDatepicker.create({
                    el: '#' + dateInput.id,
                    bodyType: 'modal',
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
                    selectedDate: $selectedDate,
                    minDate: nextDay,
                    maxDate: limitDay,
                });

                const editOrderForm = document.forms.edit_order_form;

                datepicker.onSelect(function(date, formatedDate)
                {
                    let dateFieldId = dateInput.id

                    updateServicePeriodField(editOrderForm, dateFieldId);
                });

                return;
            }

            x9y7P90sZsRepeat = x9y7P90sZsRepeat * 2;
            setTimeout(x9y7P90sZs, 100);

        }, 100);

    }
}

/** Обновляет поле с периодами в основной форме */
async function updateServicePeriodField(form, fieldId)
{
    const data = new FormData(form);

    /** Удаляем токен из формы */
    data.delete(form.name + '[_token]');

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
            const periodFieldId = fieldId.replace("date", "period")

            /** @type {Document} */
            const result = parseFormData(data);

            let collection = result.getElementById('service_сollection_edit_order')

            let resultPeriod = result.getElementById(periodFieldId)

            let currentPeriod = document.getElementById(periodFieldId)

            if(currentPeriod && resultPeriod)
            {
                animateReplace(currentPeriod, resultPeriod)
            }

        })
}

/** Парсинг данных формы из ответа сервера */
function parseFormData(data)
{
    const parser = new DOMParser();
    return parser.parseFromString(data, 'text/html');
}

/** Плавная замена поля формы */
function animateReplace(oldField, newField)
{
    newField.classList.add("fade");

    oldField.replaceWith(newField)

    void newField.offsetWidth;
    newField.classList.add("show");
}


