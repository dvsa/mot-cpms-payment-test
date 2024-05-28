<?php
namespace PaymentTest\Controller;

use Laminas\Http\Header\ContentType;
use Laminas\Http\Headers;
use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\Http\PhpEnvironment\Response as HttpResponse;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Router\Http\TreeRouteStack as HttpRouter;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Uri\Http;

/**
 * Class ControllerSetupTrait
 *
 * @package PaymentTest
 */
trait ControllerSetupTrait
{
    /** @var AbstractController */
    protected $controller;
    /** @var  MvcEvent */
    protected $event;
    /** @var  Routematch */
    protected $routeMatch;
    /** @var  HttpRouter */
    protected $router;
    /** @var ServiceManager */
    protected $serviceManager;

    /**
     * @param ServiceManager     $serviceManager
     * @param AbstractController $controller
     * @param array              $plugins
     */
    public function setupController(
        ServiceManager $serviceManager,
        AbstractController $controller,
        array $routeMatchParams,
        $plugins = [],
        $requestUri = 'http://example.com'
    ) {
        /** @var HttpRequest $request */
        /** @var AbstractHttpAppControllerTestCase | ControllerSetupTrait $this */
        /** @var TreeRouteStack $router */

        $this->controller = $controller;
        $this->routeMatch = new RouteMatch($routeMatchParams);
        $this->event      = new MvcEvent();
        $this->event->setResponse(new HttpResponse());

        $config       = $serviceManager->get('config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router       = HttpRouter::factory($routerConfig);
        $router->setRequestUri(new Http($requestUri));

        $this->router = $router;
        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);

        $this->serviceManager = $serviceManager;
        $pluginManager        = new PluginManager($serviceManager);
        /**
         * @var                $name
         * @var AbstractPlugin $class
         */
        foreach ($plugins as $name => $class) {
            $pluginManager->setService($name, $class);
            $class->setController($controller);
        }

        $this->controller->setPluginManager($pluginManager);
        $this->controller->setEvent($this->event);
	// setServiceLocator is not required as the ServiceLocator should be a controller plugin
        // $this->controller->setServiceLocator($serviceManager);

        $request = $this->getRequest();
        if ($request instanceof HttpRequest) {
            $headers = new Headers();
            $headers->addHeader(ContentType::fromString('Content-Type: application/json'));
            $headers->addHeaderLine('Accept: application/json');
            $request->setHeaders($headers);
        }
        /** @var \Laminas\View\Helper\Url $url */
        $url = $serviceManager->get('ViewHelperManager')->get('url');
        $url->setRouter($router);
    }
}
