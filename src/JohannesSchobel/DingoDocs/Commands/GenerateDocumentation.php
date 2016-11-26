<?php

namespace JohannesSchobel\DingoDocs\Commands;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Illuminate\Console\Command;
use JohannesSchobel\DingoDocs\Models\Endpoint;
use JohannesSchobel\DingoDocs\Parsers\AnnotationParser;

class GenerateDocumentation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dingodocs:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate your API documentation from existing Laravel or Dingo/API routes.';

    /**
     * reader to get the annotations and map to respective classes
     *
     * @var SimpleAnnotationReader|null
     */
    protected $reader = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        /*
         * das ist ein test ob es klappt
         * bla
         */

        $this->reader = new SimpleAnnotationReader();
        $this->reader->addNamespace('JohannesSchobel\\DingoDocs\\Models\\Annotations');

        AnnotationRegistry::registerLoader(function ($classfile) {
            $path = str_replace(['JohannesSchobel\\DingoDocs\\Commands', '\\'], ['', DIRECTORY_SEPARATOR], __DIR__) . $classfile . '.php';

            if (file_exists($path)) {
                require_once $path;
                return true;
            }
        });
    }

    /**
     * Execute the console command.
     *
     * @return boolean
     */
    public function handle()
    {
        $versions = $this->getVersions();

        if(!is_array($versions) || empty($versions)) {
            $this->error('You must provide versions to generate dingodocs.');
            return false;
        }

        $outputFolder = config('dingodocs.outputpath');
        $this->createFolder($outputFolder);

        foreach($versions as $version) {
            $routes = $this->processDingoRoutes($version);
            $this->generatePage($version, $routes);
        }

        return true;
    }

    private function generatePage($version, $routes) {
        $outputFolder = config('dingodocs.outputpath');
        $outputFile = $outputFolder . $version . '.html';

        $page = view('dingodocs::page')
            ->with('version', $version)
            ->with('routes', $routes)
            ->render();

        file_put_contents($outputFile, $page);
    }

    /**
     * Get the user-config for the versions (or default)
     *
     * @return array
     */
    private function getVersions() {
        return config('dingodocs.versions');
    }

    /**
     * Gets all Dingo Routes for the specified version
     *
     * @param string $version
     */
    private function getDingoRoutes($version) {
        return app('Dingo\Api\Routing\Router')->getRoutes()[$version];
    }

    private function processDingoRoutes($version) {
        $routes = $this->getDingoRoutes($version);

        $result = [];

        foreach($routes as $route) {

            $endpoint = new Endpoint($this->reader, $route);

            if($endpoint->getTransient() != null) {
                dingodocs_msg('I', $route, 'is skipped because of @Transient annotation.');
                continue;
            }

            $result[$endpoint->getGroup()][] = $endpoint;
        }

        return $result;
    }

    /**
     * Create a new DingoDocs documentation folder and copy all needed files
     *
     * @param $folder
     */
    private function createFolder($folder)
    {
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
            mkdir($folder . '/css');
            mkdir($folder . '/fonts');
            mkdir($folder . '/js');
        }

        // copy resources from package
        dingodocs_xcopy(__DIR__ . '/../../../resources/assets/css/',       $folder . '/css');
        dingodocs_xcopy(__DIR__ . '/../../../resources/assets/fonts/',     $folder . '/fonts');
        dingodocs_xcopy(__DIR__ . '/../../../resources/assets/js/',        $folder . '/js');

        // TODO: overwrite these files with custom files from the users directory
    }

}
