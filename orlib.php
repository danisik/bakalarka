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
//		Last update on:		18-10-2018
//      Encoding: utf-8 no BOM
//

$submissionID = 9;
$reviewID = 1;      
$nameOfSubmission = 'Survey of Business Perception Based on Sentiment Analysis through Deep Neuronal Networks for Natural Language Processing';
$nameOfReviewer = 'Kamil Ekštein';
    
generate_offline_review_form($reviewID, $nameOfReviewer, $submissionID, $nameOfSubmission, $_SERVER['DOCUMENT_ROOT'].'/TSD/pdf.pdf', '');


//
//generate review pdf form with form elements
//$rid - review id
//$reviewer_name - name of reviewer
//$sid - submission id
//$submission_name - name of submission to be reviewed
//$submission_filename - path to submission
//$review_html_footer - 
//
function generate_offline_review_form($rid, $reviewer_name, $sid, $submission_name,
	$submission_filename, $review_html_footer) {
                                
    //path to working directory
    $root = $_SERVER['DOCUMENT_ROOT'].'/';
    //path to mpdf in working directory
    $path = $root.'TSD/';
    //path for source files
    $pathSource = $path.'src/';
    //path to mpdf library
    $pathMPDF = $path.'mpdf/';
    //path to configuration file
    $configurationPath = $path.'config/configuration.xml';
    //path to logo
    $pathToLogo = $path.'img/tsd-logo.png';
    //text for watermark
    $waterMarkText = 'REVIEW';


    //including our classes
    include ($pathSource.'Enumerates.php');
    include ($pathSource.'OwnXmlReader.php');
    include ($pathSource.'TextConversion.php');
    include ($pathSource.'HTMLElements.php');
    require ($pathMPDF.'vendor/autoload.php');
    
    
    //mpdf class, creating pdf from html code
    $mpdf = setMPDF();
     
    //our class with text conversion and calculating font size
    $textConversioner = new TextConversioner;
    //our class with printing form elements (html code)
    $elements = new HTMLElements;
    //our class with read xml file and get values
    $xmlReader = new OwnXmlReader;
    $xmlReader->readXMLFile($configurationPath);

    //year of actual conference
    $yearOfConference = $xmlReader->getYearOfConference();

    //value which indicates start value for evaluation ranking
    $countOfEvaluationsFrom = 0;
    //ranking evaluation for radio buttons (amount of rbuttons)
    $countOfEvaluationsTo = 10;
    //info text where to upload review
    $submissionUploadInfo = 'After filling the form in, please, upload it to the TSD'.$yearOfConference.' web review application: Go to URL
        https://www.kiv.zcu.cz/tsd'.$yearOfConference.' and after logging in, please, proceed to section '."'My Reviews'".', select the corresponding
        submission and press the '."'Review'".' button. There, you'."'".'ll be able to upload this PDF file.';
    $fileName = 'TSD'.$yearOfConference.'_Review_Form_'.$rid;

    //set header for all pages (text)
    $mpdf->SetHTMLHeader($elements->evaluationHeader($textConversioner, $rid, $submission_name));
    
    //set rid and sid into document (hidden, easy to get rid and sid when parsing pdf document)
    $html = setHiddenRIDandSID($rid, $sid);
    $html .= createHeaderImage($pathToLogo); 
    $html .= createFirstTemplatePage($elements, $textConversioner, $xmlReader, $sid, $submission_name, $reviewer_name, $countOfEvaluationsTo);

    //write first page of evaluation
    $mpdf->WriteHTML($html);
    //add second page - because if instructions does not exist, textareas from second page are inserted into first page 
    $mpdf->AddPage();
    
    //reset html code
    $html = ''; 
    $html .= createHeaderImage($pathToLogo);    
    $html .= createSecondTemplatePage($elements, $submissionUploadInfo);

    $mpdf->WriteHTML($html);
    $mpdf->AddPage();
    //set watermark for submission
    $mpdf = setWaterMark($mpdf, $waterMarkText);
    //load submission and import it after review form
    $mpdf = loadSubmission($mpdf, $submission_filename, $pathToLogo);
    //create pdf
    $mpdf->Output(); 
    
    
    //$mpdf->Output($filename,'F');      // only save to File
    //$mpdf->Output($filename,'D');      // make it to DOWNLOAD   
}

//
//inicialize mpdf class variable
//
//return $mpdf - mpdf class, creating pdf from html code  
function setMPDF() {
    $mpdf = new \Mpdf\Mpdf([
	   'mode' => 'c',
	   'margin_top' => 30,
	   'margin_header' => 10,
	   'margin_footer' => 10,
        'default_font' => ''
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
function setHiddenRIDandSID($rid, $sid) {
    $hidden = '<input type="hidden" name="rid" value="'.$rid.'" />';
    $hidden .= '<input type="hidden" name="sid" value="'.$sid.'" />';
    
    return $hidden; 
}

//
//set watermark text into pdf
//
//return $mpdf - mpdf with watermark text set 
function setWaterMark($mpdf, $waterMarkText) {
    $mpdf->SetWatermarkText($waterMarkText, 0.1);
    $mpdf->showWatermarkText = true; 
    return $mpdf;
}

//
//load submission file (pdf file) and import pages after review formula
//$mpdf - mpdf class, creating pdf from html code  
//$pathToFile - path to submission file
//$pathToLogo - path to tsd logo
//
function loadSubmission($mpdf, $pathToFile, $pathToLogo) { 
    $mpdf->SetImportUse();
    $pageCount = $mpdf->SetSourceFile($pathToFile);
    for($i = 1; $i <= $pageCount; $i++) {            
        $template = $mpdf->ImportPage($i);
        $mpdf->UseTemplate($template);
        $mpdf->WriteHTML(createHeaderImage($pathToLogo));
        if($i < $pageCount) $mpdf->AddPage();
    }
    return $mpdf;
}

//
//create image in position of header
//$pathToLogo - path to tsd logo
//
function createHeaderImage($pathToLogo) {

    $image = '<div style="position: absolute; top: 5; right: 0; width: 120;">';
    $image .= '<img src="'.$pathToLogo.'"/>';
    $image .= '</div>';
        
    return $image;
}

//
//extract values from review pdf form
//$elements - our class creating html elements
//$textConversioner - our text conversioner
//$xmlReader - our xml reader 
//$sid - submission id
//$submission_name - name of reviewed submission                      
//$reviewer_name - name of current reviewer 
//$countOfEvaluationsTo - how many radiobuttons we want in evaluation 
//
//return $firstPage - html code of first template page
function createFirstTemplatePage($elements, $textConversioner, $xmlReader, $sid, $submission_name, $reviewer_name, $countOfEvaluationsTo) {
    //document title
    $firstPage = $elements->evaluationReviewTitle($textConversioner, $sid, $submission_name, $reviewer_name);
    //info
    $firstPage .= $elements->evaluationInstructions($textConversioner, $xmlReader);
    $firstPage .= '<form id="groups">';
    $firstPage .= $elements->evaluationRadioButtons("Originality", "Rate how original the work is", $countOfEvaluationsTo);        
    $firstPage .= $elements->evaluationRadioButtons("Significance", "Rate how significant the work is", $countOfEvaluationsTo);
    $firstPage .= $elements->evaluationRadioButtons("Relevance", "Rate how relevant the work is", $countOfEvaluationsTo);
    $firstPage .= $elements->evaluationRadioButtons("Presentation", "Rate the presentation of the work", $countOfEvaluationsTo);
    $firstPage .= $elements->evaluationRadioButtons("Technical quality", "Rate the technical quality of the work", $countOfEvaluationsTo);
    $firstPage .= $elements->evaluationRadioButtons("Overall rating", "Rate the work as a whole", $countOfEvaluationsTo);
    $firstPage .= $elements->evaluationRadioButtons("Amount of rewriting", "Express how much of the work should be rewritten", $countOfEvaluationsTo);
    $firstPage .= $elements->evaluationRadioButtons("Reviewer's expertise", "Rate how confident you are about the above rating", $countOfEvaluationsTo);
    $firstPage .= '</form>';
                  
    $firstPage .= '<hr style="margin: 10;"/>';                          
    $firstPage .= $elements->evaluationTextArea("Main Contribution", "Summarise main contribution", "main_contribution", '', 16, 87);
    
    return $firstPage;
}

//
//extract values from review pdf form
//$elements - our class creating html elements
//$submissionUploadInfo - info about what to do after filling the form
//
//return $secondPage - html code of second template page
function createSecondTemplatePage($elements, $submissionUploadInfo) {
    $secondPage = $elements->evaluationTextArea("Positive aspects", "Recapitulate the positive aspects", "positive_aspects", "", 24, 87);
    $secondPage .= $elements->evaluationTextArea("Negative aspects", "Recapitulate the negative aspects", "negative_aspects", '', 24, 87);
    $secondPage .= '<p>'.$submissionUploadInfo.'</p>';
    
    return $secondPage;
}

//
//extract values from review pdf form
//$rid - review id
//$sid - submission id
//$revform_filename - filename of review form (pdf file)
//
function process_offline_review_form($rid, $sid, $revform_filename) {

}

//
//upload evaluation values into database
//$rid - review id
//$ originality - how is the work original
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
//$rev_comment - 
//$int_comment -
// 
function upload_to_DB_offline_review_form($rid, $originality, $significance, $relevance, $presentation, 
	$technical_quality, $total_rating, $rewriting_amount, $reviewer_expertise, $main_contrib, $pos_aspects, 
	$neg_aspects, $rev_comment, $int_comment) {
																		
	if(!(
			($originality >= $countOfEvaluationsFrom && $originality <= $countOfEvaluationsTo) &&
			($significance >= $countOfEvaluationsFrom && $significance <= $countOfEvaluationsTo) && 
			($relevance >= $countOfEvaluationsFrom && $relevance <= $countOfEvaluationsTo) &&
			($presentation >= $countOfEvaluationsFrom && $presentation <= $countOfEvaluationsTo) && 
			($technical_quality >= $countOfEvaluationsFrom && $technical_quality <= $countOfEvaluationsTo) && 
			($total_rating >= $countOfEvaluationsFrom && $total_rating <= $countOfEvaluationsTo) &&
			($rewriting_amount >= $countOfEvaluationsFrom && $rewriting_amount <= $countOfEvaluationsTo) && 
			($reviewer_expertise >= $countOfEvaluationsFrom && $reviewer_expertise <= $countOfEvaluationsTo) &&
			!empty($main_contrib) && !empty($pos_aspects) && !empty($neg_aspects)
		)
	) {
		set_failure("review_details_fail", "<b>Unable to process the offline review form</b> " .
			"for Review ID# $rid, all required fields must be filled...",
			DOC_ROOT . "/index.php?form=review-details&rid=" . $rid);
		return TRUE;
	}
		
	$qry = db_get('state', 'reviews', "`id`='" . safe($rid) . "'");
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


?>