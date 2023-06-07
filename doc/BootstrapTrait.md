# Bootstrap Trait
The purpose of the BootstrapTrait file is reduce code duplication when creating PhpUnit Tests for CPMS projects. It
stream lines the bootstrap process and ensures a common bootstrap process is used across all projects.

## Usage Example
+ Add `cpms\payment-test: dev-master` to `composer.json` as a required dependency
+ Create a Bootstrap.php file in your test directory. This is the file referenced in your `phpunit.xml` configuration
file.
+ The content of your Bootstrap.php file would be as below:

        use PaymentTest\BootstrapTrait;

        require_once __DIR__ . '/../../vendor/cpms/payment-test/lib/PaymentTest/Test/BootstrapTrait.php';

        class Bootstrap
        {
           use BootstrapTrait;
        }

        $path = realpath(__DIR__ . '/../');

        chdir(dirname($path));
        Bootstrap::getInstance()->init($path);

+ Ensure that you have the `application.config.php` in the module's `config` directory
+ If your PhpUnit tests is structured as a module, you would need to pass the module's namespace as the second
argument to `Bootstrap::getInstance()->init($path);` as shown below

        Bootstrap::init($path, $testNamespace);
