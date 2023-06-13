<?php
/**
 * An abstract controller that all ordinary CPMS controllers
 *
 * @package     olcscommon
 * @subpackage  controller
 * @author      Pele Odiase <pele.odiase@valtech.co.uk>
 */

namespace PaymentTest\Controller;

use Laminas\Http\Header\ContentType;
use Laminas\Http\Headers;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase as ZendTestCase;

/**
 * Class AbstractHttpAppControllerTestCase
 * Abstract test case for PhpUnit testing
 *
 * @package PaymentTest\Controller
 */
abstract class AbstractHttpAppControllerTestCase extends ZendTestCase
{
    protected $clientMock = array();
    protected $configDir = '/../../../../../config/test/application.config.php';

    public function setUp($noConfig = false)
    {
        if (!$noConfig) {
            $this->setApplicationConfig(include $this->configDir);
        }

        parent::setUp();

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

    }

    /**
     * Dispatch the MVC with an URL and a body
     *
     * @param  string       $url
     * @param  string       $method      HTTP Method to use
     * @param  string|array $body        The body or, if JSON, an array to encode as JSON
     * @param  string       $contentType The Content-Type HTTP header to set
     *
     * @throws \Exception
     */
    public function dispatchBody($url, $method, $body, $contentType = 'application/json')
    {
        if (!is_string($body) && $contentType == 'application/json') {
            $body = json_encode($body);
        }

        $this->url($url, $method);

        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        $request->setContent($body);

        $headers = new Headers();
        $headers->addHeader(ContentType::fromString('Content-Type: ' . $contentType));
        $request->setHeaders($headers);

        $this->getApplication()->run();

        if (true !== $this->traceError) {
            return;
        }
        /** @var \Laminas\Mvc\Application $app */
        $app       = $this->getApplication();
        $exception = $app->getMvcEvent()->getParam('exception');
        if ($exception instanceof \Exception) {
            throw $exception;
        }
    }
}
