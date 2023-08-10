<?php

namespace javadle\updater\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use javadle\updater\Helpers\UpdateHelper;
use Symfony\Component\Console\Input\InputArgument;

class CommandUpdate extends Command
{
    /**
     * Имя консольной команды.
     *
     * @var string
     */
    protected $name = 'updater:update';

    /**
     * Описание консольной команды.
     *
     * @var string
     */
    protected $description = 'Update your application using updater console command.';

    /**
     * Выполнение консольной команды.
     */
    public function handle(): int
    {
        $updateHelper = new UpdateHelper();
        $updateHelper->update();
        return 0;
    }

    /**
     * Получение аргументов консольной команды.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }
}
