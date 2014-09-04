<?php

namespace Eye4webZfcUserForceLogoutTest\Eye4web\ZfcUser\ForceLogout;


use Zend\Mvc\MvcEvent;
use Eye4web\ZfcUser\ForceLogout\Module;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    protected $module;

    protected $eventManager;

    protected $authService;

    protected $serviceManager;

    protected $applicaiotn;

    protected $user;

    protected $mvcEvent;

    public function setUp()
    {
        $this->eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');

        $this->authService = $this->getMock('Zend\Authentication\AuthenticationServiceInterface');

        $this->serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');

        $this->application = $this->getMockBuilder('Zend\Mvc\Application')
            ->disableOriginalConstructor()
            ->getMock();

        $this->user = $this->getMock('Application\Entity\User');

        $this->mvcEvent = $this->getMock('Zend\Mvc\MvcEvent');


        $this->module = new Module();
    }

    public function testOnBootstrap()
    {

        $this->mvcEvent->expects($this->once())
            ->method('getApplication')
            ->will($this->returnValue($this->application));

        $this->application->expects($this->once())
            ->method('getEventManager')
            ->will($this->returnValue($this->eventManager));

        $this->eventManager->expects($this->once())
            ->method('attach')
            ->with(MvcEvent::EVENT_DISPATCH, array($this->module, 'checkForceLogout'), 1);

        $this->module->onBootstrap($this->mvcEvent);
    }

    /**
     * @dataProvider checkForceLogoutDataProvider
     */
    public function testCheckForceLogout($userLoggedIn, $user, $forceLogout)
    {
        $this->application->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($this->serviceManager));

        $this->serviceManager->expects($this->any())
            ->method('get')
            ->with('zfcuser_auth_service')
            ->will($this->returnValue($this->authService));

        $this->mvcEvent->expects($this->once())
            ->method('getApplication')
            ->will($this->returnValue($this->application));

        $this->authService->expects($this->once())
            ->method('hasIdentity')
            ->will($this->returnValue($userLoggedIn));

        if ($userLoggedIn) {
            $this->authService->expects($this->once())
                ->method('getIdentity')
                ->will($this->returnValue($user));

            if ($user instanceof UserForceLogoutInterface) {
                $this->user->expects($this->once())
                    ->method('getForceLogout')
                    ->will($this->returnValue($forceLogout));

                $response = $this->getMockBuilder('Zend\Stdlib\ResponseInterface')
                    ->disableOriginalConstructor()
                    ->getMock();

                $controller = $this->getMockBuilder('ZfcUser\Controller\UserController')
                    ->disableOriginalConstructor()
                    ->getMock();
                $controller->expects($this->once())
                    ->method('logoutAction')
                    ->will($this->returnValue($response));

                $controllerManager = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')
                    ->disableOriginalConstructor()
                    ->getMock();
                $controllerManager->expects($this->once())
                    ->method('get')
                    ->with('zfcuser')
                    ->will($controller);

                $hydrator = $this->getMockBuilder('Zend\Stdlib\Hydrator\HydratorInterface')
                    ->disableOriginalConstructor()
                    ->getMock();

                $this->serviceManager->expects($this->any())
                    ->method('get')
                    ->with('zfcuser_user_hydrator')
                    ->will($this->returnValue($hydrator));

                $mapper = $this->getMockBuilder('Eye4web\ZfcUser\ForceLogout\Mapper')
                    ->disableOriginalConstructor()
                    ->getMock();
                $mapper->expects($this->once())
                    ->method('setForceLogout')
                    ->with($user, false, $hydrator);

                $this->serviceManager->expects($this->any())
                    ->method('get')
                    ->with('Eye4web\ZfcUser\ForceLogout\Mapper')
                    ->will($this->returnValue($mapper));

                if ($forceLogout) {
                    $this->serviceManager->expects($this->any())
                        ->method('get')
                        ->with('ControllerManager')
                        ->will($this->returnValue($controllerManager));
                }
            }
        } else {
            $this->authService->expects($this->never())
                ->method('getIdentity');
        }

        $result = $this->module->checkForceLogout($this->mvcEvent);

        if ($userLoggedIn && $user instanceof UserForceLogoutInterface && $forceLogout) {
            $this->assertInstanceOf('Zend\Stdlib\ResponseInterface', $result);
        }
    }

    public function checkForceLogoutDataProvider()
    {
        $userWithNoInterface = $this->getMock('ZfcUser\Entity\UserInterface');
        return [
            /* $userLoggedIn, $user, $forceLogout */
            [false, $this->user, false],
            [false, $this->user, true],
            [true, $this->user, false],
            [true, $this->user, true],
            [false, $userWithNoInterface, false],
        ];
    }

    public function testGetServiceConfig()
    {
        $result = $this->module->getServiceConfig();
        $this->assertTrue(is_array($result));
    }
}
