<?php
namespace Main\Forms;

use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\Element;
use Zend\Form\Form;

class MultiOne extends Form implements ElementPrepareAwareInterface
{
    public function prepareElement(Form $form)
    {
        $this->add(array(
            'name' => 'answer',
            'attributes' => array(
                'type'  => 'radio',
                'name'  => 'answer',
                'value' => 'shit',
                'label' => 'some label',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Submit',
            ),
        ));
    }
}