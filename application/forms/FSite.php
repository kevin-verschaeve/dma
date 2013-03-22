<?php

class FSite extends Zend_Form
{
    private $tabConteneur;
    private $tabMatieres;
    private $matChecked;
    
    public function __construct($tabConteneur, $tabMatieres, $matChecked)
    {
        $this->tabConteneur = $tabConteneur;
        $this->tabMatieres = $tabMatieres;
        $this->matChecked = $matChecked;
        parent::__construct();
    }
    public function init()
    {
        // met l'action du formulaire et sa methode
        $this->setAction('/index/tonnage')
                  ->setMethod('post');
        
        // configure les decorateurs (a revoir)
        $this->setDecorators(array(
            'FormElements',
            //array('HtmlTag', array('tag' => 'div')),
            'Form',
        ));
        // creation des elements du formulaire
        $matiere = new Zend_Form_Element_Select('sel_matiere');
        $matiere->setLabel('Matiere : ')
                ->setMultiOptions($this->tabMatieres)   // rempli le select avec un tableau
                ->setValue($this->matChecked)   // selectionne une valeur par defaut
                ->setAttrib('class', 'selec');
                
        $conteneur = new Zend_Form_Element_Select('nConteneur');
        $conteneur->setLabel('Conteneur : ')
                ->setMultiOptions($this->tabConteneur)
                ->setRegisterInArrayValidator(false);

        // un input type text auquel on ajoute la classe datepicker (pour jquery)
        $dateDebut = new Zend_Form_Element_Text('dateDebut');
        $dateDebut->setLabel('Date de debut : ')
                  ->setAttrib('class', 'datepicker cacher')
                  ->setAttrib('autocomplete', 'off');
        
        // pareil que $dateDebut
        $dateFin = new Zend_Form_Element_Text('dateFin');
        $dateFin->setLabel('Date de fin : ')
                ->setAttrib('class', 'datepicker cacher')
                ->setAttrib('autocomplete', 'off');
        
        // bouton submit
        $submit = new Zend_Form_Element_Submit('sub');
        $submit->setAttrib('class', 'bt_submit')
                ->setLabel('Envoyer');

        // une fois les éléments créés, il faut les ajouter au formulaire
        $this->addElements(array(
                $matiere,
                $conteneur, 
                $dateDebut,
                $dateFin,
                $submit
            ));
        $this->setElementDecorators(array(
            array('ViewHelper'),
            array('Errors', array('tag' => 'div', 'class' => 'error')),
            array('Label', array('tag' => 'p', 'class' => 'spanform')),
            array('HtmlTag', array('tag' => 'div', 'class' => 'divform'))
         ));
        
    }
    public function loadDefaultDecorators()
    {
        $elementsToGroup = array();
        foreach ($this->getElements() as $element) {
            if(in_array('cacher', explode(' ', $element->class)))
            {
                $elementsToGroup[] = $element;
            }
        }
        if ($elementsToGroup) {
            $this->addDisplayGroup($elementsToGroup, 'divdate', array(
                'order' => 2,
                'decorators' => array(
                    'FormElements',
                    array('HtmlTag', array('tag'=>'div', 'class' => 'divdate'))
                )
            ));
        }

        parent::loadDefaultDecorators();
    }
}
