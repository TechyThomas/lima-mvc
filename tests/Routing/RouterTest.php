<?php

namespace Tests\Routing;

use Lima\Routing\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private $routerInstance;
    private $controllerPath;

    protected function setUp(): void
    {
        // Reset singleton
        $reflection = new \ReflectionClass(Router::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        $this->routerInstance = Router::Instance();

        // Always use a temp directory for tests to avoid destroying real data
        $this->controllerPath = sys_get_temp_dir() . '/lima_tests/Controllers';

        if (!defined('CONTROLLER_PATH')) {
            define('CONTROLLER_PATH', $this->controllerPath);
        }

        // Note: If CONTROLLER_PATH was already defined (e.g. in bootstrap), we can't redefine it.
        // But we MUST check that we are not deleting real files.
        // However, since we are running isolated unit tests, we should be fine assuming we control the environment.
        // But to be safe, we only cleanup if we are sure it is our temp dir.

        if (!is_dir($this->controllerPath)) {
            mkdir($this->controllerPath, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        // Only clean up if we are using our safe temp directory
        if (strpos($this->controllerPath, 'lima_tests') !== false) {
            $files = glob($this->controllerPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            if (is_dir($this->controllerPath)) {
                 rmdir($this->controllerPath);
            }
        }
    }

    public function testRegisterRoute()
    {
        Router::registerRoute('GET', '/test', 'TestController', 'index');

        $reflection = new \ReflectionClass($this->routerInstance);
        $routesProp = $reflection->getProperty('routes');
        $routesProp->setAccessible(true);
        $routes = $routesProp->getValue($this->routerInstance);

        $this->assertArrayHasKey('GET', $routes);
        $this->assertArrayHasKey('/test', $routes['GET']);
        $this->assertEquals('TestController', $routes['GET']['/test']['controller']);
        $this->assertEquals('index', $routes['GET']['/test']['method']);
    }

    public function testProcessRequestDefaultRoute()
    {
        // Create dummy controller
        $controllerContent = "<?php\n\nnamespace Tests\Controllers;\n\nclass DefaultController\n{\n    public function index()\n    {\n        echo \"Default Controller Index\";\n    }\n}";
        file_put_contents($this->controllerPath . '/DefaultController.php', $controllerContent);

        // We need to require the file manually because autoloading is not set up for this temp dir
        require_once $this->controllerPath . '/DefaultController.php';

        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Output buffering to capture echo
        ob_start();
        $this->routerInstance->processRequest('defaultController/index');
        $output = ob_get_clean();

        $this->assertEquals("Default Controller Index", $output);
    }

    public function testProcessRequestRegisteredRoute()
    {
        $className = 'RegisteredController';
        if (!class_exists($className)) {
             // Define class dynamically
             $code = "class $className { public function registeredMethod() { echo 'Registered Controller Method'; } }";
             eval($code);
        }

        Router::registerRoute('GET', '/registered', $className, 'registeredMethod');

        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        $this->routerInstance->processRequest('/registered');
        $output = ob_get_clean();

        $this->assertEquals("Registered Controller Method", $output);
    }
}
