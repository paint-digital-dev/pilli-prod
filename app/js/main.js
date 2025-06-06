document.addEventListener("DOMContentLoaded", () => {
    // body scroll width
    let div = document.createElement('div');

    div.style.overflowY = 'scroll';
    div.style.width = '50px';
    div.style.height = '50px';

    document.body.append(div);
    let scrollWidth = div.offsetWidth - div.clientWidth;
    div.remove();

    function bodyRemoveScroll() {
        document.body.style.overflow = 'hidden';
        document.body.style.paddingRight = scrollWidth + 'px';
        document.querySelector('.header').style.padding = `12px ${scrollWidth + 12}px 12px 12px`;
    }

    function bodyAddScroll() {
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        document.querySelector('.header').style.padding = '';
    }

    try {
        const methodsPopupLayout = document.querySelector('.methods-popup__layout'),
            methodsImages = document.querySelectorAll('.methods__item-img'),
            methodsPopupImg = document.querySelector('.methods-popup__img'),
            methodsPopupDescr = document.querySelector('.methods-popup__descr'),
            methodsPopupContent = document.querySelector('.methods-popup__content'),
            methodsPopupClose = document.querySelector('.methods-popup__close'),
            methodsLinks = document.querySelectorAll('.methods__item-link');

        methodsLinks.forEach((link, i) => {
            link.addEventListener('click', () => {
                methodsPopupContent.scrollTop = 0;

                const currentMethod = brewingMethods[i];
                methodsPopupLayout.classList.add('methods-popup__layout_active');
                methodsPopupLayout.classList.remove('methods-popup__layout_inactive');
                bodyRemoveScroll(scrollWidth);

                methodsPopupImg.src = methodsImages[i].src;

                methodsPopupDescr.innerHTML = `
                <h3 class="title title_secondary">${currentMethod.title}</h3>
                <p>${currentMethod.description}</p>
                <ul>
                    ${currentMethod.steps.map(step => `<li>${step}</li>`).join('')}
                </ul>
            `;
            });
        })

        methodsPopupClose.addEventListener('click', closePopup);

        methodsPopupLayout.addEventListener('click', (e) => {
            if (e.target === methodsPopupLayout) {
                closePopup();
            }
        });

        function closePopup() {
            methodsPopupLayout.classList.remove('methods-popup__layout_active');
            methodsPopupLayout.classList.add('methods-popup__layout_inactive');
            bodyAddScroll();
        }
    } catch (e) {
    }

    // menu
    const menuOpen = document.querySelector('.menu-btn'),
        menuClose = document.querySelector('.menu-close'),
        menuLayout = document.querySelector('.menu-layout'),
        menu = document.querySelector('.menu');

    menuOpen.addEventListener('click', () => {
        menu.classList.add('menu_active');
        menuLayout.classList.add('menu-layout_active');
        bodyRemoveScroll(scrollWidth);
    });

    menuClose.addEventListener('click', () => {
        menu.classList.remove('menu_active');
        menuLayout.classList.remove('menu-layout_active');
        bodyAddScroll();
    });

    menuLayout.addEventListener('click', () => {
        menu.classList.remove('menu_active');
        menuLayout.classList.remove('menu-layout_active');
        bodyAddScroll();
    });

    //  Remove scroll if mini cart open
    function onClassChange(hasClass) {
        if (hasClass) {
            bodyRemoveScroll();
        } else {
            bodyAddScroll();
        }
    }

    onClassChange(document.body.classList.contains('woofc-show'));

    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (mutation.attributeName === 'class') {
                const hasClass = document.body.classList.contains('woofc-show');
                onClassChange(hasClass);
            }
        });
    });
    observer.observe(document.body, {attributes: true});
});

const brewingMethods = [
    {
        title: "Пуровер (Харіо)",
        description: "Для приготування кави цим способом використовують особливу воронку. Вона схожа на маленьку чашку, яка має отвір знизу та спеціальні спіральні жолоби для активної взаємодії кави з повітрям.",
        steps: [
            "Встановіть пуловер над чашкою, куда буде стікати готова кава, після чого прогрійте його невеликою кількістю окропу (t 90–95 °С).",
            "Наповніть кавовий фільтр свіжозмеленою кавою і встановіть у воронку так, щоб він не щільно прилягав до країв.",
            "Спочатку змочіть каву в фільтрі гарячою водою в пропорції 1:1, щоб смак почав розкриватися, та зачекайте секунд 30. Для вивільнення повітря в товщі кави можна обережно перемішати суміш, але обережно, щоб не пошкодити фільтр.",
            "Обережно заливайте каву окропом протягом 2-3 хвилин тоненькою цівкою, оминаючи краї пуловера, щоб кава не вийшла слабкою. Об'єм гарячої води залежить від кількості порцій кави.",
            "Після того як вся кава стече у чашку, зніміть воронку та насолоджуйтесь вашим ароматним напоєм."
        ]
    },
    {
        title: "Гейзерна кавоварка",
        description: "Цей тип кавоварки є одним з найпоширеніших у світі. Друга її назва - мока. Зараз, з розширенням асортименту різних сортів та попитом на specialty каву, цей метод набирає популярність, адже дозволяє розкрити найтонші нотки смаку і при цьому частинки кави відфільтровуються і не залишаються в чашці. Мока має досить просту конструкцію: нижня частина, яка наповнюється водою, фільтр, в який засипають каву, та верхня частина, у якій сама кава і накопичується.",
        steps: [
            "В нижню частину залийте воду, але не вище ніж рівень клапану безпеки.",
            "Наповніть фільтр кавою середнього помелу та встановіть його на кавоварку.",
            "Під'єднайте верхню частину моки та закрийте кришкою.",
            "Встановіть кавоварку на плиту, на невеликий вогонь.",
            "Під час нагрівання вода з нижньої частини почне підійматися і зверху почне збиратися кава. Коли через фільтр почне вже проходити світлий пар, час знімати моку з вогню.",
            "Обережно остудіть основу кавоварки холодною водою, та розлийте готовий напій у чашки. Смачного!"
        ]
    },
    {
        title: "В чашці",
        description: "Заварювання кави в чашці - це імерсійний спосіб приготування, бо кава контактує з водою весь час заварювання. Відсутність фільтрації робить напій концентрованим та більш насиченим смаком. Це найбільш зручний та швидкий спосіб, адже не потребує якихось додаткових девайсів. Температура води для заварювання кави має бути 93-95 градусів, а сама вода повинна бути якісною, адже це надалі впливає на смак готового напою. Якщо ви оберете чашку з товстими стінками, то тепло під час заварювання кави буде втрачатися повільніше. Помел кави має бути рівномірним і дрібним для збалансованого смаку.",
        steps: [
            "Коли вода в чайнику закипіла, зачекайте дві хвилини, щоб вона досягла необхідної температури.",
            "В чашці змішайте каву та додаткові інгредієнти за смаком (цукор, спеції), залийте каву водою, активно помішуючи.",
            "Каву слід залишити на 4 хвилини, щоб вона сповнилась смаком. Накривати чашку при цьому не потрібно.",
            "Ще раз необхідно перемішати каву, щоб часточки кави осіли на дно, та залишити каву ще на 10 хвилин.",
            "Смачного!"
        ]
    },
    {
        title: "В турці",
        description: "Це один з найдавніших способів приготування кави, з якого навіть проводять чемпіонати. Дрібний помел, якісна вода, та уважність - запорука смачної кави звареної в турці. Найкраще використовувати турку з міді або кераміки, а для формування кавової шапки необхідно щоб діаметр шийки був на 15-20% вужче ніж основа.",
        steps: [
            "Каву та інші інгредієнти за бажанням, такі як цукор або спеції слід всипати в турку, та влити половину води кімнатної температури.",
            "Долийте залишок води, рівень якої не повинен перевищувати місце звуження, щоб під час нагрівання піна не перейшла через край.",
            "Турку встановіть на плиту на найменший вогонь.",
            "Через хвилину каву слід перемішати один раз, щоб вміст був рівномірним. Кава не повинна закипіти, як тільки піна почне підійматися, час знімати турку з вогню.",
            "Перелийте каву в підігріту чашку, та зачекайте 2-3 хвилини, щоб гуща осіла. Смачного!"
        ]
    },
    {
        title: "Френч-прес",
        description: "Френч-прес було винайдено ще в 1852 році, і відтоді він не втрачає своєї популярності в приготуванні кави. Заварювання кави в ньому не складний процес, але щоб результат був найкращим слід обирати свіжообсмажену каву, тобто коли від моменту обжарювання пройшло не більше 3 місяців. Кава має бути рівномірно змелена і помол має бути досить дрібним, а вода, для заварювання має бути очищеною з мінералізацією від 50 до 150 мг/л.",
        steps: [
            "Прогрійте прес гарячою водою з температурою близько 70 °C.",
            "Насипте в колбу змелену каву з розрахунку 8-9 г на 100 мл води.",
            "Налийте трохи води кімнатної температури та добре перемішайте.",
            "Обережно долийте гарячої води з температурою +85 до +92 °C, не поспішайте, щоб скляна колба не тріснула від різкого перепаду температур. Рівень води не повинен перевищувати верхню частину ручки преса.",
            "Перемішайте каву та закрийте кришкою, не опускаючи поршень.",
            "Зачекайте 4-7 хвилин, в залежності від того, наскільки міцну каву ви прагнете отримати. Повільно опустіть поршень з фільтром і одразу розливайте каву у чашки. Додайте цукор і насолоджуйтесь смаком свіжої кави."
        ]
    },
    {
        title: "Рожкова кавоварка",
        description: "Цей тип кавоварки користується популярністю в кав'ярнях, завдяки швидкості та простоті в роботі з нею. Але з ростом попиту на якісну каву серед споживачів почали з'являтися такі кавоварки й для домашнього використання. Свою назву вони отримали завдяки ріжку, в який і засипається свіжозмелена кава для приготування напою. Для такого типу кавоварки застосовують каву середнього та крупного помолу.",
        steps: [
            "Залийте очищену воду в спеціальну місткість для води.",
            "Мелену каву засипте в ріжок та добре спресуйте.",
            "Ріжок треба добре закріпити на його місці у кавоварці.",
            "Встановіть чашку, щоб готовий напій стікав з ріжка в неї та ввімкніть машину. Під тиском гаряча вода почне проходити скрізь змелену каву, наповнюючи ваш будинок неперевершеним ароматом.",
            "Коли характерний шум припиниться, це значить що час насолоджуватися свіжою кавою. Смачного!"
        ]
    }
];

jQuery(function ($) {
    var checkoutForm = $('form.checkout');

    // 1. loading in checkout btn
    checkoutForm.on('checkout_place_order', function () {
        var $btn = $(this).find('#place_order');
        if ($btn.length) {
            $btn.addClass('loading');
        }
        return true;
    });

    // 2. if validation error - remove loading
    $(document.body).on('checkout_error', function () {
        var $btn = checkoutForm.find('#place_order');
        $btn.removeClass('loading');
    });

    // 3.if ajax error — remove loading
    $(document).ajaxComplete(function (event, xhr, settings) {
        if (settings.url.indexOf('wc-ajax=checkout') > -1 && xhr.status !== 200) {
            checkoutForm.find('#place_order').removeClass('loading');
        }
    });

    // 4. if payment change — remove loading
    $(document.body).on('payment_method_selected', function () {
        checkoutForm.find('#place_order').removeClass('loading');
    });

    // 5. if user return back
    $(window).on('pageshow', function (event) {
        if (event.originalEvent.persisted) {
            checkoutForm.find('#place_order').removeClass('loading');
        }
    });

    // 6. remove loading after 15sec
    $(document.body).on('checkout_processing', function () {
        var $btn = checkoutForm.find('#place_order');
        setTimeout(function () {
            $btn.removeClass('loading');
        }, 15000);
    });

    // Mini cart custom button
    $(document).on('click', '.custom-fly-cart-button', function (e) {
        e.preventDefault();

        if (typeof window.woofc_show === 'function') {
            window.woofc_show();
        } else if ($('.woofc-area').length) {
            $('.woofc-area').addClass('woofc-active');
            $('body').addClass('woofc-show');
        } else if ($('.woofc-toggle-cart').length) {
            $('.woofc-toggle-cart').first().trigger('click');
        } else if ($('.woofc-count').length) {
            $('.woofc-count').first().trigger('click');
        }
    });


    // 0)if on the product page — put data-product_id from button value
    $('.single_add_to_cart_button').each(function () {
        var $btn = $(this),
            pid = parseInt($btn.val(), 10);
        if (pid && !$btn.data('product_id')) {
            $btn.attr('data-product_id', pid);
        }
    });

    // 1) Synchronization function
    function syncAddedButtons() {
        $.ajax({
            url: wpc_fly_cart_vars.ajax_url,
            type: 'POST',
            data: {action: 'get_cart_items'},
            success: function (items) {
                // объединённый селектор для всех типов кнопок
                $('.add_to_cart_button, .open-grind-popup, .single_add_to_cart_button').each(function () {
                    var $btn = $(this),
                        pid = parseInt($btn.data('product_id'), 10);

                    if (items.indexOf(pid) !== -1) {
                        $btn.addClass('added');
                    } else {
                        $btn.removeClass('added');
                    }
                    $btn.removeClass('loading');
                });
            }
        });
    }

    // 2) Initial launch after page load
    syncAddedButtons();

    // 3) On any updates of cart fragments add / remove / clicks
    $(document.body).on(
        'wc_fragments_refreshed added_to_cart removed_from_cart updated_cart_totals',
        syncAddedButtons
    );

    // 4) When clicking “delete” from the mini-cart, we immediately clean the classes of all buttons with this ID
    $(document).on('click', '.woocommerce-mini-cart__remove, .widget_shopping_cart_content .remove', function () {
        var match = $(this).closest('li.woocommerce-mini-cart-item')
                .attr('class')
                .match(/product-(\d+)/),
            pid = match ? match[1] : null;
        if (pid) {
            $('.add_to_cart_button[data-product_id="' + pid + '"], ' +
                '.open-grind-popup[data-product_id="' + pid + '"], ' +
                '.single_add_to_cart_button[data-product_id="' + pid + '"]')
                .removeClass('added loading');
        }
    });

    // 5) When you click “add” you immediately put loading
    $(document).on('click', '.add_to_cart_button, .open-grind-popup, .single_add_to_cart_button', function () {
        $(this).addClass('loading');
    });
});