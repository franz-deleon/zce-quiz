<?php
namespace Main\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;
use Main\Model\Speaker;

class SampleController extends AbstractActionController
{
    public function __construct()
    {
    }

    public function indexAction()
    {
        $this->getEventManager()->getSharedManager()->attach('Main\Model\Speaker', 'talking', function ($e) {
            $target = $e->getTarget();
            print_r("1. " . get_class($target));
        });

        $speaker = $this->getServiceLocator()->get('speaker');
        //$this->getServiceLocator()->get('di')->get('Main\Model\Speaker');

        //$speaker = $this->getServiceLocator()->get('di')->get('Main\Model\Speaker');
        $speaker->talking('hello');
    }
}