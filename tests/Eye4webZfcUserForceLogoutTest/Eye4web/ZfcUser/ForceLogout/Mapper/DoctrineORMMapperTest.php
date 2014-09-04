<?php

namespace Eye4webZfcUserForceLogoutTest\Eye4web\ZfcUser\ForceLogout\Mapper;


use Eye4web\ZfcUser\ForceLogout\Mapper\DoctrineORMMapper;

class DoctrineORMMapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var DoctrineORMMapper */
    protected $mapper;

    protected $entityManager;

    public function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper = new DoctrineORMMapper($this->entityManager);
    }

    public function testForceLogout()
    {
        $forceLogout = true;
        $user = $this->getMock('Eye4web\ZfcUser\ForceLogout\Entity\UserForceLogoutInterface');
        $hydrator = $this->getMock('Zend\Stdlib\Hydrator\HydratorInterface');

        $user->expects($this->once())
            ->method('setForceLogout')
            ->with($forceLogout);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($user);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->mapper->setForceLogout($user, $forceLogout, $hydrator);

        $this->assertInstanceOf('Eye4web\ZfcUser\ForceLogout\Entity\UserForceLogoutInterface', $result);
        $this->assertSame($user, $result);
    }
}
