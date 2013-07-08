<?php

class FImport extends Zend_Form 
{
    public function init()
    {        
        $view = Zend_Layout::getMvcInstance()->getView();
     
        // regle l'action, la metode et le nom du formulaire
        $this->setAction($view->baseUrl('/index/importer'))
              ->setMethod('post')
              ->setName('Fimport');
        
        // quand on change les decorateurs par defaut, on doit dire ceux que l'on veut utiliser
         $this->setDecorators(array(
             'File',    // on a un input type file
             'FormElements',    // sans ca les elements de formulaires ne sont pas affichés
             'Form',
             array('HtmlTag', array('tag'=>'div', 'class' => 'zend_form', 'id'=>'divimport'))
         ));
         
         $radio = new Zend_Form_Element_Radio('radioMatiere');
         $radio->setLabel('Type de matière : ')
              ->setMultiOptions(array(
                    'Verre' => 'Verre',
                    'CORPS_PLATS' => 'Corps Plats',
                    'CORPS_CREUX' => 'Corps Creux'
             ))
            ->setSeparator('&nbsp;&nbsp;')
            ->setAttrib('class', 'mats');
         
        // crée un input type="file"
        $fichier = new Zend_Form_Element_File('input_fichier');
        $fichier->setAttrib('enctype', 'multipart/form-data');
        $fichier->setLabel('Fichier : ');
        $fichier->setDestination(RESOURCE_PATH.'\\temp');
        $fichier->setRequired(true); // ajoute une regle, obligeant a remplir le champ
        $fichier->setAttrib('size', '35');
        
        // crée un bouton submit
        $submit = new Zend_Form_Element_Submit('sub_fichier');
        $submit->setLabel('Envoyer');
        $submit->setAttrib('class', 'bt_submit');
        
        // ajoute les éléments créés au formulaire
        $this->addElements(array($radio, $fichier, $submit));
        
        $this->setElementDecorators(array(
            array('ViewHelper'),
            array('Errors', array('tag' => 'div', 'class' => 'error')),
            array('Label', array('tag' => 'p', 'class' => 'spanform')),
            array('HtmlTag', array('tag' => 'div', 'class' => 'divform'))
         ));
        $this->getElement('input_fichier')->setDecorators(
         array(
                'File',
                'Errors',
                array('Label', array('tag' => 'p')),
                array('HtmlTag' , array('tag' => 'div', 'class' => 'divform')),
            )
        );
    }
}
