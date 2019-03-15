<?php
//
//		CONFERENCE PORTAL PROJECT
//		VERSION 3.0.6
//
//		Copyright (c) 2010-2019 Dept. of Computer Science & Engineering,
//		Faculty of Applied Sciences, University of West Bohemia in Plzeò.
//		All rights reserved.
//
//		Code written by:	Vojtech Danisik
//		Last update on:		03-03-2019
//      Encoding: utf-8 no BOM
//
//
//      Enumerate classes
//
//enum for instruction in pdf
class Instruction { 
    const TITLE = "title"; 
    const HEADER_TITLE = "header_title";
    const INFO = "info";
    const REVIEWER_NAME = 'reviewer_name'; 
    
    function getConstants() {
        $values = array();
        array_push($values, Instruction::TITLE);
        array_push($values, Instruction::HEADER_TITLE);
        array_push($values, Instruction::INFO);
        array_push($values, Instruction::REVIEWER_NAME);
        
        return $values;    
    }         
}
//enum contains all used form elements
class FormElements {
    const RADIOBUTTON = 'radiobutton';
    const TEXTAREA = 'textarea';
    
    function getConstants() {
        $values = array();
        array_push($values, FormElements::RADIOBUTTON);
        array_push($values, FormElements::TEXTAREA);
        
        return $values;
    }
}
//enum contains info about textareas
class TextareaInfo {
    //string for identifing textareas in array of values when parsing
    const Textareas_text = 'textareas';
    const Textarea_text = 'textarea';
    const Main_contributions_ID = 0;
    const Main_contributions = 'Main contributions';
    const Main_contributions_info = 'Summarise main contributions';
    
    const Positive_aspects_ID = 1;
    const Positive_aspects = 'Positive aspects';
    const Positive_aspects_info = 'Recapitulate the positive aspects';
    
    const Negative_aspects_ID = 2;
    const Negative_aspects = 'Negative aspects';
    const Negative_aspects_info = 'Recapitulate the negative aspects';
    
    const Comment_ID = 3;
    const Comment = 'Comment (optional)';
    const Comment_info = 'A message for the <strong>author(s)</strong>';
    
    const Internal_comment_ID = 4;
    const Internal_comment = 'Internal comment (optional)';
    const Internal_comment_info = 'An internal message for the <strong>organizers</strong>';
    
    //add constant into array of constants
    //$values - array of constants
    //$id - id of constant
    //$name - name of constant
    //$info - specific info of constant
    //$needed_to_fill - if this textarea must be filled or not
    //
    //return $values - array of constants with new, added, constant
    function add_value_to_array($values, $id, $name, $info, $needed_to_fill) {
        $values[$id]['id'] = $id;
        $values[$id]['name'] = $name;
        $values[$id]['info'] = $info;
        $values[$id]['needed'] = $needed_to_fill;
        $values[$id]['type'] = FormElements::TEXTAREA;
        
        return $values;
    }
     
    function getConstants() {
        $values = array();
        
        $values = TextareaInfo::add_value_to_array($values, TextareaInfo::Main_contributions_ID, TextareaInfo::Main_contributions, TextareaInfo::Main_contributions_info, true);
        $values = TextareaInfo::add_value_to_array($values, TextareaInfo::Positive_aspects_ID, TextareaInfo::Positive_aspects, TextareaInfo::Positive_aspects_info, true);
        $values = TextareaInfo::add_value_to_array($values, TextareaInfo::Negative_aspects_ID, TextareaInfo::Negative_aspects, TextareaInfo::Negative_aspects_info, true);
        $values = TextareaInfo::add_value_to_array($values, TextareaInfo::Comment_ID, TextareaInfo::Comment, TextareaInfo::Comment_info, false);
        $values = TextareaInfo::add_value_to_array($values, TextareaInfo::Internal_comment_ID, TextareaInfo::Internal_comment, TextareaInfo::Internal_comment_info, false);
        
        return $values;
    }
    
    function getNotNeededConstants() {
        $constants = TextareaInfo::getConstants();
        $values = array();
        foreach($constants as $key => $value) {
          if ($value['needed'] != true) {
            $values[$value['name']] .= $value['name'];
          }
        }
        return $values;
    }
}
//enum contains info about group of radiobuttons
class RadiobuttonInfo {
    //string for identifing radiobutton groups in array of values when parsing
    const Groups_text = 'groups';
    const Group_text = 'group';
    //first evaluation value
    const Count_of_evaluations_from = 0;
    //last evaluation value
    const Count_of_evaluations_to = 10;
    
    const Originality_ID = 0;
    const Originality = 'Originality';
    const Originality_Info = 'Rate how original the work is'; 
   
    const Significance_ID = 1;
    const Significance = 'Significance';
    const Significance_Info = 'Rate how significant the work is';   
    
    const Relevance_ID = 2;
    const Relevance = 'Relevance';
    const Relevance_Info = 'Rate how relevant the work is';
    
    const Presentation_ID = 3;
    const Presentation = 'Presentation';
    const Presentation_Info = 'Rate the presentation of the work';
    
    const Technical_quality_ID = 4;    
    const Technical_quality = 'Technical quality';
    const Technical_quality_Info = 'Rate the technical quality of the work';
    
    const Overall_rating_ID = 5;    
    const Overall_rating = 'Overall rating';
    const Overall_rating_Info = 'Rate the work as a whole';
    
    const Amount_of_rewriting_ID = 6;
    const Amount_of_rewriting = 'Amount of rewriting';
    const Amount_of_rewriting_Info = 'Express how much of the work should be rewritten';
    
    const Reviewers_expertise_ID = 7;
    const Reviewers_expertise = 'Reviewer'."'".'s expertise';
    const Reviewers_expertise_Info = 'Rate how confident you are about the above rating'; 
    
    //add constant into array of constants
    //$values - array of constants
    //$id - id of constant
    //$name - name of constant
    //$info - specific info of constant
    //
    //return $values - array of constants with new, added, constant
    function add_value_to_array($values, $id, $name, $info) {    
        $values[$id]['id'] = $id;
        $values[$id]['name'] = $name;
        $values[$id]['info'] = $info;
        $values[$id]['type'] = FormElements::RADIOBUTTON;
        
        return $values;
    }  
    
    function getConstants() {
        $values = array();
        
        $values = RadiobuttonInfo::add_value_to_array($values, RadiobuttonInfo::Originality_ID, RadioButtonInfo::Originality, RadioButtonInfo::Originality_Info);
        $values = RadiobuttonInfo::add_value_to_array($values, RadiobuttonInfo::Significance_ID, RadioButtonInfo::Significance, RadioButtonInfo::Significance_Info);
        $values = RadiobuttonInfo::add_value_to_array($values, RadiobuttonInfo::Relevance_ID, RadioButtonInfo::Relevance, RadioButtonInfo::Relevance_Info);
        $values = RadiobuttonInfo::add_value_to_array($values, RadiobuttonInfo::Presentation_ID, RadioButtonInfo::Presentation, RadioButtonInfo::Presentation_Info);
        $values = RadiobuttonInfo::add_value_to_array($values, RadiobuttonInfo::Technical_quality_ID, RadioButtonInfo::Technical_quality, RadioButtonInfo::Technical_quality_Info);
        $values = RadiobuttonInfo::add_value_to_array($values, RadiobuttonInfo::Overall_rating_ID, RadioButtonInfo::Overall_rating, RadioButtonInfo::Overall_rating_Info);
        $values = RadiobuttonInfo::add_value_to_array($values, RadiobuttonInfo::Amount_of_rewriting_ID, RadioButtonInfo::Amount_of_rewriting, RadioButtonInfo::Amount_of_rewriting_Info);
        $values = RadiobuttonInfo::add_value_to_array($values, RadiobuttonInfo::Reviewers_expertise_ID, RadioButtonInfo::Reviewers_expertise, RadioButtonInfo::Reviewers_expertise_Info);
    
        return $values;
    }
}
?>