<?php

    error_reporting(-1);
ini_set('display_errors', 'On');

//
//		CONFERENCE PORTAL PROJECT
//		VERSION 3.0.6
//
//		Copyright (c) 2010-2019 Dept. of Computer Science & Engineering,
//		Faculty of Applied Sciences, University of West Bohemia in Plzeň.
//		All rights reserved.
//
//		Code written by:	Vojtech Danisik
//		Last update on:		21-02-2019
//      Encoding: utf-8 no BOM
//

define('DOC_TSD_ROOT', $_SERVER['DOCUMENT_ROOT'].'/tsd2019/');
define('DOC_MY_ROOT', DOC_TSD_ROOT.'php/');
define('DOC_GP_ROOT',DOC_MY_ROOT.'offline-review/');
define('DOC_GP_LIB', DOC_GP_ROOT.'lib/');
define('DOC_GP_SOURCE', DOC_GP_ROOT.'src/');
define('DOC_GP_MPDF', DOC_GP_LIB.'mpdf/');
define('DOC_GP_CONFIGURATION', DOC_GP_ROOT.'config/configuration.xml');
define('DOC_GP_IMG_LOGO', DOC_GP_ROOT.'img/tsd-logo.png');
define('DOC_GP_CSS', DOC_GP_ROOT.'css/style.css');
define('DOC_GP_PARSER', DOC_GP_LIB.'pdfparser/');

//
//generate review pdf form with form elements
//$rid - review id
//$reviewer_name - name of reviewer
//$sid - submission id
//$submission_name - name of submission to be reviewed
//$submission_filename - path to submission
function generate_offline_review_form($rid, $reviewer_name, $sid, $submission_name, $submission_filename) {

    mb_internal_encoding('UTF-8');
    //including our classes
    include (DOC_GP_SOURCE.'Enumerates.php');
    include (DOC_GP_SOURCE.'OwnXmlReader.php');
    include (DOC_GP_SOURCE.'TextConversion.php');
    include (DOC_GP_SOURCE.'HTMLElements.php');
    require (DOC_GP_MPDF.'vendor/autoload.php');

    //mpdf class, creating pdf from html code
    $mpdf = setMPDF();
    //our class with text conversion and calculating font size
    $text_conversioner = new TextConversioner;
    //our class with printing form elements (html code)
    $elements = new HTMLElements;
    //our class with read xml file and get values
    $xml_reader = new OwnXmlReader;
    $xml_reader->read_XML_file(DOC_GP_CONFIGURATION);
    //year of actual conference
    $year_of_conference = $xml_reader->getYear_of_conference();
    //text for watermark
    $watermark_text = $xml_reader->getWatermark_text();
    //info text where to upload review
    $submission_upload_info = 'After filling the form in, please, upload it to the TSD'.$year_of_conference.' web review application: Go to URL
        <span style="display: inline; text-decoration: underline; color: blue;">https://www.kiv.zcu.cz/tsd'.$year_of_conference.' </span>and after logging in, please, proceed to section '."'My Reviews'".', 
        select the corresponding submission and press the '."'Review'".' button. There, you'."'".'ll be able to upload this PDF file.';
        
    $filename = 'TSD'.$year_of_conference.'_Review_Form_'.$rid.'.pdf';
  
    //set css file for pdf
    $stylesheet = file_get_contents(DOC_GP_CSS);
    $mpdf->WriteHTML($stylesheet,1);
    
    //set header for all pages (text)
    $mpdf->SetHTMLHeader($elements->evaluation_header($text_conversioner, $rid, $submission_name));
    
    $textarea_info = TextareaInfo::getConstants();
    
    //set watermark for submission
    $mpdf = setWatermark($mpdf, $watermark_text);
    
    //first template page
    //set rid and sid into document (hidden, easy to get rid and sid when parsing pdf document)
    $html = set_hidden_RID_and_SID($rid, $sid);
    $html .= create_header_image(DOC_GP_IMG_LOGO);
    $html .= create_first_template_page($elements, $text_conversioner, $xml_reader, $sid, $submission_name, $reviewer_name, RadiobuttonInfo::Count_of_evaluations_to, $textarea_info);
    //write first page of evaluation   
    $mpdf->WriteHTML($html);
    //add second page - because if instructions does not exist, textareas from second page are inserted into first page 
    $mpdf->AddPage();
    //second template page
    //reset html code
    $html = ''; 
    $html .= create_header_image(DOC_GP_IMG_LOGO);    
    $html .= create_second_template_page($elements, $submission_upload_info, $textarea_info);
    $mpdf->WriteHTML($html);

    $mpdf->AddPage();
    //load submission and import it after review form
    $mpdf = load_submission($mpdf, DOC_TSD_ROOT.$submission_filename, DOC_GP_IMG_LOGO);
    //create pdf
    //$mpdf->Output();
    $mpdf->Output($filename, 'D');
    header("Location: " . DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
}

//
//extract values from review pdf form
//$rid - review id
//$sid - submission id
//$revform_filename - filename of review form (pdf file)
//
//return boolean value
function process_offline_review_form($rid, $sid, $revform_filename) {
    require (DOC_GP_PARSER.'vendor/autoload.php');
    include (DOC_GP_SOURCE.'Enumerates.php');

    
    $parser = new \Smalot\PdfParser\Parser();                       
    $pdf = $parser->parseFile($revform_filename);
    $data = $pdf->getFormElementsData();

    $groups = $data[RadiobuttonInfo::Groups_text];
    $textareas = $data[TextareaInfo::Textareas_text];
    $values = array(
        RadiobuttonInfo::Groups_text => array(),
        TextareaInfo::Textareas_text => array()    
    );
    $invalid_constants = array(
                        'element' => '',
                        'value' => '');
    $invalid_indicator = 0;
        
    $radiobuttons_constant = RadiobuttonInfo::getConstants();                                               
    foreach ($groups as $key => $value) {
    
        //replacing radiobutton group for blank space (name of each radiobutton group is: groupID) to get ID of radiobutton
        $key = str_replace(RadioButtonInfo::Group_text, "", $key);
    
        $valid = check_if_element_is_valid($value, FormElements::RADIOBUTTON);
        $element = '';
        
        switch($key) {   
            case RadiobuttonInfo::Originality_ID: 
                $element = RadiobuttonInfo::Originality; 
                $values[RadiobuttonInfo::Groups_text][$element]['value'] = $value;                
                break;                
            case RadiobuttonInfo::Significance_ID: 
                $element = RadiobuttonInfo::Significance;
                $values[RadiobuttonInfo::Groups_text][$element]['value'] = $value;
                break;            
            case RadiobuttonInfo::Relevance_ID:
                $element = RadiobuttonInfo::Relevance;
                $values[RadiobuttonInfo::Groups_text][$element]['value'] = $value;
                break;                
            case RadiobuttonInfo::Presentation_ID:
                $element = RadiobuttonInfo::Presentation;
                $values[RadiobuttonInfo::Groups_text][$element]['value'] = $value;
                break;                
            case RadiobuttonInfo::Technical_quality_ID:
                $element = RadiobuttonInfo::Technical_quality;
                $values[RadiobuttonInfo::Groups_text][$element]['value'] = $value;
                break;                
            case RadiobuttonInfo::Overall_rating_ID:
                $element = RadiobuttonInfo::Overall_rating;
                $values[RadiobuttonInfo::Groups_text][$element]['value'] = $value;
                break;            
            case RadiobuttonInfo::Amount_of_rewriting_ID:
                $element = RadiobuttonInfo::Amount_of_rewriting;
                $values[RadiobuttonInfo::Groups_text][$element]['value'] = $value;
                break;                
            case RadiobuttonInfo::Reviewers_expertise_ID:
                $element = RadiobuttonInfo::Reviewers_expertise;
                $values[RadiobuttonInfo::Groups_text][$element]['value'] = $value;
                break;
        }         
        
        if (!$valid) { 
            $invalid_constants[$invalid_indicator]['element'] = $element;
            $invalid_constants[$invalid_indicator]['value'] = $value;
            $invalid_indicator++;
        }
    }
    
    $textareas_constant = TextareaInfo::getConstants();
    
    foreach ($textareas as $key => $value) {
    
        //replacing textarea for blank space (name of each textarea is: textareaID) to get ID of textarea
        $key = str_replace(TextareaInfo::Textarea_text, "", $key);
        
        @$valid = check_if_element_is_valid($value, FormElements::TEXTAREA, $textareas_constant[$key]['needed']);
        $element = '';

        switch($key) {
            case TextareaInfo::Main_contributions_ID: 
                $element = TextareaInfo::Main_contributions;  
                $values[TextareaInfo::Textareas_text][$element]['value'] = $value;
                break;                
            case TextareaInfo::Positive_aspects_ID:
                $element = TextareaInfo::Positive_aspects;
                $values[TextareaInfo::Textareas_text][$element]['value'] = $value;
                break;            
            case TextareaInfo::Negative_aspects_ID:
                $element = TextareaInfo::Negative_aspects;
                $values[TextareaInfo::Textareas_text][$element]['value'] = $value;
                break;                
            case TextareaInfo::Comment_ID:
                $element = TextareaInfo::Comment;
                $values[TextareaInfo::Textareas_text][$element]['value'] = $value;
                break;                
            case TextareaInfo::Internal_comment_ID:
                $element = TextareaInfo::Internal_comment;
                $values[TextareaInfo::Textareas_text][$element]['value'] = $value;
                break;                
        }              
              
             
        if (!$valid) {
            $invalid_constants[$invalid_indicator]['element'] = $element;
            $invalid_constants[$invalid_indicator]['value'] = $value;
            
            $invalid_indicator++;
        }                                   
        
    }                              
    
    //if every needed element have valid value and size of groups and textareas are more than zero (because size of array of groups and textareas is 0 when nothing is filled) 
    if ($invalid_indicator == 0 && sizeof($groups) > 0 && sizeof($textareas) > 0) {   
        upload_to_DB_offline_review_form($rid, $values);
        return TRUE;
    }
    else {        
        $invalid_fields = "";
        $i = 0;
        
        if ($invalid_indicator > 0) {
          //if at least one element is filled
          foreach($invalid_constants as $key) {
            if ($key['element'] != null) {
              $invalid_fields .= $key['element'];                   
          
              if ($i < ($invalid_indicator - 1)) $invalid_fields .= ", ";
              $i++;
            }
          }
        }
        else {
          //if nothing is filled
          $size = sizeof($radiobuttons_constant) + sizeof($textareas_constant);
          foreach($radiobuttons_constant as $key => $value) {
            $invalid_fields .= $value['name'].", ";             
          }
          
          
          foreach($textareas_constant as $key => $value) {            
            if ($value['needed']) $invalid_fields .= $value['name'].", ";                       
          }
                    
        }
        $invalid_fields = substr($invalid_fields, 0, strlen($invalid_fields) - 2);
        set_failure("review_details_fail", "<b>Unable to process the offline review form</b> " . "for Review ID# $rid, all required fields must be filled... (".$invalid_fields.").",
                    DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
        return TRUE;
    }
    
}

//
//upload evaluation values into database
//$rid - review id
//$values - array with all values writed below this
// 
//return boolean value
function upload_to_DB_offline_review_form($rid, $values) {									                                                                                                                         
   $qry = db_get('state', 'reviews', "`id`='" . $rid . "'");
   if (!$qry) {
	  error("Database error", "The database server returned an error: " . db_error(get_session_var('dblink')), DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
	  return TRUE;
   }
   else if ($qry === "F") {
	  set_failure("review_details_fail", "<b>Unable to process the offline review form</b> " . "for Review ID# $rid, this review has been marked as finished...", DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
	  return TRUE;
   }
	
   $qstr = sprintf("UPDATE `reviews` SET 
   `state`='U', 
   `originality` = '" . $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Originality]['value'] . "', 
   `significance` = '" . $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Significance]['value'] . "', 
   `relevance` = '" . $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Relevance]['value'] . "', 
   `presentation` = '" . $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Presentation]['value'] . "', 
   `technical_quality` = '" . $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Technical_quality]['value'] . "', 
   `total_rating` = '" . $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Overall_rating]['value'] . "',	
   `rewriting_amount` = '" . $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Amount_of_rewriting]['value'] . "', 
   `reviewer_expertise` = '" . $values[RadiobuttonInfo::Groups_text][RadiobuttonInfo::Reviewers_expertise]['value'] . "',
   `main_contrib` = '" . $values[TextareaInfo::Textareas_text][TextareaInfo::Main_contributions]['value'] . "', 
   `pos_aspects` = '" . $values[TextareaInfo::Textareas_text][TextareaInfo::Positive_aspects]['value'] . "', 
   `neg_aspects`= '" . $values[TextareaInfo::Textareas_text][TextareaInfo::Negative_aspects]['value'] . "',
   `rev_comment` = '" . $values[TextareaInfo::Textareas_text][TextareaInfo::Comment]['value'] . "', 
   `int_comment` = '". $values[TextareaInfo::Textareas_text][TextareaInfo::Internal_comment]['value'] ."'  
    WHERE `id`='" . safe($rid) . "'");
		
   if (!$qstr) {
	  set_failure("review_details_fail", "<b>Unable to process the offline review form</b> " . "for Review ID# $rid. The review contains incompatible/unprocessable characters...", DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
   	   return TRUE;
   }
	
   $qry = db_query($qstr);
   if (!$qry){
	  error("Database error", "Error while storing the data from the offline review form into the database. " . "The database server returned this error: " . db_error(get_session_var('dblink')), DOC_ROOT . "/index.php?form=review-details&rid=" . $rid) ;
	  return TRUE;
	}
	
	set_message("review_details_info", "<b>The uploaded offline review form for Review ID# $rid was successfully processed</b>..." , DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);

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
    $mpdf->SetWatermarkText($watermark_text, 0.05);
    $mpdf->showWatermarkText = true; 
    return $mpdf;
}

//
//load submission file (pdf file) and import pages after review formula
//$mpdf - mpdf class, creating pdf from html code  
//$path_to_file - path to submission file
//$path_to_logo - path to tsd logo
//
//return $mpdf - return mpdf document with imported submission
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
//return $image - logo of TSD
function create_header_image($path_to_logo) {

    $image = '<div style="position: absolute; top: 5; right: 20; width: 120;">';
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
        $first_page .= $elements->evaluation_radio_buttons($radio_button_info[$i]['name'], $radio_button_info[$i]['info'], $count_of_evaluations_to, $i);
    }
    
    $first_page .= '</form>';
                  
    $first_page .= '<hr style="margin: 10;"/>';
                              
    $first_page .= $elements->evaluation_textarea($textarea_info[0]['name'], $textarea_info[0]['info'], $textarea_info[0]['id'], 10, 87);
    
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
    $second_page = "";
    
    for ($i = 1; $i <= 4; $i++) {
      $second_page .= $elements->evaluation_textarea($textarea_info[$i]['name'], $textarea_info[$i]['info'], $textarea_info[$i]['id'], 10, 87);
    }
    
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