<?php

namespace javadle\updater\Helpers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class UpdateHelper
{
    private ?string $tmp_backup_dir = null;
    private ?string $response_html = '';


    private function initTmpBackupDir(): void
    {
        $this->tmp_backup_dir = storage_path('app/updater') . '/backup_' . date('Ymd');
    }

    public function log($msg, $append_response = false, $type = 'info'): void
    {
        //Response HTML
        if ($append_response) {
            $this->response_html .= $msg . "<br>";
        }

        //Log
        $header = "Updater - ";
        if ($type == 'info') {
            Log::info($header . '[info]' . $msg);
        } elseif ($type == 'warn') {
            Log::error($header . '[warn]' . $msg);
        } elseif ($type == 'err') {
            Log::error($header . '[err]' . $msg);
        } else {
            return;
        }

        if (app()->runningInConsole()) {
            dump($msg);
        } else {
            echo($msg . "<br/>");
        }
    }

    /*
    * Загрузить и установить обновление.
    */
    public function update(): void
    {
        $last_version_info = $this->getLastVersion();

        if ($last_version_info['version'] <= $this->getCurrentVersion()) {
            $this->log(trans("updater.ALREADY_UPDATED"), true);
            return;
        }

        try {

            if (($last_version = $this->download($last_version_info['archive'])) === false) {
                return;
            }

            // Включение режима обслуживания
            Artisan::call('down');
            $this->log(trans("updater.MAINTENANCE_MODE_ON"), true);

            if (($this->install($last_version)) === false) {
                $this->log(trans("updater.INSTALLATION_ERROR"), true, 'err');
                return;
            }

            $this->setCurrentVersion($last_version_info['version']); //update system version
            $this->log(trans("updater.INSTALLATION_SUCCESS"), true);

            $this->log(trans("updater.SYSTEM_VERSION") . $this->getCurrentVersion(), true);

            // Выключение режима обслуживания
            Artisan::call('up');
            $this->log(trans("updater.MAINTENANCE_MODE_OFF"), true);

        } catch (\Exception $e) {

            $this->log(trans("updater.EXCEPTION") . '<small>' . $e->getMessage() . '</small>', true, 'err');
            $this->recovery();

            // Поднятие laravel после восстановления при ошибке
            Artisan::call('up');
        }
    }

    private function install($archive): bool
    {
        try {
            $execute_commands = false;
            $update_script = base_path() . '/' . config('updater.tmp_folder_name') . '/' . config('updater.script_filename');


            $zip = new \ZipArchive();
            if ($zip->open($archive)) {
                $archive = substr($archive, 0, -4);

                // проверить, существует ли update_script
                $update_script_content = $zip->getFromName(config('updater.script_filename'));

                print($update_script_content);

                if ($update_script_content) {
                    File::put($update_script, $update_script_content);

                    // включить скрипт обновления;
                    include_once $update_script;
                    $execute_commands = true;

                    // запустить функцию beforeUpdate из скрипта обновления
                    beforeUpdate();
                }

                $goods = config('updater.show_change_log_for_users') ?? [];

                if (in_array(auth()->user()->getAttribute('email'), $goods)) {
                    $this->log(trans("updater.CHANGELOG"), true);
                }


                for ($indexFile = 0; $indexFile < $zip->numFiles; $indexFile++) {
                    $filename = $zip->getNameIndex($indexFile);
                    $dirname = dirname($filename);

                    // Исключить файлы
                    if (str_ends_with($filename, '/') || dirname($filename) === $archive || str_starts_with($dirname, '__')) {
                        continue;
                    }

                    if (str_contains($filename, 'version.txt')) {
                        continue;
                    }

                    if (str_starts_with($dirname, $archive)) {
                        $dirname = substr($dirname, (strlen($dirname) - strlen($archive) - 1) * (-1));
                    }


                    //установить новый путь очистки для текущего файла
                    $filename = $dirname . '/' . basename($filename);

                    //Создать новый каталог (если он существует и в текущей версии, продолжить...)
                    if (!is_dir(base_path() . '/' . $dirname)) {
                        File::makeDirectory(base_path() . '/' . $dirname, 0755, true, true);
                        if (in_array(auth()->user()->getAttribute('email'), $goods)) {
                            $this->log(trans("updater.DIRECTORY_CREATED") . $dirname, true);
                        }
                    }

                    //Перезаписать файл его последней версией
                    if (!is_dir(base_path() . '/' . $filename)) {
                        $contents = $zip->getFromIndex($indexFile);
                        $contents = str_replace("\r\n", "\n", $contents);
                        if (File::exists(base_path() . '/' . $filename)) {
                            if (in_array(auth()->user()->getAttribute('email'), $goods)) {
                                $this->log(trans("updater.FILE_EXIST") . $filename, true);
                            }
                            //сохраняем текущую версию
                            $this->backup($filename);
                        }
                        if (in_array(auth()->user()->getAttribute('email'), $goods)) {
                            $this->log(trans("updater.FILE_COPIED") . $filename, true);
                        }

                        File::put(base_path() . '/' . $filename, $contents);
                        unset($contents);
                    }
                }

                $zip->close();

                if ($execute_commands) {
                    // upgrade.php содержит метод main() с возвратом BOOL для проверки его выполнения.
                    afterUpdate();
                    unlink($update_script);
                    $this->log(trans("updater.EXECUTE_UPDATE_SCRIPT") . ' (\'upgrade.php\')', true);
                }

                File::delete($archive);
                File::deleteDirectory($this->tmp_backup_dir);

                $this->log(trans("updater.TEMP_CLEANED"), true);
            }
        } catch (\Exception $e) {
            $this->log(trans("updater.EXCEPTION") . '<small>' . $e->getMessage() . '</small>', true, 'err');
            return false;
        }

        return true;
    }

    /*
    * Загрузка обновления из $update_baseurl в $tmp_folder_name (локальная папка).
    */
    private function download($filename): bool|string
    {
        $this->log(trans("updater.DOWNLOADING"), true);
        $tmp_folder_name = base_path() . '/' . config('updater.tmp_folder_name');

        if (!is_dir($tmp_folder_name)) {
            File::makeDirectory($tmp_folder_name, 0755, true, true);
        }

        try {

            $local_file = $tmp_folder_name . '/' . $filename;
            $remote_file_url = config('updater.update_baseurl') . '/' . $filename;

            $update = file_get_contents($remote_file_url);
            file_put_contents($local_file, $update);

        } catch (\Exception $e) {
            $this->log(trans("updater.DOWNLOADING_ERROR"), true, 'err');
            $this->log(trans("updater.EXCEPTION") . '<small>' . $e->getMessage() . '</small>', true, 'err');
            return false;
        }

        $this->log(trans("updater.DOWNLOADING_SUCCESS"), true);
        return $local_file;
    }

    /*
    * Текущая версия ('version.txt' в основной папке)
    */
    public function getCurrentVersion()
    {
        // todo: env file version
        return File::get(base_path() . '/version.txt');
    }

    private function setCurrentVersion($version): void
    {
        // todo: env file version
        File::put(base_path() . '/version.txt', $version);
    }

    /*
    * Проверить, существует ли новое обновление.
    */
    public function check()
    {
        $last_version = $this->getLastVersion();
        if (version_compare($last_version['version'], $this->getCurrentVersion(), ">")) {
            return $last_version;
        }
        return '';
    }

    private function getLastVersion()
    {
        $last_version = file_get_contents(config('updater.update_baseurl') . '/updater.json');
        return json_decode($last_version, true);
    }

    /*
    * Сделать резервную копию файлов перед выполнением обновления.
    */
    private function backup($filename): void
    {
        if (!isset($this->tmp_backup_dir)) {
            $this->initTmpBackupDir();
        }

        $backup_dir = $this->tmp_backup_dir;
        if (!is_dir($backup_dir)) {
            File::makeDirectory($backup_dir, 0755, true, true);
        }

        if (!is_dir($backup_dir . '/' . dirname($filename))) {
            File::makeDirectory($backup_dir . '/' . dirname($filename), 0755, true, true);
        }

        //сохранение в резервную папку
        File::copy(base_path() . '/' . $filename, $backup_dir . '/' . $filename);
    }

    /*
    * Система восстановления из последней резервной копии.
    */
    private function recovery(): void
    {
        $this->log(trans("updater.RECOVERY") . '<small>' . $e . '</small>', true);

        if (!isset($this->tmp_backup_dir)) {
            $this->initTmpBackupDir();
            $this->log(trans("updater.BACKUP_FOUND") . '<small>' . $this->tmp_backup_dir . '</small>', true);
        }

        try {
            $backup_dir = $this->tmp_backup_dir;
            $backup_files = File::allFiles($backup_dir);
            foreach ($backup_files as $file) {
                $filename = (string)$file;
                $filename = substr($filename, (strlen($filename) - strlen($backup_dir) - 1) * (-1));
                File::copy($backup_dir . '/' . $filename, base_path() . '/' . $filename); //to respective folder
            }
        } catch (\Exception $e) {
            $this->log(trans("updater.RECOVERY_ERROR"), true, 'err');
            $this->log(trans("updater.EXCEPTION") . '<small>' . $e->getMessage() . '</small>', true, 'err');
            return;
        }

        $this->log(trans("updater.RECOVERY_SUCCESS"), true);
    }
}
