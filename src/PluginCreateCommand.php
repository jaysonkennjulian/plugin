<?php

namespace Bizzle\Plugin;

use ZipArchive;
use RuntimeException;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PluginCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bizzle:create {vendor} {name} {--i}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CLI Tool for Bizzle Plugin Creation';

    /**
     * The filesystem handler.
     * @var object
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('i')) {
            $vendor = $this->ask('What will be the vendor name?', $this->argument('vendor'));
            $name = $this->ask('What will be the package name?', $this->argument('name'));
        } else {
            $vendor = $this->argument('vendor');
            $name = $this->argument('name');
        }

        $path = getcwd().'/packages/';
        $fullpath = $path.$vendor.'/'.$name;
        $sourcepath = $fullpath .'/src/';

        $requirement = '"psr-4": {
            "'.$vendor.'\\\\'.$name.'\\\\": "packages/'.$vendor.'/'.$name.'/src",';
        $appConfigLine = 'App\Providers\RouteServiceProvider::class,

        '.$vendor.'\\'.$name.'\\'.$name.'ServiceProvider::class,';

        $this->info('Creating Bizzle Plugin Template...');
        $this->checkDir($path, $vendor, $name);
        $this->makeStructure($path,$fullpath,$sourcepath,$vendor,$name);

        $newProvider = $fullpath.'/src/'.$name.'ServiceProvider.php';
        $this->replacer(__DIR__.'/ServiceProvider.stub', ['{{vendor}}', '{{name}}'], [$vendor, $name], $newProvider);
        $newComposer = $fullpath.'/composer.json';
        $search =   [
                ':vendor\\\\:package_name\\\\',
                ':vendor/:package_name',
                ':vendor\\\\:package_name\\\\',
                ':vendor\\\\:package_name\\\\Test\\\\'
            ];
        $replace =  [
            $vendor.'\\\\'.$name.'\\\\',
            $vendor.'/'.$name,
            $vendor.'\\\\'.$name.'\\\\',
            $vendor.'\\\\'.$name.'\\\\Test\\\\'
        ];
        $this->replacer(__DIR__.'/composer.stub',$search,$replace,$newComposer);
        $newConfig = $fullpath.'/config/config.php';
        $this->replacer(__DIR__.'/config.stub',[':vendor',':package_name'],[$vendor, $name],$newConfig);
        
        $this->replacer(getcwd().'/composer.json', '"psr-4": {', $requirement);
        $this->replacer(getcwd().'/config/app.php', 'App\Providers\RouteServiceProvider::class,', $appConfigLine);

        $this->info('Bizzle Plugin Template Successfully Created!');
        $this->output->newLine(2);
        $this->dumpAutoloads();
    }

    public function checkDir($path, $vendor, $name)
    {
        if (is_dir($path.$vendor.'/'.$name)) {
            throw new RuntimeException('Bizzle Plugin already exists');
        }
    }

    public function makeDir($path)
    {
        if (!is_dir($path)) {
            return mkdir($path, 0777, true);
        }
    }

    public function makeStructure($path,$fullpath,$sourcepath,$vendor,$name)
    {
        $this->makeDir($path);
        $this->makeDir($path.$vendor);
        $this->makeDir($fullpath);
        $this->makeDir($sourcepath);
        $this->makeDir($fullpath.'/public');
        $this->makeDir($fullpath.'/public/assets');
        $this->makeDir($fullpath.'/public/assets/css');
        $this->makeDir($fullpath.'/public/assets/js');
        $this->makeDir($fullpath.'/public/assets/images');
        $this->makeDir($fullpath.'/database');
        $this->makeDir($fullpath.'/database/migrations');
        $this->makeDir($fullpath.'/database/seeds');
        $this->makeDir($fullpath.'/config');
        $this->makeDir($sourcepath.'Routes');
        $this->makeDir($sourcepath.'Models');
        $this->makeDir($sourcepath.'Views');
        $this->makeDir($sourcepath.'Controllers');
    }

    public function replacer($oldFile, $search, $replace, $newFile = null)
    {
        $newFile = ($newFile == null) ? $oldFile : $newFile;
        $file = $this->files->get($oldFile);
        $replacing = str_replace($search, $replace, $file);
        $this->files->put($newFile, $replacing);
    }

    public function dumpAutoloads()
    {
        shell_exec('composer dump-autoload');
    }
}