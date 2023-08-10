<?php

namespace javadle\updater\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use javadle\updater\Helpers\UpdateHelper;
use Symfony\Component\Console\Input\InputArgument;

class CommandCurrentVersion extends Command
{
    /**
     * Имя консольной команды.
     *
     * @var string
     */
    protected $name = 'updater:current-version';

    /**
     * Описание консольной команды.
     *
     * @var string
     */
    protected $description = "Current version ('version.txt' in main folder) using updater.";

    /**
     * Выполнение консольной команды.
     */
    public function handle(): int
    {
        $updateHelper = new UpdateHelper();
        $currentVersion = $updateHelper->getCurrentVersion();
        $this->info($currentVersion);
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
