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

//
//      Class for printing form elements (html code)
//
 
class HTMLElements {
    
    //
    //print radiobutton into pdf
    //$nameOfEvaluation - header for info
    //$evaluationInfo - info about purpose of this group of radiobuttons
    //$countOfRankings - scale of evaluations
    //$groupID - id of group
    //
    //return $radio - html text contains radio buttons
    function evaluationRadioButtons($nameOfEvaluation, $evaluationInfo, $countOfRankings, $groupID){   
        $radio = '';
        $radio .= '<p style="margin: 0.2; font-size: 15pt;"><strong>'.$nameOfEvaluation.'</strong> - '.$evaluationInfo.':</p>';
        $radio .= '<p style="margin: 0.2;">';
        $radio .= '<fieldset id="group'.$groupID.'">';
        for($i = 0; $i <= $countOfRankings; $i++) {
            $radio .= '<input type="radio" name="group'.$groupID.'" value="'.$i.'">  '.$i.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        } 
        $radio .= '</fieldset>'; 
        $radio .= '</p>';
        return $radio;                                                                                                 
    }
    
    //
    //print textarea into pdf
    //$textAreaHeader - header for info
    //$textAreaInfo - for what purpose is this textarea
    //$textareaID - id of textarea
    //$rows - how many rows textarea have to 
    //$cols - how many cols textarea have to
    //$areaText - text to be displayed in textarea by default 
    //
    //return $area - html text contains textarea
    function evaluationTextArea($textAreaHeader, $textAreaInfo, $textareaID, $rows, $cols, $areaText = '') {
        //char a, because mpdf have some internal 'problem' (maybe) to display textarea without declared text, so we declare some string and then 
        //in file src/Mpdf.php in method "printobjectbuffer" text is set as empty string
        if($areaText == '') $areaText = 'a';
        
        $area = '';
        $area .= '<p style="margin: 0; margin-top: 15; font-size: 15pt;"><strong>'.$textAreaHeader.'</strong> - '.$textAreaInfo.': </p>';                        
        $area .= '<textarea name="textarea'.$textareaID.'" rows="'.$rows.'" cols="'.$cols.'">'.$areaText.'</textarea>';
        return $area;
    } 
    
    //
    //get header for evaluation pdf document
    //$textConversioner - variable of our class for text conversion
    //$reviewID - id of review
    //$nameOfSubmission - name of reviewed submission
    //
    //return $header - html text contains header for document
    function evaluationHeader($textConversioner, $reviewID, $nameOfSubmission) {
    
        $fontSizeOfReviewID = 25;
        $fontSizeOfHeaderTitle = 16;
        
        $values = $textConversioner->checkText(Types::HEADER_TITLE, $fontSizeOfHeaderTitle, $nameOfSubmission);
        
        $fontSizeOfHeaderTitle = $values["font"];
        $nameOfSubmission = $values["text"];
        
        $header = '<p><span style="font-size: '.$fontSizeOfReviewID.'pt; font-weight: bold;">REVIEW ID #'.$reviewID.' : </span>';
        $header .= '<span style="font-size: '.$fontSizeOfHeaderTitle.'pt; font-weight: bold;">'.$nameOfSubmission.'</span></p>';
        $header .= '<hr style="margin: 0;"/>';
        return $header;
    }
    
    //
    //get title for evalutation pdf document
    //$textConversioner - variable of our class for text conversion
    //$submissionID - id of reviewed submission
    //$nameOfSubmission - name of reviewed submission
    //$nameOfReviewer - name of reviewer
    //
    //return $title - html text contains title for document
    function evaluationReviewTitle($textConversioner, $submissionID, $nameOfSubmission, $nameOfReviewer) {
        $fontSizeOfTitleInfo = 15;
        $fontSizeOfTitle = 25;
        
        $values = $textConversioner->checkText(Types::TITLE, $fontSizeOfTitle, $nameOfSubmission);
        
        $fontSizeOfTitle = $values["font"];
        $nameOfSubmission = $values["text"];
        
        $title = '';
        $title .= '<p style="font-size: '.$fontSizeOfTitleInfo.'pt; font-weight: bold; text-align: center;">Offline Review Form for Submission S-ID #'.$submissionID;
        $title .= '<p style="font-size: '.$fontSizeOfTitle.'pt; font-weight: bold; text-align: center;">'.$nameOfSubmission.'';
        $title .= '<p style="font-size: '.$fontSizeOfTitleInfo.'pt; font-weight: bold; text-align: center;">Review by '.$nameOfReviewer.'';
        return $title;    
    }
    
    //
    //get instructions for evaluation
    //$textConversioner - variable of our class for text conversion
    //$xmlReader - ownXmlReader contains instructions
    //
    //return $instructions - html text contains instruction text below title
    function evaluationInstructions($textConversioner, $xmlReader) {
        $fontSizeOfInfo = 13;
        //get text from XML
        $instructionHeader = $xmlReader->getInstructionHeader();
        $instructionText = $xmlReader->getInstructionText();
        $instructionWarning = $xmlReader->getInstructionWarning();
        
        //unite all info to one text
        $fullText = $instructionHeader.$instructionText.$instructionWarning;
        
        //calculate length of each part of info
        $lenOfHeader = mb_strlen($instructionHeader);
        $lenOfText = mb_strlen($instructionText);
        $lenOfWarning = mb_strlen($instructionWarning);
        
        //check text
        $values = $textConversioner->checkText(Types::INFO, $fontSizeOfInfo, $fullText);
        
        //get back updated font and text (if it was needed)
        $fontSizeOfInfo = $values["font"];
        $fullText = $values["text"]; 
        
        //calculate start position of each string
        $instructionHeaderStart = 0;
        $instructionTextStart = $lenOfHeader;
        $instructionWarningStart = $lenOfHeader + $lenOfText; 
        
        //separate each part of text back
        $instructionHeader = substr($fullText, $instructionHeaderStart, $lenOfHeader);
        $instructionText = substr($fullText, $instructionTextStart, $lenOfText);
        $instructionWarning = substr($fullText, $instructionWarningStart, $lenOfWarning);
          
        $instructions = '';
        $instructions .= '<p style="margin-bottom: 10; font-size: '.$fontSizeOfInfo.'pt">';
        $instructions .= '<strong>'.$instructionHeader.': </strong>'.$instructionText.' <span style="color: red">'.$instructionWarning.'</span>';
        $instructions .= '</p>';
        return $instructions;
    }   
} 
?>