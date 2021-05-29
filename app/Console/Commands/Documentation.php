<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class Documentation
 * @package App\Console\Commands
 */
class Documentation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'doc:api {--file=} {--path=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate docs of API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        exec('./vendor/bin/openapi --bootstrap ./routes/api.php --output ./swagger.yaml ./routes/api.php ./app');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['file', null, InputOption::VALUE_OPTIONAL, 'Get name to file generated.'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'Get path to read docs.'],
        ];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
        ];
    }
}
