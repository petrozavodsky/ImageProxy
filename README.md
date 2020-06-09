## Установка и активация плагина

1. Поместите каталог `ImageProxy` из этого репозитория в каталог `/wp-content/plugin/` вашего сайта любым доступным вам способом.
2. Страница настроек плагина находится по адресу https://site.ru/wp-admin/options-general.php?page=image-proxy-option (место site.ru подставьте свой домен).
3. Вставьте имеющиеся у вас KEY и SALT в соответствующие поля переведите селектбокс в положение `On` и  нажмите кнопку сохранения.

В значения полей KEY и SALT нужно получить на этапе установки бекенда плагина, установка описана [тут](https://github.com/petrozavodsky/ImageProxy-config/blob/master/README.md).

Удалить неиспользуемые размеры изображений можно при помощи плагина WP-CLI командой: `wp media regenerate --yes` при условии что плагин включен и активен. Этой же командой можно сгенерировать их заново если предварительно выключить плагин.

Вместо WP-CLI тоже самое можно сделать с помощью плагина [Regenerate Thumbnails](https://wordpress.org/plugins/regenerate-thumbnails/)

Остались вопросы? [Готов ответить в telegram](https://github.com/petrozavodsky/ImageProxy)
