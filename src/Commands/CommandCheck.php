<?php

namespace javadle\updater\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use javadle\updater\Helpers\UpdateHelper;
use Symfony\Component\Console\Input\InputArgument;

class CommandCheck extends Command
{
    /**
     * Имя консольной команды.
     *
     * @var string
     */
    protected $name = 'updater:check';

    /**
     * Описание консольной команды.
     *
     * @var string
     */
    protected $description = 'Check if a new Update exist using updater.';

    /**
     * Выполнение консольной команды.
     */
    public function handle(): int
    {
        $updateHelper = new UpdateHelper();
        $check = $updateHelper->check();
        $this->info(json_encode($check));
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
