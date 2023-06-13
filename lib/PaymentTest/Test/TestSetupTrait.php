<?php
namespace PaymentTest\Test;

use CpmsCommonTest\Bootstrap;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaTool;
use PaymentTest\Controller\SampleController;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\TreeRouteStack as HttpRouter;
use Laminas\Router\RouteMatch;

trait TestSetupTrait
{
    /** @var */
    public static $bootstrap;

    /** @var \Laminas\ServiceManager\ServiceManager */
    protected $serviceManager;

    /** @var \Laminas\Mvc\Application */
    protected $app;

    /** @var  \Laminas\Mvc\Controller\AbstractController */
    protected $controller;

    /** @var \Laminas\Http\PhpEnvironment\Request $request */
    protected $request;

    protected $response;

    /** @var  RouteMatch */
    protected $routeMatch;

    /** @var  \Laminas\Mvc\MvcEvent */
    protected $event;

    protected $clientMock = array();

    public function setUp()
    {
        if (empty($this->serviceManager)) {
            $this->serviceManager = Bootstrap::getInstance()->getServiceManager();
        }

        if (empty($this->controller)) {
            $this->controller = new SampleController();
        }

        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array());
        $this->event      = new MvcEvent();
        $config           = $this->serviceManager->get('Config');
        $routerConfig     = isset($config['router']) ? $config['router'] : array();
        $router           = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator($this->serviceManager);
        $this->serviceManager->setAllowOverride(true);
    }

    /**
     * Setup test data
     *
     * @param $fixturePath
     */
    public function setUpDatabase($fixturePath)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->serviceManager->get('doctrine.entitymanager.orm_default');

        $entityManager->clear();

        $tool    = new SchemaTool($entityManager);
        $classes = $entityManager->getMetadataFactory()->getAllMetadata();

        $dropSchemaSql = $tool->getDropDatabaseSQL();
        $conn          = $entityManager->getConnection();

        while ($sql = array_shift($dropSchemaSql)) {
            try {
                $conn->executeQuery($sql);
            } catch (\Exception $exception) {
                $dropSchemaSql[] = $sql;
            }
        }

        $tool->createSchema($classes);

        $loader = new Loader();
        $loader->loadFromDirectory($fixturePath);
        $fixtures = $loader->getFixtures();

        $purger   = new ORMPurger();
        $executor = new ORMExecutor($entityManager, $purger);
        $executor->execute($fixtures);
    }

    /**
     * @return \PaymentTest\Test\BootstrapTrait
     */
    public function getBootstrap()
    {
        return static::$bootstrap;
    }
}
