<?php
class FSupLignes extends Zend_Form 
{
    private $sup;
    private  $matiere;
    private $mois;
    private $annee;
    
    public function __construct($matiere, $mois, $annee, $sup =false) {    
        $this->matiere = $matiere;
        $this->mois = $mois;
        $this->annee = $annee;
        $this->sup = $sup;
        parent::__construct();
    } 
    
    public function init()
    {   
        $view = Zend_Layout::getMvcInstance()->getView();
     
        // regle l'action, la metode et le nom du formulaire
        $this->setAction($view->baseUrl('/index/suplignes'))
              ->setMethod('post')
              ->setName('FsupLignes');
        
        // créé un input type="radio"
        $radio = new Zend_Form_Element_Radio('matiere');
        $radio->setLabel('Type de matière : ')
            // clé du tableau : value du bouton radio
            // valeur : affichée à lécran
            // pour d'autres options, rajouter une ligne dans le tableau
              ->setMultiOptions(array(
                    'VERRE' => 'Verre',
                    'CORPS_PLATS' => 'Corps Plats',
                    'CORPS_CREUX' => 'Corps Creux'
             ))
            // coche par defaut le radio Verre Couleur
            ->setValue($this->matiere)
            // met les boutons a la suite (le separateur par defaut etant \n )
            ->setSeparator('&nbsp;&nbsp;');
        
        $mois = new Zend_Form_Element_Select('mois');
        $mois->setLabel('Voir les enregistrements pour le mois :');
        $mois->setMultiOptions(array(
            1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août',
            'Septembre', 'Octobre', 'Novembre', 'Décembre'
        ));
        $mois->setAttrib('class', 'aligner');
        $mois->setValue($this->mois);
        
        $annee = new Zend_Form_Element_Text('annee');
        $annee->setAttrib('class', 'aligner');
        $annee->setValue($this->annee);
        
        $sub_voir = new Zend_Form_Element_Submit('sub_voir');
        $sub_voir->setLabel('Envoyer');
        $sub_voir->setAttrib('class', 'bt_submit');
        
        $sub_sup = null;
        if($this->sup) {
            $sub_sup = new Zend_Form_Element_Submit('sub_sup');
            $sub_sup->setLabel('Supprimer');
            $sub_sup->setAttrib('class', 'bt_submit');
            $sub_sup->setAttrib('onclick', 'return confirmer();');
        }
        
        $this->addElements(array($radio, $mois, $annee, $sub_voir, $sub_sup));
        
        $this->setElementDecorators(array(
            array('ViewHelper'),
            array('Errors', array('tag' => 'div', 'class' => 'error')),
            array('Label', array('tag' => 'p', 'class' => 'spanform')),
            array('HtmlTag', array('tag' => 'div', 'class' => 'divform margin10'))
         ));
    }
}

