<?php

class FMatiere extends Zend_Form 
{
    private $periode;
    private $classeRadio;
    private $action;
    private $id;

    // surcharge le constructeur pour y ajouter nos parametres
    // puis appelle le constructeur parent
    public function __construct($periode =false, $id =false) {
        $this->periode = $periode;

        $this->id = $id ? 'FmatiereSeul' : 'Fmatiere';
        $this->classeRadio = $periode ? 'plustard' : 'rad';
        // si on a une periode, l'action ira vers infosperiode, sinon vers index
        $this->action = $periode ? '/palmares/infosperiode' : '/palmares/index';
        parent::__construct();
    }
    public function init()
    {
        // regle l'action (suivant que l'on ai une periode ou non), le methode et le nom
        $this->setAction($this->action)
              ->setMethod('post')
              ->setName($this->id);
        
        // regle les decorateurs
        $this->setDecorators(array(
            'FormElements',
            'Form',
        ));
        $this->setAttrib('class', 'zend_form');
        
        
        // créé un input type="radio"
        $radio = new Zend_Form_Element_Radio('radioMatiere');
        $radio->setLabel('Type de matière : ')
            // clé du tableau : value du bouton radio
            // valeur : affichée à lécran
            // pour d'autres options, rajouter une ligne dans le tableau
              ->setMultiOptions(array(
                    'Verre Couleur' => 'Verre Couleur',
                    'Papier/Carton' => 'Papier/Carton',
                    'Corps Creux' => 'Corps Creux'
             ))
            // coche par defaut le radio Verre Couleur
            ->setValue('Verre Couleur')
            // met les boutons a la suite (le separateur par defaut etant \n )
            ->setSeparator('&nbsp;&nbsp;');
        $radio->setAttrib('class', $this->classeRadio);
        // ajoute l'element au formulaire
        $this->addElement($radio);
        
        // si on a specifié une periode
        if($this->periode)
        {
            // créé un input type text pour la date de debut
            $dateDebut = new Zend_Form_Element_Text('dateDebut');
            $dateDebut->setLabel('Date de debut : ');
            // class datepicker, pour jquery
            // class aligner pour regrouper (voir plus bas)
            $dateDebut->setAttrib('class', 'datepicker aligner');
            $dateDebut->setRequired(true);
            // enleve l'autocompletion du navigateur (gene le datepicker)
            $dateDebut->setAttrib('autocomplete', 'off');
            $this->addElement($dateDebut);

            // créé un input type text pour la date de fin
            $dateFin = new Zend_Form_Element_Text('dateFin');
            $dateFin->setLabel('Date de fin : ');
            // class datepicker, pour jquery
            // class aligner pour regrouper (voir plus bas)
            $dateFin->setAttrib('class', 'datepicker aligner');
            $dateFin->setRequired(true);
            // enleve l'autocompletion du navigateur (gene le datepicker)
            $dateFin->setAttrib('autocomplete', 'off');
            $this->addElement($dateFin);        

            // bouton submit
            $submit = new Zend_Form_Element_Submit('sub_infosPeriode');
            $submit->setLabel('Envoyer');
            $submit->setAttrib('class', 'bt_submit');
            $this->addElement($submit);
       } 
       
       // decore les elements
        $this->setElementDecorators(array(
            array('ViewHelper'),
            array('Errors', array('tag' => 'div', 'class' => 'errors')),
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
        // parcourt les elements créé au dessus
        foreach ($this->getElements() as $element) {
            // si celui en cours a la class aligner
            if(in_array('aligner', explode(' ', $element->class)))
            {
                // on le sauvegarde
                $elementsToGroup[] = $element;
            }
        }
        // si on a au moins 1 valeur dans le tableau
        if ($elementsToGroup) {
            // créé un groupe avec les elements sauvegardés
            $this->addDisplayGroup($elementsToGroup, 'divdate', array(
                'order' => 1,   // place le groupe en deuxieme position
                'decorators' => array(  // met le decorateur
                    'FormElements',
                    array('HtmlTag', array('tag'=>'div', 'class' => 'divdate'))
                )
            ));
        }

        parent::loadDefaultDecorators();
    }
}