<?php

class TestController extends Zend_Controller_Action
{
    public function indexAction()
    {
        
       /* $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('vide');
        try 
        {
            $dd = '03/04/2013';
            //echo serialize($dd);
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
        exit;*/
        
    }
    public function pdfAction()
    {
        $this->_helper->actionStack('header', 'index', 'default', array());
        $tsite = new TSite;
        $sites = $tsite->getInfos();
        //Zend_Debug::dump($coms);exit;
        
        try 
        {
            $pdf = new Zend_Pdf(); 
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4); 
            $pdf->pages = array_reverse($pdf->pages);
            $pdf->pages[] = $page; 
            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10); 

            $xText = 20;   $yText = 720;
            $rectX1 = 10;  $rectY1 = 735;
            $rectX2 = 150; $rectY2 = 695;
            // Draw text 
            foreach($sites as $com)
            {
                $txt = $page->drawText($com['NOM_SITE'], $xText, $yText);
                $page->drawRectangle($rectX1, $rectY1, $rectX2 , $rectY2, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
                $yText -= 20; $rectY1 -= 20; $rectY2 -= 20;
            }
            $pdf->save('example.pdf');
            //$this->_redirect('/example.pdf');

        }
        catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }
    }
    
    public function piAction()
    {
        echo phpinfo();exit;
    }
}