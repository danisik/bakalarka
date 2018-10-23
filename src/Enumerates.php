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
//      Enumerate classes
//

class Types { 
    const TITLE = "title"; 
    const HEADER_TITLE = "header_title";
    const INFO = "info"; 
    
    function getConstants() {
        $values = array();
        array_push($values, Types::TITLE);
        array_push($values, Types::HEADER_TITLE);
        array_push($values, Types::INFO);
        
        return $values;    
    }
}

class TextAreaInfo {
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
    
    function getConstants() {
        $values = array();
        
        $values[TextAreaInfo::Main_contributions_ID]['name'] = TextAreaInfo::Main_contributions;
        $values[TextAreaInfo::Main_contributions_ID]['info'] = TextAreaInfo::Main_contributions_info;
        
        $values[TextAreaInfo::Positive_aspects_ID]['name'] = TextAreaInfo::Positive_aspects;
        $values[TextAreaInfo::Positive_aspects_ID]['info'] = TextAreaInfo::Positive_aspects_info;
        
        $values[TextAreaInfo::Negative_aspects_ID]['name'] = TextAreaInfo::Negative_aspects;
        $values[TextAreaInfo::Negative_aspects_ID]['info'] = TextAreaInfo::Negative_aspects_info;
        
        $values[TextAreaInfo::Comment_ID]['name'] = TextAreaInfo::Comment;
        $values[TextAreaInfo::Comment_ID]['info'] = TextAreaInfo::Comment_info;
        
        $values[TextAreaInfo::Internal_comment_ID]['name'] = TextAreaInfo::Internal_comment;
        $values[TextAreaInfo::Internal_comment_ID]['info'] = TextAreaInfo::Internal_comment_info;
        
        return $values;
    }
}

class RadioButtonInfo {
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
    
    function getConstants() {
        $values = array();
        
        $values[RadioButtonInfo::Originality_ID]['name'] = RadioButtonInfo::Originality;
        $values[RadioButtonInfo::Originality_ID]['info'] = RadioButtonInfo::Originality_Info;
        
        $values[RadioButtonInfo::Significance_ID]['name'] = RadioButtonInfo::Significance;
        $values[RadioButtonInfo::Significance_ID]['info'] = RadioButtonInfo::Significance_Info;
        
        $values[RadioButtonInfo::Relevance_ID]['name'] = RadioButtonInfo::Relevance;
        $values[RadioButtonInfo::Relevance_ID]['info'] = RadioButtonInfo::Relevance_Info;
        
        $values[RadioButtonInfo::Presentation_ID]['name'] = RadioButtonInfo::Presentation;
        $values[RadioButtonInfo::Presentation_ID]['info'] = RadioButtonInfo::Presentation_Info;
        
        $values[RadioButtonInfo::Technical_quality_ID]['name'] = RadioButtonInfo::Technical_quality;
        $values[RadioButtonInfo::Technical_quality_ID]['info'] = RadioButtonInfo::Technical_quality_Info;
        
        $values[RadioButtonInfo::Overall_rating_ID]['name'] = RadioButtonInfo::Overall_rating;
        $values[RadioButtonInfo::Overall_rating_ID]['info'] = RadioButtonInfo::Overall_rating_Info;
        
        $values[RadioButtonInfo::Amount_of_rewriting_ID]['name'] = RadioButtonInfo::Amount_of_rewriting;
        $values[RadioButtonInfo::Amount_of_rewriting_ID]['info'] = RadioButtonInfo::Amount_of_rewriting_Info;
        
        $values[RadioButtonInfo::Reviewers_expertise_ID]['name'] = RadioButtonInfo::Reviewers_expertise;
        $values[RadioButtonInfo::Reviewers_expertise_ID]['info'] = RadioButtonInfo::Reviewers_expertise_Info;
    
        return $values;
    }
}
?>