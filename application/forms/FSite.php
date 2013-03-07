<?php

class FSite extends Zend_Form
{
    public function init()
    {
        // met l'action du formulaire et sa methode
        $this->setAction('/index/tonnage')
                  ->setMethod('post');
        
        // configure les decorateurs (a revoir)
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'class'=>'zend_form')),
            'Form',
        ));
        
        // creation des elements du formulaire
        // input type text Requis pour pouvoir valider
        $input = new Zend_Form_Element_Text('idsite');
        $input->setLabel('Id site : ');
        $input->setRequired(true);

        // un input type text auquel on ajoute la classe datepicker 'pour jquery)
        $dateDebut = new Zend_Form_Element_Text('dateDebut');
        $dateDebut->setLabel('Date de debut : ');
        $dateDebut->setAttrib('class', 'datepicker');
        
        // pareil que $dateDebut
        $dateFin = new Zend_Form_Element_Text('dateFin');
        $dateFin->setLabel('Date de fin : ');
        $dateFin->setAttrib('class', 'datepicker');
        
        // bouton submit
        $submit = new Zend_Form_Element_Submit('sub');
        $submit->setLabel('Envoyer');

        // une fois les éléments créés, il faut les ajouter au formulaire
        $this->addElements(array(
                $input, 
                $dateDebut,
                $dateFin,
                $submit
            ));
    }
}
