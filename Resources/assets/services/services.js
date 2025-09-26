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

/** Валидация периодов */
var TimeValidator =
    {
        validateTimeFormat(timeString)
        {
            const regex = /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/;
            return regex.test(timeString);
        },

        validateTimeRange(fromTime, toTime)
        {

            const [fromH, fromM] = fromTime.split(":").map(Number);
            const [toH, toM] = toTime.split(":").map(Number);

            const fromMinutes = fromH * 60 + fromM;
            const toMinutes = toH * 60 + toM;

            const submit_button = document.getElementById("service_form_service");
            if(fromMinutes >= toMinutes)
            {
                return {isValid : false, error : "Значение \"Время от\" должно быть меньше значения \"Время до\""};
            }

            return {isValid : true, error : null};
        },

        /** Валидация формы */
        setupTimeValidation(formName, timeFrom, timeTo, index)
        {

            const form = document.querySelector("[name=\"" + formName + "\"]");
            const fromInput = document.querySelector("[name=\"" + timeFrom + "\"]");
            const toInput = document.querySelector("[name=\"" + timeTo + "\"]");
            const errorElement = document.querySelector(".time-error" + index);

            const validate = () =>
            {
                const result = this.validateTimeRange(fromInput.value, toInput.value);


                if(errorElement)
                {
                    errorElement.textContent = result.isValid ? "" : result.error;
                    errorElement.style.display = result.isValid ? "none" : "block";

                    const submit_button = document.getElementById("service_form_service");

                    const time_errors = document.querySelectorAll(".time-error");
                    for(const time_error of time_errors)
                    {

                        if(time_error.style.display === "block")
                        {
                            submit_button.disabled = true;
                            break;
                        }
                        else
                        {
                            submit_button.disabled = false;
                        }
                    }

                }

                return result.isValid;
            };

            fromInput.addEventListener("change", validate);
            toInput.addEventListener("change", validate);

            return validate;
        },
    };


/** Валидация уже имеющихся периодов */
var cards = document.querySelectorAll(".card");

for(let index = 0; index < cards.length; index++)
{
    const res = TimeValidator.setupTimeValidation(
        "service_form",
        "service_form[period][" + index + "][frm]",
        "service_form[period][" + index + "][upto]",
        index,
    );
}

/** Добавление еще одного периода */
$addButtonPeriod = document.getElementById("periodAddCollection");

if($addButtonPeriod)
{
    /* Блок для новой коллекции */
    let $blockCollectionCall = document.getElementById("collection-period");

    if($blockCollectionCall)
    {
        $addButtonPeriod.addEventListener("click", function()
        {

            let $addButtonPeriod = this;
            /* получаем прототип коллекции  */
            let newForm = $addButtonPeriod.dataset.prototype;
            let index = $addButtonPeriod.dataset.index * 1;

            /* Замена '__periods__' в HTML-коде прототипа
             вместо этого будет число, основанное на том, сколько коллекций */
            newForm = newForm.replace(/__periods__/g, index);

            /* Вставляем новую коллекцию */
            let div = document.createElement("div");
            div.id = "item_service_form_period_" + index;
            div.classList.add("mb-3");
            div.classList.add("item-service");

            div.innerHTML = newForm;
            $blockCollectionCall.append(div);


            /* Удалить */
            (div.querySelector(".del-item-period"))?.addEventListener("click", deletePeriod);

            const delButton = div.querySelector(".del-item-period");
            delButton.dataset.delete = "item_service_form_period_" + (index).toString();

            //const timeErrorDiv = div.querySelector(".time-error");
            //timeErrorDiv.classList.add("time-error" + index);


            /* Увеличиваем data-index на 1 после вставки новой коллекции */
            $addButtonPeriod.dataset.index = (index + 1).toString();

            /* Плавная прокрутка к элементу */
            div.scrollIntoView({block : "center", inline : "center", behavior : "smooth"});


            /** Добавить валидацию периодов для нового периода */
            {
                TimeValidator.setupTimeValidation(
                    "service_form",
                    "service_form[period][" + index + "][frm]",
                    "service_form[period][" + index + "][upto]",
                    index,
                );
            }

        });
    }
}

/** Удаление периода */
document.querySelectorAll(".del-item-period").forEach(function(item)
{
    item.addEventListener("click", deletePeriod);
});

function deletePeriod()
{
    let services = document.querySelectorAll(".item-service").length;

    if(services > 1)
    {
        document.getElementById(this.dataset.delete).remove();
    }
}
