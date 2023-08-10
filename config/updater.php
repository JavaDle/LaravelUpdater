<?php
/*
* @author: Husein JavaDLE
* https://github.com/JavaDle
*/

return [

    /*
     * Временная папка для хранения обновлений перед их установкой.
     */
    'tmp_folder_name' => 'storage/app/updater/tmp_update',

    /*
    * Имя файла скрипта, вызываемого при обновлении.
    */
    'script_filename' => 'upgrade.php',

    /*
    * URL-адрес, где хранятся ваши обновления (например, для папки с именем «обновления» по адресу http://site.com/yourapp).
    */
    'update_baseurl' => 'http://localhost:8888/update',

    /*
    * Установите промежуточное ПО для маршрута: updater.update
    * НЕ работает только «auth» (управляйте безопасностью, используя конфигурацию «allow_users_id»)
    */
    'middleware' => ['web', 'auth'],

    /*
    * Установите, какие пользователи могут выполнять обновление;
    * Этот параметр принимает: ARRAY(user_id) или FALSE => например: [1] OR [1,3,0] OR false
    * Как правило, ADMIN имеет user_id=1; установите FALSE, чтобы отключить эту проверку (не рекомендуется)
    */
    'allow_users_id' => [1]
];
