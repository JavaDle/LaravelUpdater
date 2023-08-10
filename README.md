# Updater [самостоятельное обновление для вашего приложения Laravel]

Updater позволяет вашему приложению Laravel автоматически обновляться! ;)

Когда вы выпускаете приложение, очень важно поддерживать его; поэтому может быть необходимо опубликовать обновление для исправления ошибок, а также для реализации новых функций.

Вы развертываете свое приложение для нескольких пользователей:

БЕЗ Updater => Вы хотите связаться с ними по одному и отправить им обновление по электронной почте или по ссылке? ... ммм ... очень плохо, потому что каждый пользователь (с ролью администратора) должен вручную перезаписывать все файлы в своем развертывании; или вам нужно вручную получить доступ ко всем развертываниям (например, с помощью FTP) и установить для них обновление.

#### C Updater => Пусть ваше приложение (ОДНО) определяет, что доступно новое обновление, и уведомляет администратора о его наличии; более того, пусть ваше приложение установит его и выполнит все соответствующие шаги.

### НОВАЯ ВЕРСИЯ журнал изменений

- Теперь он может запускаться из команды artisan
- Теперь поддерживает Laravel 8 и 9;
- Новый вид на основе Bootstrap 5;
- Исправление ошибок.

**3-2023**

- Поддержка laravel  10
- Поддержка ^php 8.0
- break: переместите функцию main() в upgrade.php и используйте функции beforeUpdate() и afterUpdate().

## Функции:

#### > Самостоятельное обновление

Updater позволяет вашему приложению Laravel обновляться самостоятельно :)
Пусть ваше приложение (ОДНО) обнаруживает, что доступно новое обновление, и уведомляет о его наличии администратора; более того, пусть ваше приложение установит его и выполнит все соответствующие шаги.

#### > Режим обслуживания

LaraUpdate активирует режим обслуживания (используя собственную команду Laravel) с момента запуска обновления до его успешного завершения.

#### > Безопасность

Вы можете указать, какие пользователи (например, только администратор) могут выполнять обновление для приложения; этот параметр хранится в `config/laraupdater.php`, поэтому каждое приложение может устанавливать своих пользователей независимо. Кроме того, LaraUpdater совместим с Laravel-Auth.

#### > Отказоустойчивый

Во время обновления Update СОЗДАЕТ РЕЗЕРВНЫЕ КОПИИ всех перезаписываемых файлов, поэтому в случае ошибки может попытаться автоматически восстановить предыдущее состояние. Если восстановление не удалось, вы можете использовать резервную копию, хранящуюся в корне вашей системы, для ручного обслуживания.

#### > Поддерживает скрипт PHP

Update может импортировать скрипт PHP для выполнения пользовательских действий (например, создать таблицу в базе данных после обновления); команды выполняются на последнем шаге обновления.

#### > Интегрированное резервное копирование/восстановление

#### > Многоязычный

#### > Доступ из веб-интерфейса или консоли

## Начиная

Эти инструкции помогут вам запустить копию проекта на вашем сервере для целей разработки и тестирования.
### Prerequisites

Updater был протестирован с использованием Laravel 8/9
Рекомендуемая версия Laravel >= 8

## Installing

Этот пакет можно установить через Composer:

```sh
composer require javadle/updater
```

После установки необходимо выполнить следующие действия:

#### 1) add the service provider in `config/app.php` file:

```
'providers' => [
    // ...
    pcinaglia\updater\UpdaterServiceProvider::class,
];
```

#### 2) publish Updater in your app

Этот шаг скопирует файл конфигурации в папку конфигурации вашего приложения Laravel.

```sh
php artisan vendor:publish --provider="javadle\updater\UpdaterServiceProvider"
```

Когда он будет опубликован, вы сможете управлять конфигурацией Updater через файл в `config/updater.php`, он содержит:
```
    /*
    * Временная папка для хранения обновлений перед их установкой.
    */
    'tmp_folder_name' => 'tmp',

    /*
    * Имя файла скрипта, вызываемого при обновлении.
    */
    'script_filename' => 'upgrade.php',

    /*
    * URL-адрес, где хранятся ваши обновления (например, для папки с именем «обновления» по адресу https://site.com/yourapp).
    */
    'update_baseurl' => 'http://localhost:8888/update',

    /*
     * Установите промежуточное ПО для маршрута: updater.update
     * НЕ работает только 'auth' (управление безопасностью с помощью конфигурации 'allow_users_id')
     */
    'middleware' => ['web', 'auth'],

    /*
     * Установите, какие пользователи могут выполнять обновление;
     * Этот параметр принимает: ARRAY(user_id) или FALSE => например: [1] OR [1,3,0] OR false
     * Как правило, у ADMIN user_id=1; установите FALSE, чтобы отключить эту проверку (не рекомендуется)
     */
    'allow_users_id' => [1]
```

#### 3) Создать версию.txt

Чтобы сохранить текущую версию вашего приложения, вам нужно создать текстовый файл с именем `version.txt` и скопировать его в основную папку вашего приложения Laravel.
Например, создайте файл .txt, содержащий только:

```
1.0
```

Используйте только 1 строку, первую, в файле .txt.
При выпуске обновления эти файлы обновляются из LaraUpdate.

## Создайте свой «репозиторий» обновлений

#### 1) Создать архив

Создайте ZIP-архив со всеми файлами, которые вы хотите заменить во время обновления (используйте ту же структуру вашего приложения, чтобы упорядочить файлы в архиве).

#### 1.1) Сценарий обновления (необязательно)

Вы можете создать файл PHP с именем `upgrade.php` для выполнения пользовательских действий (например, создать новую таблицу в базе данных).
Этот файл должен содержать функцию с именами `beforeUpdate()` и `afterUpdate()` с логическим возвратом (чтобы передать статус ее выполнения в Updater), см. этот пример:
```php
<?php

function beforeUpdate(): bool
{
    Artisan::call('backup::db');
    return true;
}


function afterUpdate(): bool
{
    Artisan::call('migrate --force');
    Artisan::call('db::seed');
    Artisan::call('module::seed');

    return true;
}

?>
```

Обратите внимание, что приведенный выше пример не обрабатывает никаких исключений, поэтому статус его выполнения всегда возвращает true (не рекомендуется).

#### 2) Установите метаданные для вашего обновления:

Создайте файл с именем updater.json следующим образом:

```json
{
  "version": "1.0.1",
  "archive": "RELEASE-1.01.zip",
  "description": "Added Blogs"
}
```

`archive` содержит имя архива .zip (см. шаг 1).

#### 3) Загрузите свое обновление

Загрузите `updater.json` и .zip-архив в ту же папку на вашем сервере (та, на которой будет размещено обновление).

#### 4) Настройте свое приложение

Укажите сервер, на котором будет размещено обновление, в `config/updater.php` (см. Установка):

Например, если вы загружаете файлы в:

     http://yoursites.com/updatesformyapp/RELEASE-1.02.zip
     и http://yoursites.com/updatesformyapp/updater.json.

установите `'update_baseurl'` следующим образом: `'update_baseurl' => 'http://yoursites.com/updatesformyapp',`

## Использование

Updater реализует три основных метода, которые вы можете вызвать с помощью веб-маршрутов или команды artisan:

#### updater.check, updater:check

Возвращает '' (обновление не существует) ИЛИ $version (например, 1.0.1, если обновление существует).

#### updater.currentVersion, updater:current-version

Возвращает текущую версию вашего приложения (из `version.txt`).

#### updater.update, updater:update

Он загружает и устанавливает последнее доступное обновление.
Этот веб-маршрут защищен с помощью информации из `'allow_users_id'` в `config/updater.php`

Я предлагаю не использовать эти маршруты напрямую, НО показывать оповещение, когда доступно обновление; Предупреждение может содержать кнопку для выполнения обновления, см. решение ниже:

### Всплывающее окно с уведомлением с использованием Bootstrap 5 и JQuery (в комплекте)

![alt text](readme files/preview 1.png «Предупреждение с кнопкой обновления»)

Добавьте в `resources/view/layout/app.blade.php` этот код, чтобы загрузить представление, включенное в Updater (я предлагаю сразу перед `@yield('content')`):
```
@include('vendor.updater.notification')
```

ТЕСТ: опубликуйте обновление и обновите страницу, чтобы показать оповещение.


## Author

- **Pietro Cinaglia** - Автор оригинала - Свяжитесь с помощью [GitHub](https://github.com/pietrocinaglia) или [LinkedIn](https://linkedin.com/in/pietrocinaglia)
### Contributors

- **Aura Komputer** - Contact using [GitHub](https://github.com/aurakomputer)


## Лицензия

Этот проект находится под лицензией MIT License — подробности см. в файле [LICENSE](LICENSE).

(Лицензия MIT — информация о гарантии) ПРОГРАММНОЕ ОБЕСПЕЧЕНИЕ ПРЕДОСТАВЛЯЕТСЯ «КАК ЕСТЬ», БЕЗ КАКИХ-ЛИБО ГАРАНТИЙ, ЯВНЫХ ИЛИ ПОДРАЗУМЕВАЕМЫХ, ВКЛЮЧАЯ, ПОМИМО ПРОЧЕГО, ГАРАНТИИ КОММЕРЧЕСКОЙ ПРИГОДНОСТИ, ПРИГОДНОСТИ ДЛЯ ОПРЕДЕЛЕННОЙ ЦЕЛИ И НЕНАРУШЕНИЯ ПРАВ. НИ ПРИ КАКИХ ОБСТОЯТЕЛЬСТВАХ АВТОРЫ ИЛИ ОБЛАДАТЕЛИ АВТОРСКИМ ПРАВОМ НЕ НЕСУТ ОТВЕТСТВЕННОСТИ ЗА ЛЮБЫЕ ПРЕТЕНЗИИ, УЩЕРБ ИЛИ ИНУЮ ОТВЕТСТВЕННОСТЬ, БУДУТ СВЯЗАННЫЕ С ДОГОВОРОМ, ДЕЛОМ ИЛИ ИНЫМ ОБРАЗОМ, ВОЗНИКАЮЩИЕ ИЗ ПРОГРАММНОГО ОБЕСПЕЧЕНИЯ ИЛИ ИСПОЛЬЗОВАНИЯ ИЛИ ИНЫХ СДЕЛОК В СВЯЗИ С ПРОГРАММНЫМ ОБЕСПЕЧЕНИЕМ ИЛИ ИСПОЛЬЗОВАНИЕМ ПРОГРАММНОЕ ОБЕСПЕЧЕНИЕ.