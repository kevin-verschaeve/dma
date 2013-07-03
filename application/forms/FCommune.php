<?php

class FCommune extends Zend_Form
{
    private $lesCommunes;
    private $idCommune;
    private $lamatiere;
    
    // surcharge le constructeur pour y ajouter nos parametres
    // puis appelle le constructeur parent
    public function __construct($lesCommunes, $idCommune, $lamatiere){
        $this->lesCommunes = $lesCommunes;
        $this->idCommune = $idCommune;
        $this->lamatiere = $lamatiere;
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
        
        $matiere = new Zend_Form_Element_Radio('matiere');
        $matiere->setLabel('Type de matière : ')
            // clé du tableau : value du bouton radio
            // valeur : affichée à lécran
            // pour d'autres options, rajouter une ligne dans le tableau
              ->setMultiOptions(array(
                    'VERRE' => 'Verre',
                    'CORPS_PLATS' => 'Corps Plats',
                    'CORPS_CREUX' => 'Corps Creux'
             ))
            // coche par defaut le radio Verre Couleur
            ->setValue($this->lamatiere)
            // met les boutons a la suite (le separateur par defaut etant \n )
            ->setSeparator('&nbsp;&nbsp;');
        
        // créé un select, dont la valeur selectionné par defaut sera celle portant l'id $this->idCommune
        // $this->lesCommunes est un array(idCommune => nomCommune)
        // la clé du tableau est mise en value de chaque option
        // la valeur est affichée a l'ecran dans le select
        $communes = new Zend_Form_Element_Select('sel_communes');
        $communes->setValue($this->idCommune);
        $communes->setLabel('Communes : ')  
                ->setMultiOptions($this->lesCommunes);
        
        // bouton submit
        $submit = new Zend_Form_Element_Submit('sub_graphique');
        $submit->setLabel('Envoyer');
        $submit->setAttrib('class', 'bt_submit');
        
        // ajoute l'element au formulaire
        $this->addElements(array($matiere,$communes, $submit));
        
        
        // decore chaque element du formulaire
        $this->setElementDecorators(array(
            array('ViewHelper'),
            // les erreurs seront placées dans un div ayant la class errors
            array('Errors', array('tag' => 'div', 'class' => 'errors')),
            // les labels sont placés dans des balises p ayant la classe spanform
            array('Label', array('tag' => 'p', 'class' => 'spanform')),
            // les balises par defaut sont remplacées par des div ayants la class divform
            array('HtmlTag', array('tag' => 'div', 'class' => 'divform'))
         ));
    }
}
