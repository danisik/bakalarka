<?php

//
//		CONFERENCE PORTAL PROJECT
//		VERSION 3.1.0
//
//		Copyright (c) 2010-2019 Dept. of Computer Science & Engineering,
//		Faculty of Applied Sciences, University of West Bohemia in PlzeĹ.
//		All rights reserved.
//
//		Code written by:	Vojtech Danisik
//		Last update on:		07-04-2019
//      Encoding: utf-8 no BOM
//

//
//      Class for printing tcpdf elements
//
class TCPDFElements {
    
    //default font size of title parameters
    private $font_size_of_review_ID = 23;
    private $font_size_of_header_title = 20;
    private $font_size_of_title_info = 17;
    private $font_size_of_title = 30;
    private $font_size_of_name = 17;        
    private $font_size_of_info = 12;
    
    public $image_path = '';
    
    //tsd logo image parameters
    private $logo_x = 170;
    private $logo_y = 0;
    private $logo_w = 40;
    private $logo_h = 20;
    private $logo_fitbox = 10;
    
    //watermark parameters
    public $watermark_text = '';
    private $watermark_font_type = 'helvetica';
    private $watermark_font_style = 'B';
    private $watermark_font_size = 100;
    
    //used fonts
    private $bold_font_tcpdf = 'times';
    private $bold_font_html = 'Times New Roman';
    private $normal_font = 'helvetica';
    
    //default font size for info about form element
    private $radiobutton_text_size = 14;
    private $textarea_text_size = 14;
    
    function __construct($image_path, $watermark_text) {
        $this->image_path = $image_path;
        $this->watermark_text = $watermark_text;
    }
    
    //
    //print radiobutton into pdf
    //$pdf - tcpdi class
    //$name_of_evaluation - name of evaluation parameter
    //$evaluation_info - info about purpose of this group of radiobuttons
    //$count_of_rankings - scale of evaluations
    //$group_ID - id of group
    //
    //return $pdf - tcpdi class
    function evaluation_radio_buttons($pdf, $name_of_evaluation, $evaluation_info, $group_ID){       
        $pdf->writeHTML('<span style="font-weight: bold; font-family: '.$this->bold_font_html.'; font-size: '.$this->radiobutton_text_size.'pt;">'.$name_of_evaluation.'</span> - '.$evaluation_info.': ');
        for ($i = 0; $i <= RadiobuttonInfo::Count_of_evaluations_to; $i++) {
            $pdf->RadioButton(RadioButtonInfo::Group_text.''.$group_ID, 5, array(), array(), $i);
            $pdf->Cell(13, 5, $i);
        }
        $pdf->Ln(6);  
        return $pdf;
    }            
    
    //
    //print textarea into pdf
    //$pdf - tcpdi class
    //$textarea_header - header text of textarea
    //$textarea_info - for what purpose is this textarea
    //$textarea_ID - id of textarea
    //$rows - how many rows textarea have to 
    //$cols - how many cols textarea have to
    //$textarea_text - text to be displayed in textarea by default 
    //
    //return $pdf - tcpdi class
    function evaluation_textarea($pdf, $textarea_header, $textarea_info, $textarea_ID, $rows, $cols, $textarea_text = '') {
        $pdf->writeHTML('<span style="font-weight: bold; font-family: '.$this->bold_font_html.'; font-size: '.$this->textarea_text_size.'pt;">'.$textarea_header.'</span> - '.$textarea_info.': ');
        $pdf->TextField(TextareaInfo::Textarea_text.''.$textarea_ID, $rows, $cols, array('multiline'=>true, 'lineWidth'=>0, 'borderStyle'=>'none'), array('v'=>$textarea_text));
        $pdf->Ln(6);
        return $pdf;
    } 
    
    //print horizontal bar into pdf
    //$pdf - tcpdi class
    //
    //return $pdf - tcpdi class
    function horizontal_bar($pdf) {
        $pdf->writeHTML("<hr>", true, false, false, false, '');  
        return $pdf;
    }             
    
    //
    //get instructions for evaluation
    //$pdf - tcpdi class
    //$text_conversioner - our text conversioner
    //$xml_reader - ownXmlReader contains instructions
    //
    //return $pdf - tcpdi class
    function evaluation_instructions($pdf, $text_conversioner, $configuration_data) {
        //get text from XML
        $instruction_header = $configuration_data->getInstruction_header();
        $instruction_text = $configuration_data->getInstruction_text();
        $instruction_warning = $configuration_data->getInstruction_warning();
        
        //unite all info to one text
        $full_text = $instruction_header.$instruction_text.$instruction_warning;
        
        //calculate length of each part of info
        $len_of_header = mb_strlen($instruction_header);
        $len_of_text = mb_strlen($instruction_text);
        $len_of_warning = mb_strlen($instruction_warning);
        
        //check text
        $values = $text_conversioner->check_text(Instruction::INFO, $this->font_size_of_info, $full_text);
        
        //get back updated font and text (if it was needed)
        $new_font_size_of_info = $values["font"];
        $full_text = $values["text"]; 
        
        //calculate start position of each string
        $instruction_header_start = 0;
        $instruction_text_start = $len_of_header;
        $instruction_warning_start = $len_of_header + $len_of_text; 
        
        //separate each part of text back
        $instruction_header = substr($full_text, $instruction_header_start, $len_of_header);
        $instruction_text = substr($full_text, $instruction_text_start, $len_of_text);
        $instruction_warning = substr($full_text, $instruction_warning_start, $len_of_warning);
          
        $pdf->writeHTML('<p style="font-size: '.$new_font_size_of_info.'pt; margin-bottom: 10;">
                        <span style="font-weight: bold; font-family: '.$this->bold_font_html.';">'.$instruction_header.': 
                        </span>'.$instruction_text.' <span style="color: red">'.$instruction_warning.'</span></p>');
        $pdf->Ln(6);
        return $pdf;
    }
    
    //
    //get header for evaluation pdf document
    //$pdf - tcpdi class
    //$text_conversioner - our text conversioner
    //$review_ID - id of review
    //$name_of_submission - name of reviewed submission
    //
    //return $pdf - tcpdi class
    function evaluation_header($pdf, $text_conversioner, $review_ID, $name_of_submission) {        
        $values = $text_conversioner->check_text(Instruction::HEADER_TITLE, $this->font_size_of_header_title, $name_of_submission);
        
        $new_font_size_of_header_title = $values["font"];
        $name_of_submission = $values["text"];
        
        $pdf->setFont($this->bold_font_tcpdf, 'B', $this->font_size_of_title_info);
        $pdf->Cell(13, 5, 'REVIEW ID #'.$review_ID.' : ');
        $pdf->setFont($this->bold_font_tcpdf, 'B', $new_font_size_of_header_title);
        $pdf->SetXY(65, 10);
        $pdf->Cell(10, 5, $name_of_submission);
        $pdf->use_default_font(); 
        $pdf->Ln(12);
        $pdf = $this->horizontal_bar($pdf);
        $pdf = $this->putLogo($pdf);
                            
        return $pdf;
    }
    
    //put tsd logo into header
    //$pdf - tcpdi class
    //
    //return $pdf - tcpdi class
    function putLogo($pdf) {                            
		    $pdf->Image($this->image_path, $this->logo_x, $this->logo_y, $this->logo_w, $this->logo_h, 'PNG', '', '', false, 300, '', false, false, 0, $this->logo_fitbox, false, false);  
        return $pdf;
    }
    
    
    //get title for evalutation pdf document
    //$pdf - tcpdi class
    //$text_conversioner - our text conversioner
    //$submission_ID - id of reviewed submission
    //$name_of_submission - name of reviewed submission
    //$name_of_reviewer - name of reviewer
    //
    //return $pdf - tcpdi class
    function evaluation_review_title($pdf, $text_conversioner, $submission_ID, $name_of_submission, $name_of_reviewer) {    
        $values = $text_conversioner->check_text(Instruction::TITLE, $this->font_size_of_title, $name_of_submission);
        
        $new_font_size_of_title = $values["font"];
        $name_of_submission = $values["text"];
        
        
        $values = $text_conversioner->check_text(Instruction::REVIEWER_NAME, $this->font_size_of_name, $name_of_reviewer);
        
        $new_font_size_of_name = $values['font'];
        $name_of_reviewer = $values['text'];
        
        $pdf->setFont($this->bold_font_tcpdf, 'B', $this->font_size_of_title_info);
        $pdf->Cell(0, 9, 'Offline Review Form for Submission S-ID #'.$submission_ID, 0, false, 'C', 0, '', 0, false, 'T', 'M'); 
        $pdf->Ln(12);
        
        $pdf->setFont($this->bold_font_tcpdf, 'B', $new_font_size_of_title);
        $pdf->Cell(0, 9, $name_of_submission, 0, false, 'C', 0, '', 0, false, 'T', 'M'); 
        $pdf->Ln(16);
        
        $pdf->setFont($this->bold_font_tcpdf, 'B', $new_font_size_of_name);
        $pdf->Cell(0, 9, 'Review by '.$name_of_reviewer, 0, false, 'C', 0, '', 0, false, 'T', 'M'); 
        $pdf->Ln(12);
        
        $pdf->use_default_font();              
        $pdf->Ln(6);
                                                    
        return $pdf;
    }     
    
    
    //load submission file (pdf file) and import pages after review formula
    //$pdf - tcpdi class 
    //$text_conversioner - our text conversioner
    //$review_ID - id of review
    //$name_of_submission - name of reviewed submission
    //$path_to_file - path to submission file
    //
    //return $pdf - tcpdi class
    function load_submission($pdf, $text_conversioner, $review_ID, $name_of_submission, $path_to_file) { 
        $numPages = $pdf->setSourceFile($path_to_file);
        for ($i = 1; $i <= $numPages; $i++) {
            $tplIdx = $pdf->importPage($i);
            $pdf->SetPrintHeader(false);
            $pdf->SetPrintFooter(false);
            $pdf->AddPage();
            $pdf = $this->evaluation_header($pdf, $text_conversioner, $review_ID, $name_of_submission);
            $pdf->useTemplate(
                $tplIdx, $x = null, $y = null, 
                $w = 0, $h = 0, $adjustPageSize = true
            );
            $pdf = $this->add_watermark($pdf);  
        }
    
        return $pdf;
    }

    //write RID and SID into metadata
    //$pdf - tcpdi class
    //$rid - review id
    //$sid - submission id
    //
    //return $pdf - tcpdi class
    function set_hidden_RID_and_SID($pdf, $rid, $sid) {
        $pdf->SetKeywords($sid.' '.$rid);
        return $pdf; 
    }            
    
    //add watermark into pdf page
    //$pdf - tcpdi class
    //
    //return $pdf - tcpdi class
    function add_watermark($pdf) {
        // Get the page width/height
        $myPageWidth = $pdf->getPageWidth();
        $myPageHeight = $pdf->getPageHeight();

        // Find the middle of the page and adjust.
        $myX = ( $myPageWidth / 2 ) - 65;
        $myY = ( $myPageHeight / 2 ) + 10;

        // Set the transparency of the text to really light
        $pdf->SetAlpha(0.05);

        // Rotate 45 degrees and write the watermarking text
        $pdf->StartTransform();
        $pdf->Rotate(45, $myX, $myY);
        $pdf->SetFont($this->watermark_font_type, $this->watermark_font_style, $this->watermark_font_size);
        
        //$pdf->Image(K_PATH_IMAGES.'SACS.png', $myX, $myY, $ImageW, $ImageH, '', '', '', true, 150);
        $pdf->Text($myX, $myY ,trim($this->watermark_text));
        $pdf->StopTransform();

        // Reset the transparency to default
        $pdf->SetAlpha(1); 
        
        $pdf->use_default_font();       
        return $pdf;
    }                                                

}
?>