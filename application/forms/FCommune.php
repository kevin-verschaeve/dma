<?php

class FCommune extends Zend_Form
{
    private $lesCommunes;
    private $idCommune;
    
    // surcharge le constructeur pour y ajouter nos parametres
    // puis appelle le constructeur parent
    public function __construct($lesCommunes, $idCommune){
        $this->lesCommunes = $lesCommunes;
        $this->idCommune = $idCommune;
        parent::__construct();
    }
    public function init()
    {
        // regle la methode et ajoute une nom
        // pas besoin d'action ici, on ne valide rien, on le fait en javascript
        $this->setMethod('post')
             ->setName('FCommune');
        
        // quand on change les decorateurs par defaut, on doit dire ceux que l'on veut utiliser
        $this->setDecorators(array(
            'FormElements', // sans ca les elements de formulaires ne sont pas affichés
            'Form',
        ));
        // ajoute la class zend_form a ce formulaire
        $this->setAttrib('class', 'zend_form');
        
        // créé un select, dont la valeur selectionné par defaut sera celle portant l'id $this->idCommune
        // $this->lesCommunes est un array(idCommune => nomCommune)
        // la clé du tableau est mise en value de chaque option
        // la valeur est affichée a l'ecran dans le select
        $communes = new Zend_Form_Element_Select('sel_communes');
        $communes->setValue($this->idCommune);
        $communes->setLabel('Communes : ')  
                ->setMultiOptions($this->lesCommunes);
        
        // ajoute l'element au formulaire
        $this->addElement($communes);
        
        
        // decore chaque element du formulaire
        $this->setElementDecorators(array(
            array('ViewHelper'),
            // les erreurs seront placées dans un div ayant la class errors
            array('Errors', array('tag' => 'div', 'class' => 'errors')),
            // les labels sont placés dans des balses p ayant la classe spanform
            array('Label', array('tag' => 'p', 'class' => 'spanform')),
            // les balises par defaut sont remplacées par des div ayants la class divform
            array('HtmlTag', array('tag' => 'div', 'class' => 'divform'))
         ));
    }
}
