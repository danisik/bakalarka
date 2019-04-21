<?php
//
//		CONFERENCE PORTAL PROJECT
//		VERSION 3.1.0
//
//		Copyright (c) 2010-2019 Dept. of Computer Science & Engineering,
//		Faculty of Applied Sciences, University of West Bohemia in Plzeò.
//		All rights reserved.
//
//		Code written by:	Vojtech Danisik
//		Last update on:		27-03-2019
//      Encoding: utf-8 no BOM
//
//
//      Enumerate classes
//

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
?>