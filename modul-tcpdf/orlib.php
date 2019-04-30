<?php

//
//		CONFERENCE PORTAL PROJECT
//		VERSION 3.1.0
//
//		Copyright (c) 2010-2019 Dept. of Computer Science & Engineering,
//		Faculty of Applied Sciences, University of West Bohemia in PlzeĹn.
//		All rights reserved.
//
//		Code written by:	Vojtech Danisik
//		Last update on:		30-04-2019
//      Encoding: utf-8 no BOM
//

//IMPORTANT - if there is some deprecated/warning, just ignore it and generate PDF 
error_reporting(0);

//DISPLAY ERROR
//error_reporting(~0);
//ini_set('display_errors', 1);


define('DOC_GP_ROOT', DATA_ROOT.'/php/');
define('DOC_GP_OFFLINE_REVIEW', DOC_GP_ROOT.'offline-review/');
define('DOC_GP_LIB', DOC_GP_OFFLINE_REVIEW.'lib/');
define('DOC_GP_SOURCE', DOC_GP_OFFLINE_REVIEW.'src/');
define('DOC_GP_MPDF', DOC_GP_LIB.'mpdf/');
define('DOC_GP_CONFIGURATION', DOC_GP_OFFLINE_REVIEW.'config/configuration.xml');
define('DOC_GP_IMG', DOC_GP_OFFLINE_REVIEW.'img/');
define('ORLIB_LOGO', 'tsd-logo.png');
define('DOC_GP_PARSER', DOC_GP_LIB.'pdfparser/');

require_once(DOC_GP_LIB.'tcpdf/tcpdf.php');
require_once(DOC_GP_LIB.'tcpdf/tcpdi.php');

//Own class, extends TCPDI (which extends TCPDF)
//Extends because we need to get rid of unnecessary horizontal bar at the start of document 
//+ add some our value
class ORLIBPDF extends TCPDI {
 
 
   //https://tcpdf.org/docs/fonts/
   //http://fonts.snm-portal.com/ --> ttf into tcpdf
   
   public $default_font_type = 'helvetica';
   public $default_font_style = '';
   public $default_font_size = 13;
   
   var $html_header;

    public function set_HTML_header($htmlHeader) {
        $this->html_header = $htmlHeader;
    }

    public function Header() {
        $this->writeHTMLCell(
            $w = 0, $h = 0, $x = '', $y = '',
            $this->html_header, $border = 0, $ln = 0, $fill = 0,
            $reseth = true, $align = 'top', $autopadding = true);
    }
    
    public function use_default_font() {        
        $this->SetFont($this->default_font_type, $this->default_font_style, $this->default_font_size);
    }    
}

//$rid - review id
//$reviewer_name - name of current reviewer
//$sid - submission id
//$submission_name - name of reviewed submission                      
//$submission_filename - filename of submission
//
//return boolean value
function generate_offline_review_form($rid, $reviewer_name, $sid, $submission_name, $submission_filename) {
  
    mb_internal_encoding('UTF-8');
    include (DOC_GP_SOURCE.'Instruction.php');
    include (DOC_GP_SOURCE.'FormElements.php');
    include (DOC_GP_SOURCE.'TextareaInfo.php');
    include (DOC_GP_SOURCE.'RadiobuttonInfo.php');
    include (DOC_GP_SOURCE.'ConfigurationData.php');
    include (DOC_GP_SOURCE.'TextConverter.php');
    include (DOC_GP_SOURCE.'Elements.php');
                             
    $text_conversioner = new TextConverter;
    $configuration_data = new ConfigurationData;
    $configuration_data->read_XML_file(DOC_GP_CONFIGURATION); 
    $elements = new Elements(DOC_GP_IMG.ORLIB_LOGO, $configuration_data->getWatermark_text());

    $year_of_conference = $configuration_data->getYear_of_conference();
    $submission_upload_info = 'After filling the form in, please, upload it to the TSD'.$year_of_conference.' web review application: Go to URL
    <span style="display: inline; text-decoration: underline; color: blue;">https://www.kiv.zcu.cz/tsd'.$year_of_conference.' </span>and after logging in, please, proceed to section '."'My Reviews'".', 
    select the corresponding submission and press the '."'Review'".' button. There, you'."'".'ll be able to upload this PDF file.';
    $filename = 'TSD'.$year_of_conference.'_Review_Form_'.$rid.'.pdf';
    
    //DON'T USE UTF-8, THERE IS INTERNAL ERROR WHEN SOME UTF-8 CHARACTERS ARE NEED TO BE DISPLAYED (defaulty used ISO-8859-1)
    $pdf = new ORLIBPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->use_default_font();
    $pdf = $elements->set_hidden_RID_and_SID($pdf, $rid, $sid);                                                   
                      
    $pdf = create_first_template_page($pdf, $elements, $text_conversioner, $configuration_data, $rid, $sid, $submission_name, $reviewer_name);   
    $pdf = create_second_template_page($pdf, $elements, $text_conversioner, $rid, $submission_name, $submission_upload_info);    
    $pdf = $elements->load_submission($pdf, $text_conversioner, $rid, $submission_name, DATA_ROOT.'/'.$submission_filename);    
        
    $pdf->Output($filename, 'D');
    //$pdf->Output();    
    
    return TRUE;
    
}

//create first template page
//$pdf - tcpdi class 
//$elements - our class creating html elements
//$text_conversioner - our text conversioner
//$configuration_data - configuration data 
//$rid - review id
//$sid - submission id
//$submission_name - name of reviewed submission                      
//$reviewer_name - name of current reviewer 
//
//return $pdf - tcpdi class
function create_first_template_page($pdf, $elements, $text_conversioner, $configuration_data, $rid, $sid, $submission_name, $reviewer_name) {
    $pdf->addPage();    
    $pdf = $elements->evaluation_header($pdf, $text_conversioner, $rid, $submission_name);
    $pdf = $elements->evaluation_review_title($pdf, $text_conversioner, $sid, $submission_name, $reviewer_name);    
    $pdf = $elements->evaluation_instructions($pdf, $text_conversioner, $configuration_data);
          
    $radiobutton_info = RadioButtonInfo::getConstants();        
    $textarea_info = TextareaInfo::getConstants();
    
    for ($i = 0; $i < count($radiobutton_info); $i++) {
        $pdf = $elements->evaluation_radio_buttons($pdf, $radiobutton_info[$i]['name'], $radiobutton_info[$i]['info'], $i);
    }  
    $pdf = $elements->horizontal_bar($pdf); 
    $which_textarea = 0;
    $pdf = $elements->evaluation_textarea($pdf, $textarea_info[$which_textarea]['name'], $textarea_info[$which_textarea]['info'], $which_textarea, 191, 50);
    $pdf = $elements->add_watermark($pdf);        
    return $pdf;
}

//create second template page
//$pdf - tcpdi class 
//$elements - our class creating html elements
//$text_conversioner - our text conversioner
//$review_ID - id of review
//$name_of_submission - name of reviewed submission
//$submission_upload_info - info about what to do after filling the form
//
//return $pdf - tcpdi class
function create_second_template_page($pdf, $elements, $text_conversioner, $review_ID, $name_of_submission, $submission_upload_info) {
    $pdf->addPage();   
    $pdf = $elements->evaluation_header($pdf, $text_conversioner, $review_ID, $name_of_submission);
    
    $textarea_info = TextareaInfo::getConstants();
    
    for ($i = 1; $i < count($textarea_info); $i++) {
        $pdf = $elements->evaluation_textarea($pdf, $textarea_info[$i]['name'], $textarea_info[$i]['info'], $textarea_info[$i]['id'], 191, 45);
        $label = <<<EOD
        
        
        
        
        
        
        
EOD;
        $pdf->Write($h=0, $label, $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
    }
    $pdf = $elements->horizontal_bar($pdf);
    $pdf->writeHTML($submission_upload_info);
    $pdf = $elements->add_watermark($pdf);
    
    return $pdf;
}


//extract values from review pdf form
//$rid - review id
//$sid - submission id
//$revform_filename - filename of review form (pdf file)
//
//return boolean value
function process_offline_review_form($rid, $sid, $revform_filename) {
    
    require (DOC_GP_PARSER.'vendor/autoload.php');
    include (DOC_GP_SOURCE.'Instruction.php');
    include (DOC_GP_SOURCE.'FormElements.php');
    include (DOC_GP_SOURCE.'TextareaInfo.php');
    include (DOC_GP_SOURCE.'RadiobuttonInfo.php');
    
    $parser = new \Smalot\PdfParser\Parser();
    
    /*
    //If $revform_filename sended to this method send invalid PDF filename (like revform384.xxx, where filename extension is not PDF)
    //then there is some internal error in web system and parser cannot read this file.
    $filename_extension = substr($revform_filename, strlen($revform_filename) - 3, strlen($revform_filename));    
    if ($filename_extension == 'xxx') {
        set_failure("review_details_fail", "Uploaded REVIEW PDF was not processed due to an internal error. Please contact administrators.",
          DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
        return FALSE;
    }
    */
    
    //$revform_filename = substr($revform_filename, 0, strlen($revform_filename) - 3).'pdf';                                       
    $pdf = $parser->parseFile($revform_filename, DOC_GP_LIB.'tcpdf/');
        
    
    $data = $pdf->getFormElementsData();
    print_r($data);
        
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
            $invalid_constants[$invalid_indicator] = $element;
            $invalid_indicator++;
        }
    }
    echo '<br>';
    print_r($values);
    echo '<br>';
    $invalid = full_element_control($invalid_constants, $invalid_indicator, $values[RadiobuttonInfo::Groups_text], FormElements::RADIOBUTTON);
    $invalid_constants = $invalid[0];
    $invalid_indicator = $invalid[1];
    
    
    
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
            $invalid_indicator++;
        }                                   
        
    }        
    
    $invalid = full_element_control($invalid_constants, $invalid_indicator, $values[TextareaInfo::Textareas_text], FormElements::TEXTAREA);
    $invalid_constants = $invalid[0];
    $invalid_indicator = $invalid[1];
             
    $not_needed_textareas = TextareaInfo::getNotNeededConstants();;
    foreach ($not_needed_textareas as $key => $value) {
      if (!array_key_exists($key, $values[TextareaInfo::Textareas_text])) {
        $values[TextareaInfo::Textareas_text][$key]['value'] = "";
      }
    }
    
    $rid_sid = parse_RID_and_SID($pdf);
    
    //Check if file does not contain rid or sid in metadata
    if ($rid_sid[0] == NULL || $rid_sid[1] == NULL) {
      set_failure("review_details_fail", "Invalid PDF file, please upload only PDF files generated by TSD conference generator.",
        DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
        return FALSE;
    } 
    
    //Check if rid and sid of file match with sent rid and sid  
    if ($rid != $rid_sid[0]) {
      set_failure("review_details_fail", "Review ID# (" . $rid_sid[0] . ") of file didn't match Review ID# of this Review(" . $rid . ").",
        DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
        return FALSE;
    }
    else if ($sid != $rid_sid[1]) {
      set_failure("review_details_fail", "Submission ID# (" . $rid_sid[1] . ") of file didn't match Submission ID# of this Review(" . $sid . ").",
        DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
        return FALSE;
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
            if ($key != null) {
              $invalid_fields .= $key;                   
          
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
        $invalid_fields = substr($invalid_fields, 0, strlen($invalid_fields));
        set_failure("review_details_fail", "<b>Unable to process the offline review form</b> " . "for Review ID# $rid, all required fields must be filled (".$invalid_fields.").",
                    DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
        return FALSE;
    }     
}

//upload evaluation values into database
//$rid - review id
//$values - array with all values writed below this
// 
//return boolean value
function upload_to_DB_offline_review_form($rid, $values) {									                                                                                                                         
   $qry = db_get('state', 'reviews', "`id`='" . $rid . "'");
   if (!$qry) {
	  error("Database error", "The database server returned an error: " . db_error(get_session_var('dblink')), DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
	  return FALSE;
   }
   else if ($qry === "F") {
	  set_failure("review_details_fail", "<b>Unable to process the offline review form</b> " . "for Review ID# $rid, this review has been marked as finished...", DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
	  return FALSE;
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
   	   return FALSE;
   }
	
   $qry = db_query($qstr);
   if (!$qry){
	  error("Database error", "Error while storing the data from the offline review form into the database. " . "The database server returned this error: " . db_error(get_session_var('dblink')), DOC_ROOT . "/index.php?form=review-details&rid=" . $rid) ;
	  return FALSE;
	}
	
	set_message("review_details_info", "<b>The uploaded offline review form for Review ID# $rid was successfully processed</b>..." , DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);

	return TRUE;	
}

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
            if($elementValue == 'Off' && $elementNeeded == true) $valid = false;
            break;
        case FormElements::TEXTAREA:
            if($elementValue == '' && $elementNeeded == true) $valid = false;
            break;
    }
    
    return $valid;
} 

//return RID and SID of PDF saved in metadata
//$pdf - pdf parser class, parsing content of PDF file  
//
//return array - return sid and rid of PDF document
function parse_RID_and_SID($pdf) {
    $metadata = $pdf->getDetails();
    $rid = '';
    $sid = '';
    
    //format of RID and SID: KEY: KeyWords, VALUE: RID, SID
    foreach($metadata as $key => $value) {
      if (strpos($key, 'Keywords') !== false) {
        $data = explode(' ', $value);
        $sid = $data[0];
        $rid = $data[1];
        break; 
      }
    }
    return array($rid, $sid);  
}

//control one type of form element if contains all values
//$invalid_constants - array of invalid constants
//$invalid_indicator - index to array
//$elements - values parsed from pdf
//$elementType - type of form element 
//
//return array - $invalid_constants and $invalid_indicator

function full_element_control($invalid_constants, $invalid_indicator, $elements, $elementType) {
  $constants = '';
  $index = '';
  switch($elementType) {
    case FormElements::RADIOBUTTON:
      $constants = RadiobuttonInfo::getConstants();
      $index = RadiobuttonInfo::Groups_text;
      break;
    case FormElements::TEXTAREA:
      $constants = TextareaInfo::getConstants();
      $index = TextareaInfo::Textareas_text;
      break;
  }
  
  foreach($constants as $key => $value) {
    if ($elements[$value['name']] == null && $value['needed'] == true) {
      $invalid_constants[$invalid_indicator] = $value['name'];
      $invalid_indicator++;
    }
  }
  
  return array($invalid_constants, $invalid_indicator);
}

?>