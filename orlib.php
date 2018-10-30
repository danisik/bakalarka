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
//		Last update on:		23-10-2018
//      Encoding: utf-8 no BOM
//

if (@$_GET['func'] == 'generate') {
    $submissionID = 9;
    $reviewID = 1;      
    $nameOfSubmission = 'Survey of Business Perception Based on Sentiment Analysis through Deep Neuronal Networks for Natural Language Processing';
    $nameOfReviewer = 'Kamil Ekštein';
    generate_offline_review_form($reviewID, $nameOfReviewer, $submissionID, $nameOfSubmission, $_SERVER['DOCUMENT_ROOT'].'/TSD/pdf.pdf');
}
else if (@$_GET['func'] == 'parse') {
    //path to working directory
    $root = $_SERVER['DOCUMENT_ROOT'].'/';
    //path to mpdf in working directory
    $path = $root.'TSD/';
    
    if ($_GET['type'] == 'old') process_offline_review_form('', '', $path.'TSD2017_Review_Form_383.pdf');  
    else if ($_GET['type'] == 'mpdf') process_offline_review_form('', '', $path.'TSD2019_Review_Form_1.pdf'); 
} 

//
//generate review pdf form with form elements
//$rid - review id
//$reviewer_name - name of reviewer
//$sid - submission id
//$submission_name - name of submission to be reviewed
//$submission_filename - path to submission
//
function generate_offline_review_form($rid, $reviewer_name, $sid, $submission_name, $submission_filename) {

    mb_internal_encoding('UTF-8');
    //path to working directory
    $root = $_SERVER['DOCUMENT_ROOT'].'/';
    //path to mpdf in working directory
    $path = $root.'TSD/';
    //path for source files
    $path_source = $path.'src/';
    //path to mpdf library
    $path_MPDF = $path.'mpdf/';
    //path to configuration file
    $configuration_path = $path.'config/configuration.xml';
    //path to logo
    $path_to_logo = $path.'img/tsd-logo.png';
    //path to css
    $path_to_css = $path.'css/style.css';

    //including our classes
    include ($path_source.'Enumerates.php');
    include ($path_source.'OwnXmlReader.php');
    include ($path_source.'TextConversion.php');
    include ($path_source.'HTMLElements.php');
    require ($path_MPDF.'vendor/autoload.php');
    
    
    //mpdf class, creating pdf from html code
    $mpdf = setMPDF();
     
    //our class with text conversion and calculating font size
    $text_conversioner = new TextConversioner;
    //our class with printing form elements (html code)
    $elements = new HTMLElements;
    //our class with read xml file and get values
    $xml_reader = new OwnXmlReader;
    $xml_reader->read_XML_file($configuration_path);

    //year of actual conference
    $year_of_conference = $xml_reader->getYear_of_conference();
    //text for watermark
    $watermark_text = $xml_reader->getWatermark_text();

    //info text where to upload review
    $submission_upload_info = 'After filling the form in, please, upload it to the TSD'.$year_of_conference.' web review application: Go to URL
        <span style="display: inline; text-decoration: underline; color: blue;">https://www.kiv.zcu.cz/tsd'.$year_of_conference.' </span>and after logging in, please, proceed to section '."'My Reviews'".', select the corresponding
        submission and press the '."'Review'".' button. There, you'."'".'ll be able to upload this PDF file.';
        
    $filename = 'TSD'.$year_of_conference.'_Review_Form_'.$rid.'.pdf';


    //set css file for pdf
    $stylesheet = file_get_contents($path_to_css);
    $mpdf->WriteHTML($stylesheet,1);
    
    //set header for all pages (text)
    $mpdf->SetHTMLHeader($elements->evaluation_header($text_conversioner, $rid, $submission_name));
    
    $textarea_info = TextAreaInfo::getConstants();
    
    //first template page
    //set rid and sid into document (hidden, easy to get rid and sid when parsing pdf document)
    $html = set_hidden_RID_and_SID($rid, $sid);
    $html .= create_header_image($path_to_logo);
    $html .= create_first_template_page($elements, $text_conversioner, $xml_reader, $sid, $submission_name, $reviewer_name, RadiobuttonInfo::Count_of_evaluations_to, $textarea_info);
    //write first page of evaluation
    //echo $html;    
    $mpdf->WriteHTML($html);
    //add second page - because if instructions does not exist, textareas from second page are inserted into first page 
    $mpdf->AddPage();
     //echo $html;
    //second template page
    //reset html code
    $html = ''; 
    $html .= create_header_image($path_to_logo);    
    $html .= create_second_template_page($elements, $submission_upload_info, $textarea_info);
    $mpdf->WriteHTML($html);

    $mpdf->AddPage();
    //set watermark for submission
    $mpdf = setWatermark($mpdf, $watermark_text);
    //load submission and import it after review form
    $mpdf = load_submission($mpdf, $submission_filename, $path_to_logo);
    //create pdf
    
    if ($_GET['type'] == 'preview') $mpdf->Output();  
    else if ($_GET['type'] == 'download') $mpdf->Output($filename,'D');  
}

//
//extract values from review pdf form
//$rid - review id
//$sid - submission id
//$revform_filename - filename of review form (pdf file)
//
function process_offline_review_form($rid, $sid, $revform_filename) {

    //path to working directory
    $root = $_SERVER['DOCUMENT_ROOT'].'/';
    //path to mpdf in working directory
    $path = $root.'TSD/';
    //path for source files
    $path_source = $path.'src/';
    //path to pdfparser library
    $path_PDF_parser = $path.'pdfparser/';
    require ($path_PDF_parser.'vendor/autoload.php');
    include($path_source.'Enumerates.php');
    
    $parser = new \Smalot\PdfParser\Parser();                            
    $pdf = $parser->parseFile($revform_filename);
    
    $data = $pdf->getFormElementsData();
    //print_r ($pdf);
    $groups = $data['groups'];
    $textareas = $data['textareas'];
    
    $values = array(
        RadiobuttonInfo::Groups_text => array(),
        TextareaInfo::Textareas_text => array()    
    );
    
    $invalid_values = array(
                        'element' => '',
                        'value' => '');
    $invalid_indicator = 0;
        
    $radiobuttons_constant = RadiobuttonInfo::getConstants();
    
    $i = 0;                                                                   
    foreach ($groups as $key => $value) {
        $valid = check_if_element_is_valid($value, FormElements::RADIOBUTTON);
        $element = '';
        
        switch($i) {   
            case RadiobuttonInfo::Originality_ID: 
                $element = RadiobuttonInfo::Originality; 
                $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Originality] = $value;                
                echo (RadiobuttonInfo::Originality.' - '.$value.'<br>');
                break;                
            case RadiobuttonInfo::Significance_ID: 
                $element = RadiobuttonInfo::Significance;
                $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Significance] = $value;
                echo (RadiobuttonInfo::Significance.' - '.$value.'<br>');
                break;            
            case RadiobuttonInfo::Relevance_ID:
                $element = RadiobuttonInfo::Relevance;
                $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Relevance] = $value;
                echo (RadiobuttonInfo::Relevance.' - '.$value.'<br>');
                break;                
            case RadiobuttonInfo::Presentation_ID:
                $element = RadiobuttonInfo::Presentation;
                $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Presentation] = $value;
                echo (RadiobuttonInfo::Presentation.' - '.$value.'<br>');
                break;                
            case RadiobuttonInfo::Technical_quality_ID:
                $element = RadiobuttonInfo::Technical_quality;
                $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Technical_quality] = $value;
                echo (RadiobuttonInfo::Technical_quality.' - '.$value.'<br>');                
                break;                
            case RadiobuttonInfo::Overall_rating_ID:
                $element = RadiobuttonInfo::Overall_rating;
                $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Overall_rating] = $value;
                echo (RadiobuttonInfo::Overall_rating.' - '.$value.'<br>');
                break;            
            case RadiobuttonInfo::Amount_of_rewriting_ID:
                $element = RadiobuttonInfo::Amount_of_rewriting;
                $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Amount_of_rewriting] = $value;
                echo (RadiobuttonInfo::Amount_of_rewriting.' - '.$value.'<br>');
                break;                
            case RadiobuttonInfo::Reviewers_expertise_ID:
                $element = RadiobuttonInfo::Reviewers_expertise;
                $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Reviewers_expertise] = $value;
                echo (RadiobuttonInfo::Reviewers_expertise.' - '.$value.'<br>');
                break;
        }         
        
        if (!$valid) {
            $invalid_constants[$invalid_indicator]['element'] = $element;
            $invalid_constants[$invalid_indicator]['value'] = $value;
            $invalid_indicator++;
        }
        
        $i++;
    }
    
    
    $textareas_constant = TextareaInfo::getConstants();
    
    echo ('<br>');
    $i = 0;
    foreach ($textareas as $key => $value) {
    
        @$valid = check_if_element_is_valid($value, FormElements::TEXTAREA, $textareas_constant[$i]['needed']);
        $element = '';
        switch($i) {
            case TextareaInfo::Main_contributions_ID: 
                $element = TextareaInfo::Main_contributions;  
                $values[TextareaInfo::Textareas_text][TextareaInfo::Main_contributions]['value'] = $value;
                echo (TextareaInfo::Main_contributions.' - '.$value.'<br>');
                break;                
            case TextareaInfo::Positive_aspects_ID:
                $element = TextareaInfo::Positive_aspects;
                $values[TextareaInfo::Textareas_text][TextareaInfo::Positive_aspects]['value'] = $value;
                echo (TextareaInfo::Positive_aspects.' - '.$value.'<br>');
                break;            
            case TextareaInfo::Negative_aspects_ID:
                $element = TextareaInfo::Negative_aspects;
                $values[TextareaInfo::Textareas_text][TextareaInfo::Negative_aspects]['value'] = $value;
                echo (TextareaInfo::Negative_aspects.' - '.$value.'<br>');
                break;                
            case TextareaInfo::Comment_ID:
                $element = TextareaInfo::Comment;
                $values[TextareaInfo::Textareas_text][TextareaInfo::Comment]['value'] = $value;
                echo (TextareaInfo::Comment.' - '.$value.'<br>');
                break;                
            case TextareaInfo::Internal_comment_ID:
                $element = TextareaInfo::Internal_comment;
                $values[TextareaInfo::Textareas_text][TextareaInfo::Internal_comment]['value'] = $value;
                echo (TextareaInfo::Internal_comment.' - '.$value.'<br>');                
                break;                
        }              
        
        
        if (!$valid) {
            $invalid_constants[$invalid_indicator]['element'] = $element;
            $invalid_constants[$invalid_indicator]['value'] = $value;
            $invalid_indicator++;
        }                                   
        
        $i++;                                                              
    }                              

    echo ('<br>');
    foreach($invalid_constants as $key => $value) {
        if (strlen($value['value']) == 0) $value['value'] = 'N/A';
        echo ('Error -  invalid element: '.$value['element'].', value: '.$value['value'].'<br>');
    }
    
    if (sizeof($invalid_constants) == 0) {
        upload_to_DB_offline_review_form($rid, $values);
    }
    else {
        set_failure("review_details_fail", "<b>Unable to process the offline review form</b> " .
			"for Review ID# $rid, all required fields must be filled...",
			DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
		return TRUE;
    }
    
}

//
//upload evaluation values into database
//$rid - review id
//$values - array with all values writed below this
//
//$originality - how is the work original
//$significance - how is the work significant 
//$relevance - how is the work relevant                                    
//$presentation - presentation of the work 
//$technical_quality - technical quality of the work 
//$total_rating - work as a whole
//$rewriting_amount - how much of the work should be rewritten
//$reviewer_expertise - confidence about ranking 
//$main_contrib - main contribution summarisation 
//$pos_aspects - positive aspects of submission
//$neg_aspects - negative aspects of submission 
//$rev_comment - reviewer comment, displaying for submissioner
//$int_comment - internal comment, displaying only for admins
// 
function upload_to_DB_offline_review_form($rid, $values) {									
    //path to working directory
    $root = $_SERVER['DOCUMENT_ROOT'].'/';
    //path to mpdf in working directory
    $path = $root.'TSD/';
    //path for source files
    $path_source = $path.'src/';                                    
                                                                                      
   $qry = db_get('state', 'reviews', "`id`='" . safe($rid) . "'");
   $qry = null;
   if (!$qry) {
	  error("Database error", "The database server returned an error: " . db_error(get_session_var('dblink')),
		 DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
	  return TRUE;
   }
   else if ($qry === "F") {
	  set_failure("review_details_fail", "<b>Unable to process the offline review form</b> " .
		 "for Review ID# $rid, this review has been marked as finished...",
		 DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
	  return TRUE;
   }
	
   $qstr = sprintf("UPDATE `reviews` SET `state`='U', `originality` = '" . safe($originality) .
	  "', `significance` = '" . safe($significance) . "', `relevance` = '" . safe($relevance) .
	  "', `presentation` = '" . safe($presentation) . "', `technical_quality` = '" . safe($technical_quality) .
	  "', `total_rating` = '" . safe($total_rating) . "',	`rewriting_amount` = '" . safe($rewriting_amount) .
	  "', `reviewer_expertise` = '" . safe($reviewer_expertise) . "', `main_contrib` = '" . safe($main_contrib) .
	  "', `pos_aspects` = '" . safe($pos_aspects) . "', `neg_aspects`= '" . safe($neg_aspects) .
	  "', `int_comment` = '".safe($int_comment)."', `rev_comment` = '" . safe($rev_comment) .
	  "' WHERE `id`='" . safe($rid) . "'");
		
   if (!$qstr) {
	  set_failure("review_details_fail", "<b>Unable to process the offline review form</b> " .
		 "for Review ID# $rid. The review contains incompatible/unprocessable characters...",
		 DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
   	   return TRUE;
   }
	
   $qry = db_query($qstr);
   if (!$qry){
	  error("Database error", "Error while storing the data from the offline review form into the database. " .
		 "The database server returned this error: " . db_error(get_session_var('dblink')),
	      DOC_ROOT . "/index.php?form=review-details&rid=" . $rid) ;
	  return TRUE;
	}
	
	set_message("review_details_info", "<b>The uploaded offline review form for Review ID# $rid was successfully processed</b>..." ,
		DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);

	return TRUE;	
}

//
//inicialize mpdf class variable
//
//return $mpdf - mpdf class, creating pdf from html code  
function setMPDF() {
    $mpdf = new \Mpdf\Mpdf([
	   'margin_top' => 30,
	   'margin_header' => 10,
	   'margin_footer' => 10,
        'default_font' => 'helvetica'
    ]);
    //editable form elements
    $mpdf->useActiveForms = true;
    
    return $mpdf;
}


//
//write RID and SID into document (hidden in document)
//$rid - review id
//$sid - submission id
//
//return $hidden - input type hidden contains rid and sid
function set_hidden_RID_and_SID($rid, $sid) {
    $hidden = '<input type="hidden" name="rid" value="'.$rid.'" />';
    $hidden .= '<input type="hidden" name="sid" value="'.$sid.'" />';
    
    return $hidden; 
}

//
//set watermark text into pdf
//$mpdf - mpdf class
//$watermark_text - text for watermark
//
//return $mpdf - mpdf with watermark text set 
function setWatermark($mpdf, $watermark_text) {
    $mpdf->SetWatermarkText($watermark_text, 0.1);
    $mpdf->showWatermarkText = true; 
    return $mpdf;
}

//
//load submission file (pdf file) and import pages after review formula
//$mpdf - mpdf class, creating pdf from html code  
//$path_to_file - path to submission file
//$path_to_logo - path to tsd logo
//
function load_submission($mpdf, $path_to_file, $path_to_logo) { 
    $mpdf->SetImportUse();
    $page_count = $mpdf->SetSourceFile($path_to_file);
    for($i = 1; $i <= $page_count; $i++) {            
        $template = $mpdf->ImportPage($i);
        $mpdf->UseTemplate($template);
        $mpdf->WriteHTML(create_header_image($path_to_logo));
        if($i < $page_count) $mpdf->AddPage();
    }
    return $mpdf;
}

//
//create image in position of header
//$path_to_logo - path to tsd logo
//
function create_header_image($path_to_logo) {

    $image = '<div style="position: absolute; top: 5; right: 0; width: 120;">';
    $image .= '<img src="'.$path_to_logo.'"/>';
    $image .= '</div>';
        
    return $image;
}

//
//create first template page
//$elements - our class creating html elements
//$text_conversioner - our text conversioner
//$xml_reader - our xml reader 
//$sid - submission id
//$submission_name - name of reviewed submission                      
//$reviewer_name - name of current reviewer 
//$count_of_evaluations_to - how many radiobuttons we want in evaluation
//$textarea_info - enumerate contains info about textareas
//
//return $firstPage - html code of first template page
function create_first_template_page($elements, $text_conversioner, $xml_reader, $sid, $submission_name, $reviewer_name, $count_of_evaluations_to, $textarea_info) {
    //document title
    $first_page = $elements->evaluation_review_title($text_conversioner, $sid, $submission_name, $reviewer_name);
    //info
    $first_page .= $elements->evaluation_instructions($text_conversioner, $xml_reader);
    $first_page .= '<form id="groups">';
    
    $radio_button_info = RadioButtonInfo::getConstants();
    
    for ($i = 0; $i < count($radio_button_info); $i++) {
        $name = $radio_button_info[$i]['name'];
        $info = $radio_button_info[$i]['info'];
        $first_page .= $elements->evaluation_radio_buttons($name, $info, $count_of_evaluations_to, $i);
    }
    
    $first_page .= '</form>';
                  
    $first_page .= '<hr style="margin: 10;"/>';
                              
    $name_main = $textarea_info[0]['name'];
    $info_main = $textarea_info[0]['info'];
    $first_page .= $elements->evaluation_textarea($name_main, $info_main, TextAreaInfo::Main_contributions_ID, 10, 87);
    
    return $first_page;
}

//
//create second template page
//$elements - our class creating html elements
//$submission_upload_info - info about what to do after filling the form
//$textarea_info - enumerate contains info about textareas
//
//return $secondPage - html code of second template page
function create_second_template_page($elements, $submission_upload_info, $textarea_info) {
    $name_positive = $textarea_info[1]['name'];
    $info_positive = $textarea_info[1]['info'];
    
    $name_negative = $textarea_info[2]['name'];
    $info_negative = $textarea_info[2]['info'];
    
    $name_comment = $textarea_info[3]['name'];
    $info_comment = $textarea_info[3]['info'];
    
    $name_internal = $textarea_info[4]['name'];
    $info_internal = $textarea_info[4]['info'];
    
    $second_page = $elements->evaluation_textarea($name_positive, $info_positive, TextAreaInfo::Positive_aspects_ID, 10, 87);
    $second_page .= $elements->evaluation_textarea($name_negative, $info_negative, TextAreaInfo::Negative_aspects_ID, 10, 87);
    
    $second_page .= $elements->evaluation_textArea($name_comment, $info_comment, TextAreaInfo::Comment_ID, 10, 87);
    $second_page .= $elements->evaluation_textArea($name_internal, $info_internal, TextAreaInfo::Internal_comment_ID, 10, 87);
    $second_page .= '<p>'.$submission_upload_info.'</p>';
    
    return $second_page;
}

//
//check form element, if his value is valid (for group of radiobutton must me selected one button, for textarea must be writed some text, if is set as needed)
//$elementValue - value of sent element
//$elementType - type of element (rbutton, textarea)
//$elementNeeded - if this element is needed (only for textarea)
//
//return $valid - true/false depends if element value is correct
function check_if_element_is_valid($elementValue, $elementType, $elementNeeded = false) {
    $valid = true;      
    switch($elementType) {
        case FormElements::RADIOBUTTON:
            if($elementValue == 'Off') $valid = false;
            break;
        case FormElements::TEXTAREA:
            if($elementValue == '' && $elementNeeded == true) $valid = false;
            break;
    }
    
    return $valid;
} 


?>