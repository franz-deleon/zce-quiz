<?php
namespace Main\Model;

use Zend\EventManager\EventCollection;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;

class Speaker
{
    protected $output = array();

    protected $events;


    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));
        $this->events = $events;
        return $this;
    }

    public function getEventManager()
    {
        return $this->events;
    }

    public function setInjector(Injector $injector)
    {
        //var_dump($injector);
    }

    public function talking($sentence)
    {
        $params = compact($sentence);
        $this->getEventManager()->trigger('talking', $this, $params);
    }
}