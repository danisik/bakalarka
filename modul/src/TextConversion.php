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
//		Last update on:		27-03-2019
//      Encoding: utf-8 no BOM
//

//
//      Class manipulating with text and calculating font size for text
// 
class TextConversioner {

    //max strlen of instructions above evaluation
    private $length_of_instructions = 450;
    //max strlen of title before changing the font
    private $max_base_length_of_title = 40;
    //minimum font-size of title for better displaying title of document
    private $min_font_size_of_title = 24;
    //max strlen of title in header before changing the font
    private $max_base_length_of_header_title = 30;
    //minimum font-size of title for better displaying title of document
    private $min_font_size_of_header_title = 17;
    //max strlen of info before changing the font
    private $max_base_length_of_info = 450;
    //minimum font-size of info
    private $min_font_size_of_info = 10;
    //max strlen of reviewer name before changing the font
    private $max_base_length_of_name = 40;
    //minimum font-size of reviewer name
    private $min_font_size_of_name = 17;
    
    
    
    //
    //calculate font of text, if is needed to lower default given font
    //$type - type of text (title, header_title, info, .....)
    //$font_size - default font size of text
    //$text - text to be judged
    //
    //return method which recognize if text needs to be rounded or not
    function check_text($type, $font_size, $text) {
    
        $values = array(
            "font" => $font_size,
            "text" => $text
        );
        
        $str_length_of_text = mb_strlen($text);
        $max_length = 0;
        
        switch($type) {
            case Instruction::TITLE:
                $max_length = $this->max_base_length_of_title;
                break;
            case Instruction::HEADER_TITLE:
                $max_length = $this->max_base_length_of_header_title;
                break;
            case Instruction::INFO:
                $max_length = $this->max_base_length_of_info;
                break;
            case Instruction::REVIEWER_NAME:
                $max_length = $this->max_base_length_of_name;
                break;
            default:
                throw new Exception("Bad type of text selected.");
                break;
        }    
        
        if($str_length_of_text <= $max_length) {
            return $values;
        }
        else {
            //calculate font and round it down
            $new_font_size = round(($font_size * $max_length) / $str_length_of_text, 0, PHP_ROUND_HALF_DOWN);             
            return $this->reduce_text_if_too_long($type, $font_size, $max_length, $new_font_size, $text);
        }
    }
    
    //
    //calculate font of text, if is needed to lower default given font
    //$type - type of text (title, header_title, info, .....)
    //$old_font_size - default font size of text
    //$old_max_base_length_of_text - default max length of text, depends on type
    //$new_font_size - calculated font size
    //$text - text to be judged
    //
    //return $values - array contains font size of given text and given text (if font is lower than minimum(which is set), then calculate new length and reduce text to calculated length)
    function reduce_text_if_too_long($type, $old_font_size, $old_max_base_length_of_text, $new_font_size, $text) {
        $min_font_size = 0;
        switch($type) {
            case Instruction::TITLE:
                $min_font_size = $this->min_font_size_of_title;
                break;
            case Instruction::HEADER_TITLE:
                $min_font_size = $this->min_font_size_of_header_title;
                break;
            case Instruction::INFO:
                $min_font_size = $this->min_font_size_of_info;
                break;
            case Instruction::REVIEWER_NAME:
                $min_font_size = $this->min_font_size_of_name;
                break;
            default:
                throw new Exception("Bad type of text selected.");
                break;
        }
        
        
        $values = array(
            "font" => $new_font_size,
            "text" => $text
        );

        if ($new_font_size < $min_font_size) {
            $new_length_of_text = round(($old_font_size / $min_font_size) * $old_max_base_length_of_text, 0, PHP_ROUND_HALF_DOWN);    
            //-3 because we want last 3 characters as 3 dots
            $rounded_text = substr($text, 0, $new_length_of_text - 3);
            $rounded_text .= "...";  
                                                                                                                      
            $values["font"] = $min_font_size;
            $values["text"] = $rounded_text;
        }                            
        
        return $values;  
    }
}
?>