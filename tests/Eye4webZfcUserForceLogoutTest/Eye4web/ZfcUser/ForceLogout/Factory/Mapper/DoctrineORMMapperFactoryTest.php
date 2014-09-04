<?php

namespace Eye4webZfcUserForceLogoutTest\Eye4web\ZfcUser\ForceLogout\Factory\Mapper;


use Eye4web\ZfcUser\ForceLogout\Factory\Mapper\DoctrineORMMapperFactory;

class DoctrineORMMapperFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;

    protected $serviceManager;

    public function setUp()
    {
        $this->serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');

        $this->factory = new DoctrineORMMapperFactory();
    }

    public function testCreateService()
    {
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serviceManager->expects($this->once())
            ->method('get')
            ->with('Doctrine\ORM\EntityManager')
            ->will($this->returnValue($entityManager));

        $result = $this->factory->createService($this->serviceManager);

        $this->assertInstanceOf('Eye4web\ZfcUser\ForceLogout\Mapper\DoctrineORMMapper', $result);
    }
}
