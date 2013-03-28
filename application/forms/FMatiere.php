<?php

class FMatiere extends Zend_Form 
{
    private $periode;
    private $classeRadio;
    private $action;
    private $id;


    public function __construct($periode =false, $id =false) {
        $this->periode = $periode;

        $this->id = $id ? 'FmatiereSeul' : 'Fmatiere';
        $this->classeRadio = $periode ? 'plustard' : 'rad';
        $this->action = $periode ? '/palmares/infosperiode' : '/palmares/index';
        parent::__construct();
    }
    public function init()
    {
        $this->setAction($this->action)
              ->setMethod('post')
              ->setName($this->id);
        
        $this->setDecorators(array(
            'FormElements',
            //array('HtmlTag', array('tag' => 'div')),
            'Form',
        ));
        $this->setAttrib('class', 'zend_form');
        
        $radio = new Zend_Form_Element_Radio('radioMatiere');
        $radio->setLabel('Type de matière : ')
              ->setMultiOptions(array(
                    'Verre Couleur' => 'Verre Couleur',
                    'Papier/Carton' => 'Papier/Carton',
                    'Corps Creux' => 'Corps Creux'
             ))
            ->setValue('Verre Couleur')
            ->setSeparator('&nbsp;&nbsp;');
        $radio->setAttrib('class', $this->classeRadio);
        $this->addElement($radio);
        
        if($this->periode)
        {
            $dateDebut = new Zend_Form_Element_Text('dateDebut');
            $dateDebut->setLabel('Date de debut : ');
            $dateDebut->setAttrib('class', 'datepicker aligner');
            $dateDebut->setRequired(true);
            $dateDebut->setAttrib('autocomplete', 'off');
            $this->addElement($dateDebut);

            $dateFin = new Zend_Form_Element_Text('dateFin');
            $dateFin->setLabel('Date de fin : ');
            $dateFin->setAttrib('class', 'datepicker aligner');
            $dateFin->setRequired(true);
            $dateFin->setAttrib('autocomplete', 'off');
            $this->addElement($dateFin);        

            // bouton submit
            $submit = new Zend_Form_Element_Submit('sub_infosPeriode');
            $submit->setLabel('Envoyer');
            $submit->setAttrib('class', 'bt_submit');
            $this->addElement($submit);
       } 
       
        $this->setElementDecorators(array(
            array('ViewHelper'),
            array('Errors', array('tag' => 'div', 'class' => 'errors')),
            array('Label', array('tag' => 'p', 'class' => 'spanform')),
            array('HtmlTag', array('tag' => 'div', 'class' => 'divform'))
         ));
    }
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
                'order' => 1,   // le place en deuxieme position
                'decorators' => array(  // met le decorateur
                    'FormElements',
                    array('HtmlTag', array('tag'=>'div', 'class' => 'divdate'))
                )
            ));
        }

        parent::loadDefaultDecorators();
    }
}