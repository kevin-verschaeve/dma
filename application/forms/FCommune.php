<?php

class FCommune extends Zend_Form
{
    private $lesCommunes;
    private $idCommune;
    
    public function __construct($lesCommunes, $idCommune){
        $this->lesCommunes = $lesCommunes;
        $this->idCommune = $idCommune;
        parent::__construct();
    }
    public function init()
    {
        $this->setMethod('post')
             ->setName('FCommune');
        
        $this->setDecorators(array(
            'FormElements',
            //array('HtmlTag', array('tag' => 'div')),
            'Form',
        ));
        $this->setAttrib('class', 'zend_form');
        
        $communes = new Zend_Form_Element_Select('sel_communes');
        $communes->setValue($this->idCommune);
        $communes->setLabel('Communes : ')
                ->setMultiOptions($this->lesCommunes);
        
        $this->addElement($communes);
        
        
        $this->setElementDecorators(array(
            array('ViewHelper'),
            array('Errors', array('tag' => 'div', 'class' => 'errors')),
            array('Label', array('tag' => 'p', 'class' => 'spanform')),
            array('HtmlTag', array('tag' => 'div', 'class' => 'divform'))
         ));
    }
}
