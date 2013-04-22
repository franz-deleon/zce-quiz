<?php
namespace Main;

use Main\Model\Speaker;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Main\Model\ExamManager;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
    public function onBootstrap($e)
    {
        $application = $e->getApplication();
        $serviceManager = $application->getServiceManager();

//        // Add initializer to Controller Service Manager that check if controllers needs entity manager injection
//        $controllerLoader->addInitializer(function ($instance) use ($serviceManager) {
//            if (method_exists($instance, 'setEntityManager')) {
//                $instance->setEntityManager($serviceManager->get('doctrine.entitymanager.orm_default'));
//            }
//        });

//        $speaker = new Speaker();
//        $speaker->setEventManager($serviceManager->get('EventManager'));
//print_r(get_class($serviceManager->get('sharedeventmanager')));
        $speaker = $serviceManager->get('di')->get('Main\Model\Speaker', array(
            'events' => $serviceManager->get('EventManager')
        ));
        $serviceManager->setService('speaker', $speaker);

        $examManager = new ExamManager;
        $examManager->setEntityManager($serviceManager->get('doctrine.entitymanager.orm_default'));
        $serviceManager->setService('ExamManager', $examManager);
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}