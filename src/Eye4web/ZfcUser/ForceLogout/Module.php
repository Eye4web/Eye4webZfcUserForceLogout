<?php

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace Eye4web\ZfcUser\ForceLogout;

use Eye4web\ZfcUser\ForceLogout\Entity\UserForceLogoutInterface;
use Eye4web\ZfcUser\ForceLogout\Mapper\ForceLogoutMapperInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'checkForceLogout'), 1);
    }

    /**
     * @param $event
     */
    public function checkForceLogout(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();
        $authService = $serviceManager->get('zfcuser_auth_service');

        if ($authService->hasIdentity()) {
            $user = $authService->getIdentity();
            if ($user instanceof UserForceLogoutInterface && $user->getForceLogout()) {
                $controller = $serviceManager->get('ControllerManager')->get('zfcuser');
                $response = $controller->logoutAction();

                /** @var ForceLogoutMapperInterface $mapper */
                $mapper = $serviceManager->get('Eye4web\ZfcUser\ForceLogout\Mapper');
                $mapper->setForceLogout($user, false, $serviceManager->get('zfcuser_user_hydrator'));

                return $response;
            }
        }
    }

    public function getServiceConfig()
    {
        return include __DIR__ . '/../../../../config/service.config.php';
    }
}
