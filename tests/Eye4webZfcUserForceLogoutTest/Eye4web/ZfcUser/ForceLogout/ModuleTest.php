<?php

namespace Eye4webZfcUserForceLogoutTest\Eye4web\ZfcUser\ForceLogout;

use Eye4web\ZfcUser\ForceLogout\Entity\UserForceLogoutInterface;
use Zend\Mvc\MvcEvent;
use Eye4web\ZfcUser\ForceLogout\Module;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    protected $module;

    protected $eventManager;

    protected $authService;

    protected $serviceManager;

    protected $application;

    protected $user;

    protected $mvcEvent;

    protected $serviceManagerPlugins;

    public function setUp()
    {
        $this->eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');

        $this->authService = $this->getMock('Zend\Authentication\AuthenticationServiceInterface');

        $this->serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $this->serviceManager->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(array($this, 'helperMockCallbackServiceManagerGet')));

        $this->application = $this->getMockBuilder('Zend\Mvc\Application')
            ->disableOriginalConstructor()
            ->getMock();

        $this->user = $this->getMock('Eye4web\ZfcUser\ForceLogout\Entity\UserForceLogoutInterface');

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
    public function testCheckForceLogout($userLoggedIn, $userInterface, $forceLogout)
    {
        $this->application->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($this->serviceManager));

        $this->serviceManagerPlugins['zfcuser_auth_service'] = $this->authService;

        $this->mvcEvent->expects($this->once())
            ->method('getApplication')
            ->will($this->returnValue($this->application));

        $this->authService->expects($this->once())
            ->method('hasIdentity')
            ->will($this->returnValue($userLoggedIn));

        if ($userLoggedIn) {
            $this->authService->expects($this->once())
                ->method('getIdentity')
                ->will($this->returnValue($this->user));

            if ($userInterface) {
                $this->user->expects($this->once())
                    ->method('getForceLogout')
                    ->will($this->returnValue($forceLogout));

                if ($forceLogout) {
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
                        ->will($this->returnValue($controller));

                    $hydrator = $this->getMockBuilder('Zend\Stdlib\Hydrator\HydratorInterface')
                        ->disableOriginalConstructor()
                        ->getMock();

                    $this->serviceManagerPlugins['zfcuser_user_hydrator'] = $hydrator;

                    $mapper = $this->getMockBuilder('Eye4web\ZfcUser\ForceLogout\Mapper\ForceLogoutMapperInterface')
                        ->disableOriginalConstructor()
                        ->getMock();
                    $mapper->expects($this->once())
                        ->method('setForceLogout')
                        ->with($this->user, false, $hydrator);

                    $this->serviceManagerPlugins['Eye4web\ZfcUser\ForceLogout\Mapper'] = $mapper;
                    
                    $this->serviceManagerPlugins['ControllerManager'] = $controllerManager;
                }
            }
        } else {
            $this->authService->expects($this->never())
                ->method('getIdentity');
        }

        $result = $this->module->checkForceLogout($this->mvcEvent);

        if ($userLoggedIn && $userInterface && $forceLogout) {
            $this->assertInstanceOf('Zend\Stdlib\ResponseInterface', $result);
        }
    }

    public function checkForceLogoutDataProvider()
    {
        return [
            /* $userLoggedIn, $user, $forceLogout */
            [false, true, false],
            [false, true, true],
            [true, true, false],
            [true, true, true],
            [false, false, false],
            [true, false, false],
            [true, true, false],
            [true, true, true],
        ];
    }

    public function testGetServiceConfig()
    {
        $result = $this->module->getServiceConfig();
        $this->assertTrue(is_array($result));
    }

    public function helperMockCallbackServiceManagerGet($key)
    {
        return (array_key_exists($key, $this->serviceManagerPlugins))
            ? $this->serviceManagerPlugins[$key]
            : null;
    }
}
