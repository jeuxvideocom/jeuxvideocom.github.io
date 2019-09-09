<?php

use Illuminate\Support\Facades\Artisan as Artisan;
use Illuminate\Support\Facades\Mail;

abstract class BrowserKitTestCase extends Laravel\BrowserKitTesting\TestCase
{

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';
    protected static $db_inited = false;

    public function setUp()
    {
        parent::setUp();

        if (!static::$db_inited) {
            //on reset la bdd avant les tests, une seul fois
            static::$db_inited = true;
            Artisan::call('migrate:refresh');
            Artisan::call('db:seed');
        }
        Mail::fake();
    }

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}
