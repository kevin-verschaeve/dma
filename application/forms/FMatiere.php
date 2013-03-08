<?php

class FMatiere extends Zend_Form 
{
    private $periode;
    private $classeRadio;
    private $action;
    
    public function __construct($periode =false) {
        $this->periode = $periode;

        $this->classeRadio = $periode ? 'plustard' : 'rad';
        $this->action = $periode ? '/palmares/infosperiode' : '/palmares/index';
        parent::__construct();
    }
    public function init()
    {
        $this->setAction($this->action)
              ->setMethod('post')
              ->setName('Fmatiere');
        
        $radio = new Zend_Form_Element_Radio('radioMatiere');
        $radio->setLabel('Type de matière')
              ->setMultiOptions(array(
                    'Verre Couleur' => 'Verre',
                    'Papier/Carton' => 'Papier/Carton',
                    'Corps Creux' => 'Corps Creux'
             ))
            ->setValue('Verre Couleur')
            ->setSeparator('');
        $radio->setAttrib('class', $this->classeRadio);
        $this->addElement($radio);
        
        if($this->periode)
        {
            $dateDebut = new Zend_Form_Element_Text('dateDebut');
            $dateDebut->setLabel('Date de debut : ');
            $dateDebut->setAttrib('class', 'datepicker');
            $dateDebut->setRequired(true);
            $this->addElement($dateDebut);

            $dateFin = new Zend_Form_Element_Text('dateFin');
            $dateFin->setLabel('Date de fin : ');
            $dateFin->setAttrib('class', 'datepicker');
            $dateFin->setRequired(true);
            $this->addElement($dateFin);
            
            // bouton submit
            $submit = new Zend_Form_Element_Submit('sub');
            $submit->setLabel('Envoyer');
            $this->addElement($submit);
        }
    }
}