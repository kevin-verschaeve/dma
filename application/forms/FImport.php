<?php

class FImport extends Zend_Form 
{
    public function init()
    {
        // regle l'action, la metode et le nom du formulaire
        $this->setAction('/index/importer')
              ->setMethod('post')
              ->setName('Fimport');
        
        // quand on change les decorateurs par defaut, on doit dire ceux que l'on veut utiliser
         $this->setDecorators(array(
             'File',    // on a un input type file
             'FormElements',    // sans ca les elements de formulaires ne sont pas affichés
             array('Form',array('class'=>'zend_form')), // ajoute la class zend_form au formulaire
         ));
         
        // crée un input type="file"
        $fichier = new Zend_Form_Element_File('input_fichier');
        $fichier->setAttrib('enctype', 'multipart/form-data');
        $fichier->setLabel('Importer : ');
        $fichier->setRequired(true);    // ajoute une regle, obligeant a le remplir
        
        // crée un bouton submit
        $submit = new Zend_Form_Element_Submit('sub_fichier');
        $submit->setLabel('Envoyer');
        $submit->setAttrib('class', 'bt_submit');
        
        // ajoute les éléments créés au formulaire
        $this->addElements(array($fichier, $submit));
        
    }
}
