### Установка

Добавить в `tailwind.admin.config.js`, созданный в пакете `tailwindcss-theme`.

    "./vendor/4geo35/geo-news-parser/src/resources/views/components/**/*.blade.php",
    "./vendor/4geo35/geo-news-parser/src/resources/views/admin/**/*.blade.php",
    "./vendor/4geo35/geo-news-parser/src/resources/views/livewire/admin/**/*.blade.php",

Запустить миграции для создания таблиц `php artisan migrate`

#### Views

Сокращение для представлений: `gnp`  

#### Config

Название файла: `geo-news-parser`

- `disallowImages` => `false`: не искать изображения

#### Routes

Файл `admin.php`, `middleware(["web", "auth", "app-management"])`

- `admin.geo-news-parser.index` => `admin/geo-news-parser`
