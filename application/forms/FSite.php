<?php

class FSite extends Zend_Form
{
    private $tabSite;
    private $tabMatieres;
    private $matChecked;
    
    // surcharge le constructeur pour y ajouter nos parametres
    // puis appelle le constructeur parent
    public function __construct($tabSite, $tabMatieres, $matChecked)
    {
        $this->tabSite = $tabSite;
        $this->tabMatieres = $tabMatieres;
        $this->matChecked = $matChecked;
        parent::__construct();
    }
    public function init()
    {
        $view = Zend_Layout::getMvcInstance()->getView();
        
        // met l'action du formulaire et sa methode
        $this->setAction($view->baseUrl('/index/tonnage'))
                  ->setMethod('post');
        
        // configure les decorateurs
        $this->setDecorators(array(
            'FormElements',
            'Form',
        ));
        
        // creation des elements du formulaire
        
        // créé un select des differentes matieres
        $matiere = new Zend_Form_Element_Select('sel_matiere');
        $matiere->setLabel('Matiere : ')
                ->setMultiOptions($this->tabMatieres)   // rempli le select avec un tableau
                ->setValue($this->matChecked)   // selectionne une valeur par defaut
                ->setAttrib('onchange','change();');
                ;
                
        // créé un select des site pour la matiere selectionnée
        $site = new Zend_Form_Element_Select('nSite');
        $site->setLabel('Site : ')
                ->setMultiOptions($this->tabSite)
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
            
            
        $btdates = new Zend_Form_Element_Button('show');
        $btdates->setLabel('Entrer une période');
        $btdates->setAttrib('onclick','showdates();');
        
        // bouton submit
        $submit = new Zend_Form_Element_Submit('sub');
        $submit->setAttrib('class', 'bt_submit')
                ->setLabel('Envoyer');

        // une fois les éléments créés, il faut les ajouter au formulaire
        $this->addElements(array(
                $matiere,
                $site, 
                $dateDebut,
                $dateFin,
                $btdates,
                $submit
            ));
        $this->setElementDecorators(array(
            array('ViewHelper'),
            array('Errors', array('tag' => 'div', 'class' => 'error')),
            array('Label', array('tag' => 'p', 'class' => 'spanform')),
            array('HtmlTag', array('tag' => 'div', 'class' => 'divform'))
         ));
        
    }
    /**
     * Permet de regrouper des elements ayant la meme classe
     * et de leur ajouter des attributs
     */
    public function loadDefaultDecorators()
    {
        $elementsToGroup = array();
        // parcours tous les elements créés au dessus
        foreach ($this->getElements() as $element) {
            // si l'element en cours a la classe cacher
            if(in_array('cacher', explode(' ', $element->class)))
            {
                // on le sauvegarde
                $elementsToGroup[] = $element;
            }
        }
        // pour etre sur que l'on a des elements sauvegardés
        if ($elementsToGroup) {
            $this->addDisplayGroup($elementsToGroup, 'divdate', array(
                'order' => 2,   // place le groupe en 3eme position (commence a 0)
                'decorators' => array(  // ajoute un decorateur
                    'FormElements',
                    array('HtmlTag', array('tag'=>'div', 'class' => 'divdate'))
                )
            ));
        }

        parent::loadDefaultDecorators();
    }
}
