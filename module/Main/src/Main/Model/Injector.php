<?php
namespace Main\Model;

use Zend\EventManager\EventManager;

class Injector
{
    public function __construct($something = null)
    {
    }

    public function setSomething($val)
    {
    }

    public function setWeird(EventManager $em)
    {

    }
}