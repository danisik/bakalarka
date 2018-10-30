<?php

//
//		CONFERENCE PORTAL PROJECT
//		VERSION 3.0.6
//
//		Copyright (c) 2010-2018 Dept. of Computer Science & Engineering,
//		Faculty of Applied Sciences, University of West Bohemia in Plzeň.
//		All rights reserved.
//
//		Code written by:	Vojtěch Danišík
//		Last update on:		30-10-2018
//      Encoding: utf-8 no BOM
//

//
//      Class for printing form elements (html code)
//
 
class HTMLElements {
    
    //
    //print radiobutton into pdf
    //$name_of_evaluation - header for info
    //$evaluation_info - info about purpose of this group of radiobuttons
    //$count_of_rankings - scale of evaluations
    //$group_ID - id of group
    //
    //return $radio - html text contains radio buttons
    function evaluation_radio_buttons($name_of_evaluation, $evaluation_info, $count_of_rankings, $group_ID){   
        $radio = '';
        $radio .= '<p id="radiobutton"><span id="bold_info">'.$name_of_evaluation.'</span> – '.$evaluation_info.':</p>';
        $radio .= '<p id="margin_02">';
        $radio .= '<fieldset id="group'.$group_ID.'">';
        for($i = 0; $i <= $count_of_rankings; $i++) {
            $radio .= '<input type="radio" name="group'.$group_ID.'" value="'.$i.'">  '.$i.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        } 
        $radio .= '</fieldset>'; 
        $radio .= '</p>';
        return $radio;                                                                                                 
    }
    
    //
    //print textarea into pdf
    //$textarea_header - header for info
    //$textarea_info - for what purpose is this textarea
    //$textarea_ID - id of textarea
    //$rows - how many rows textarea have to 
    //$cols - how many cols textarea have to
    //$textarea_text - text to be displayed in textarea by default 
    //
    //return $area - html text contains textarea
    function evaluation_textarea($textarea_header, $textarea_info, $textarea_ID, $rows, $cols, $textarea_text = '') {
        //char a, because mpdf have some internal 'problem' (maybe) to display textarea without declared text, so we declare some string and then 
        //in file src/Mpdf.php in method "printobjectbuffer" text is set as empty string
        if($textarea_text == '') $textarea_text = 'a';
        
        $area = '';
        $area .= '<p id="textarea"><span id="bold_info">'.$textarea_header.'</span> – '.$textarea_info.': </p>';                        
        $area .= '<textarea name="textarea'.$textarea_ID.'" rows="'.$rows.'" cols="'.$cols.'">'.$textarea_text.'</textarea>';
        return $area;
    } 
    
    //
    //get header for evaluation pdf document
    //$text_conversioner - variable of our class for text conversion
    //$review_ID - id of review
    //$name_of_submission - name of reviewed submission
    //
    //return $header - html text contains header for document
    function evaluation_header($text_conversioner, $review_ID, $name_of_submission) {
    
        $font_size_of_review_ID = 23;
        $font_size_of_header_title = 20;
        
        $values = $text_conversioner->check_text(Instruction::HEADER_TITLE, $font_size_of_header_title, $name_of_submission);
        
        $font_size_of_header_title = $values["font"];
        $name_of_submission = $values["text"];
        
        $header = '<p><span id="evaluation_header" style="font-size: '.$font_size_of_review_ID.'pt;">REVIEW ID #'.$review_ID.' : </span>';
        $header .= '<span id="evaluation_header" style="font-size: '.$font_size_of_header_title.'pt;">'.$name_of_submission.'</span></p>';
        $header .= '<hr/>';
        return $header;
    }
    
    //
    //get title for evalutation pdf document
    //$text_conversioner - variable of our class for text conversion
    //$submission_ID - id of reviewed submission
    //$name_of_submission - name of reviewed submission
    //$name_of_reviewer - name of reviewer
    //
    //return $title - html text contains title for document
    function evaluation_review_title($text_conversioner, $submission_ID, $name_of_submission, $name_of_reviewer) {
        $font_size_of_title_info = 17;
        $font_size_of_title = 30;
        $font_size_of_name = 17;
        
        $values = $text_conversioner->check_text(Instruction::TITLE, $font_size_of_title, $name_of_submission);
        
        $font_size_of_title = $values["font"];
        $name_of_submission = $values["text"];
        
        
        $values = $text_conversioner->check_text(Instruction::REVIEWER_NAME, $font_size_of_name, $name_of_reviewer);
        
        $font_size_of_name = $values['font'];
        $name_of_reviewer = $values['text'];
        
        
        $title = '';
        $title .= '<p id="evaluation_title" style="font-size: '.$font_size_of_title_info.'pt;">Offline Review Form for Submission S-ID #'.$submission_ID.'</p>';
        $title .= '<p id="evaluation_title" style="font-size: '.$font_size_of_title.'pt;">'.$name_of_submission.'</p>';
        $title .= '<p id="evaluation_title" style="font-size: '.$font_size_of_name.'pt;">Review by '.$name_of_reviewer.'</p>';
        return $title;    
    }
    
    //
    //get instructions for evaluation
    //$text_conversioner - variable of our class for text conversion
    //$xml_reader - ownXmlReader contains instructions
    //
    //return $instructions - html text contains instruction text below title
    function evaluation_instructions($text_conversioner, $xml_reader) {
        $font_size_of_info = 12;
        //get text from XML
        $instruction_header = $xml_reader->getInstruction_header();
        $instruction_text = $xml_reader->getInstruction_text();
        $instruction_warning = $xml_reader->getInstruction_warning();
        
        //unite all info to one text
        $full_text = $instruction_header.$instruction_text.$instruction_warning;
        
        //calculate length of each part of info
        $len_of_header = mb_strlen($instruction_header);
        $len_of_text = mb_strlen($instruction_text);
        $len_of_warning = mb_strlen($instruction_warning);
        
        //check text
        $values = $text_conversioner->check_text(Instruction::INFO, $font_size_of_info, $full_text);
        
        //get back updated font and text (if it was needed)
        $font_size_of_info = $values["font"];
        $full_text = $values["text"]; 
        
        //calculate start position of each string
        $instruction_header_start = 0;
        $instruction_text_start = $len_of_header;
        $instruction_warning_start = $len_of_header + $len_of_text; 
        
        //separate each part of text back
        $instruction_header = substr($full_text, $instruction_header_start, $len_of_header);
        $instruction_text = substr($full_text, $instruction_text_start, $len_of_text);
        $instruction_warning = substr($full_text, $instruction_warning_start, $len_of_warning);
          
        $instructions = '';
        $instructions .= '<p id="evaluation_instructions" style="font-size: '.$font_size_of_info.'pt">';
        $instructions .= '<span id="bold_info">'.$instruction_header.': </span>'.$instruction_text.' <span style="color: red">'.$instruction_warning.'</span>';
        $instructions .= '</p>';
        return $instructions;
    }   
} 
?>