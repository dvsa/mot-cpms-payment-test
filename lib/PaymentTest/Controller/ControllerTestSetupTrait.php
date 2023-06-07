<?php

namespace PaymentTest\Controller;

use PaymentTest\Test\BootstrapTrait;

trait ControllerTestSetupTrait
{
    /** @var  \Laminas\ServiceManager\ServiceManager */
    protected $serviceManager;

    /** @var  string The raw body content of the response */
    protected $rawResponseBody;

    /**
     * @return BootstrapTrait
     */
    abstract public function getBootstrap();

    /**
     * @param $applicationConfig
     */
    abstract protected function setApplicationConfig($applicationConfig);

    /**
     * @return mixed
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setApplicationConfig($this->getBootstrap()->getConfig());
        $this->serviceManager = $this->getServiceManager();
        $this->getServiceManager()->setAllowOverride(true);
    }

    /**
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->getBootstrap()->getServiceManager();
    }

    /**
     * After calling dispatch, will get the raw body content of the Response
     *
     * @return string The raw body content of the Response
     */
    public function getRawResponseBody()
    {
        return $this->getResponse()->getContent();
    }
}
