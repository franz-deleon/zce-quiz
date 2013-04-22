<?php
return array(
    'router' => array(
        'routes' => array(
            'homepage' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'IndexController',
                        'action'     => 'index',
                    ),
                ),
            ),
            'home' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/main/index',
                    'defaults' => array(
                        'controller' => 'IndexController',
                        'action'     => 'index',
                    ),
                ),
            ),
            'main-renew' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/main/renew',
                    'defaults' => array(
                        'controller' => 'IndexController',
                        'action'     => 'renew',
                    ),
                ),
            ),
            'main-exam-ended' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/main/exam-ended',
                    'defaults' => array(
                        'controller' => 'IndexController',
                        'action'     => 'exam-ended',
                    ),
                ),
            ),
            'main-exam-result' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/main/result',
                    'defaults' => array(
                        'controller' => 'IndexController',
                        'action'     => 'result',
                    ),
                ),
            ),
            'sample-index' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/main/sample',
                    'defaults' => array(
                        'controller' => 'SampleController',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'IndexController' => 'Main\Controller\IndexController',
            'SampleController' => 'Main\Controller\SampleController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'main/index/index'        => __DIR__ . '/../view/main/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            'Main' => __DIR__ . '/../view',
        ),
    ),
    // Doctrine config
    'doctrine' => array(
        'driver' => array(
            'main_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Main/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Main\Entity' => 'main_driver'
                )
            )
        )
    ),
//    'di' => array(
//        'definition' => array(
//            'class' => array(
//                'Main\Model\Speaker' => array(
//                    'setInjector' => array('required' => true),
//    				'setEventManager' => array('required' => true),
//                ),
//            ),
//        ),
//        'instance' => array(
//    		'preferences' => array(
//                 'Zend\EventManager\EventManagerInterface' => 'Zend\EventManager\EventManager',
//            ),
//        ),
//    ),
);