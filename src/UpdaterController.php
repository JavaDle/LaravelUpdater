<?php
/*
* @author: Husein JavaDLE
* https://github.com/JavaDle
*/

namespace javadle\updater;

use Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use javadle\updater\Helpers\UpdateHelper;

class UpdaterController extends Controller
{
    private function checkPermission(): bool
    {
        if (config('updater.allow_users_id') !== null) {
            if (config('updater.allow_users_id') === false || in_array(Auth::User()->id, config('updater.allow_users_id')) === true) {
                return true;
            }
        }
        return false;
    }


    /*
    * Загрузить и установить обновление.
    */
    public function update()
    {
        $updateHelper = new UpdateHelper();
        $updateHelper->log(trans("updater.SYSTEM_VERSION") . $this->getCurrentVersion(), true, 'info');
        if (!$this->checkPermission()) {
            $updateHelper->log(trans("updater.PERMISSION_DENIED."), true, 'warn');
            return;
        }
        return $updateHelper->update();
    }

    /*
    * Проверка, существует ли новое обновление.
    */
    public function check()
    {
        $updateHelper = new UpdateHelper();
        return $updateHelper->check();
    }

    /*
    * Текущая версия ('version.txt' в основной папке)
    */
    public function getCurrentVersion()
    {
        // todo: env file version
        $updateHelper = new UpdateHelper();
        return $updateHelper->getCurrentVersion();
    }
}
