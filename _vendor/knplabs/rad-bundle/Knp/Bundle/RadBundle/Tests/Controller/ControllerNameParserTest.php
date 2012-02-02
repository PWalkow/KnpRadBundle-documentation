<?php

namespace Knp\Bundle\RadBundle\Tests\Controller;

use Knp\Bundle\RadBundle\Controller\ControllerNameParser;

class ControllerNameParserTest extends \PHPUnit_Framework_TestCase
{
    public function testUsualControllersParse()
    {
        $parser = $this->createParser();

        $this->assertSame(
            'Knp\Bundle\RadBundle\Controller\Fixture\UsualBundle\Controller\DefaultController::indexAction',
            $parser->parse('KnpUsualBundle:Default:index')
        );

        $this->assertSame(
            'Knp\Bundle\RadBundle\Controller\Fixture\UsualBundle\Controller\ShortController::indexAction',
            $parser->parse('KnpUsualBundle:Short:index')
        );
    }

    public function testAppControllersParse()
    {
        $parser = $this->createParser();

        $this->assertSame(
            'Knp\Bundle\RadBundle\Controller\Fixture\Applicatoin\Controller\DefaultController::indexAction',
            $parser->parse('KnpApp:Default:index')
        );

        $this->assertSame(
            'Knp\Bundle\RadBundle\Controller\Fixture\Applicatoin\Controller\ShortController::index',
            $parser->parse('KnpApp:Short:index')
        );
    }

    private function createParser()
    {
        $bundles = array(
            'KnpUsualBundle' => array(
                $this->getBundle(
                    'Knp\Bundle\RadBundle\Controller\Fixture\UsualBundle',
                    'KnpUsualBundle',
                    'Symfony\Component\HttpKernel\Bundle\BundleInterface'
                )
            ),
            'KnpApp' => array(
                $this->getBundle(
                    'Knp\Bundle\RadBundle\Controller\Fixture\Applicatoin',
                    $appName = 'KnpApp',
                    'Knp\Bundle\RadBundle\Bundle\ApplicationBundle'
                )
            ),
        );

        $kernel = $this->getMockBuilder(
            'Knp\Bundle\RadBundle\HttpKernel\RadKernel'
        )->disableOriginalConstructor()->getMock();
        $kernel
            ->expects($this->any())
            ->method('getBundle')
            ->will($this->returnCallback(function ($bundle) use ($bundles) {
                return $bundles[$bundle];
            }))
        ;
        $kernel
            ->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnCallback(function () use($appName) {
                return new Configuration($appName);
            }))
        ;

        return new ControllerNameParser($kernel);
    }

    private function getBundle($namespace, $name, $class)
    {
        $bundle = $this->getMockBuilder($class)->disableOriginalConstructor()->getMock();
        $bundle->expects($this->any())->method('getName')->will($this->returnValue($name));
        $bundle->expects($this->any())->method('getNamespace')->will($this->returnValue($namespace));

        return $bundle;
    }
}

class Configuration
{
    public function __construct($appName)
    {
        $this->appName = $appName;
    }

    public function getApplicationName()
    {
        return $this->appName;
    }
}

namespace Knp\Bundle\RadBundle\Controller\Fixture\UsualBundle\Controller;

class DefaultController
{
    public function indexAction()
    {
    }
}

class ShortController
{
    public function index()
    {
    }
}

namespace Knp\Bundle\RadBundle\Controller\Fixture\Applicatoin\Controller;

class DefaultController
{
    public function indexAction()
    {
    }
}

class ShortController
{
    public function index()
    {
    }
}