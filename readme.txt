=== Woo to IIko ===
Contributors: bo3gyx
Tags: woocommerce, iiko
Donate link: https://tochka.com/my/rwsite
Requires at least: 4.6
Tested up to: 5.3
Requires PHP: 5.6+
Stable tag: master
License: GPL v3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Woocommerce and Iiko integration

== Description ==
Woocommerce and IikoDelivery Api integration. Import products and export orders.

== Installation ==
Installation instruction: https://woo-to-iiko.rwsite.ru/docs/installation/

== Frequently Asked Questions ==
FAQ this plugin: https://woo-to-iiko.rwsite.ru/docs/nastrojka/

== Screenshots ==
1. https://i.imgur.com/GlriRHW.png
2. https://i.imgur.com/GCDphMp.png
3. https://i.imgur.com/hCOFvA8.png

== Changelog ==

v.0.1.0.0
 - Исправлена работа платежных шлюзов и передача способа оплаты
 - Сделана интеграция со способами доставки WooCommerce
 - Незначительные исправления

v.0.0.9.9
 - Исправлены баги
 - Улучшены функции обработки ошибок
 - Улучшен дизайн страницы импорта
 - В email и на страницу оплаты добвлен вывод способра оплаты и доставки
 - Добавлены шорткоды: [terminal_worktime], [terminal_address], [terminal_name], [terminal_rest_name]

v.0.9
 - Исправлен импорт товаров, заданы доп. настройки:
 - Настройка выбора типов товара для импорта
 - Настройка \"пропускать изображения\" при импорте
 - Импортируются все данные товара (калории и т.д.) импорт идет в мета. поля
 - Время проверки выгрузки по крону - 1 час
 - Исправлено удаление товаров
 - Исправлено поведение видимости товара
 - Исправлено поведение видимости товара для терминала
 - Исправлена ошибка валидации времени доставки на странице оплаты

v 0.0.8
 - Multisite base version.
 - Исправлен менеджер обновлений
 - Добавлен менеджер лицензий
 - Добавлены строки для перевода

v 0.0.7
 - Исправлены ошибки при выводе виджета в админ-панели
 - Исправлены ошибки импорта категорий товаров.
 - Исправлены ошибки совместимости со стандартными функциями доставки Woocommerce
 - При импорте категорий теперь импортируются все данные (в т.ч. seo)
Форма оформления заказа:
 - Добавлена jQuery валидация для формы оплаты!
 - Все не используемые при выборе поля скрываются.
 - Добавлена возможность выбора терминала доставки пользователем, при оформлении заказа.
Экспорт
 - При неудаче статус заказа обновляется на \"Не удался\", пользователю приходит email оповещение.

== Upgrade Notice ==
Please check all options after update this plugin.