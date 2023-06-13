<?php

namespace PaymentTest\Controller;

use CpmsCommon\Service\ErrorCodeService;
use Laminas\Http\Header\ContentType;
use Laminas\Http\Request;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase as BaseTestCase;

/**
 * Class AbstractHttpControllerTestCase
 *
 * @package PaymentTest\Controller
 */
abstract class AbstractHttpControllerTestCase extends BaseTestCase
{
    use ControllerTestSetupTrait;

    /** @var  string The decoded body content of the response */
    protected $decodedResponseBody;

    /**
     * @param string $url
     * @param string $method
     * @param array $params
     * @param bool $isXmlHttpRequest
     * @throws \Exception
     */
    public function dispatch($url, $method = 'GET', $params = array(), $isXmlHttpRequest = false)
    {
        $this->addJsonContentHeaders();
        // json parameters must be encoded and then set to the request content, rather than passed directly as post
        // parameters
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $body = json_encode($params);
            $this->getRequest()->setContent($body);
            $params = [];
        }
        parent::dispatch($url, $method, $params, $isXmlHttpRequest);
    }

    /**
     * Dispatch a request with a raw (ie non-json) set of parameters
     *
     * @param $url
     * @param string $method
     * @param array $params
     * @param int $version
     * @throws \Exception
     */
    public function dispatchRaw($url, $method = 'GET', $params = array(), $version = 1)
    {
        $this->addJsonContentHeaders($version);
        parent::dispatch($url, $method, $params, false);
    }

    /**
     * Add JSON Headers to request
     *
     * @param int   $version
     * @param null  $accessToken
     * @param array $jsonBody
     */
    protected function addJsonContentHeaders($version = 1, $accessToken = null, array $jsonBody = null)
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $headers = $request->getHeaders();
        $headers->addHeader(
            ContentType::fromString(
                sprintf("Content-Type: application/vnd.dvsa-gov-uk.v%d+json; charset=UTF-8", $version)
            )
        );

        if ($accessToken) {
            $headers->addHeaderLine('Authorization: Bearer ' . $accessToken);
        }

        if ($jsonBody and is_array($jsonBody)) {
            $request->setContent(json_encode($jsonBody));
        }

        $headers->addHeaderLine('Accept: application/json');
        $request->setHeaders($headers);
    }

    /**
     * Asserts that the response has 200 header and contains an array consisting of items, page, limit
     * and total indexes.
     */
    public function assertResponseIsListView()
    {
        $this->assertResponseStatusCode(200);
        $this->assertArrayHasKey('items', $this->getDecodedResponseBody());
        $this->assertArrayHasKey('page', $this->getDecodedResponseBody());
        $this->assertArrayHasKey('limit', $this->getDecodedResponseBody());
        $this->assertArrayHasKey('total', $this->getDecodedResponseBody());
    }

    /**
     * Asserts that the response has a 302 header and the body content is empty.
     */
    public function assertIsRedirect()
    {
        $this->assertResponseStatusCode(302);
        $this->assertEmpty($this->getResponse()->getContent());
    }

    /**
     * @param int  $expectedErrorType  The expected error code
     * @param int  $expectedHttpStatus The expected response status
     * @param null $errorDetail        Any detail that is expected to be passed to the error message function
     */
    public function assertResponseIsError($expectedErrorType, $expectedHttpStatus = 400, $errorDetail = null)
    {
        $result = $this->getDecodedResponseBody();

        $this->assertResponseStatusCode($expectedHttpStatus);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals($expectedErrorType, $result['code']);
        $this->assertEquals(
            ErrorCodeService::getMessage($expectedErrorType, $errorDetail),
            $this->getDecodedResponseBody()['message']
        );
    }

    /**
     * After calling dispatch, will get the body content of the Response decoded as JSON
     *
     * @return array The JSON decoded content of the Response
     */
    public function getDecodedResponseBody()
    {
        return json_decode($this->getRawResponseBody(), true);
    }
}
