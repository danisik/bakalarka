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
//      Class manipulating with text and calculating font size for text
// 
class TextConversioner {

    //max strlen of instructions above evaluation
    private $lengthOfInstructions = 450;
    //max strlen of title before changing the font
    private $maxBaseLengthOfTitle = 40;
    //minimum font-size of title for better displaying title of document
    private $minFontSizeOfTitle = 24;
    //max strlen of title in header before changing the font
    private $maxBaseLengthOfHeaderTitle = 40;
    //minimum font-size of title for better displaying title of document
    private $minFontSizeOfHeaderTitle = 16;
    //max strlen of info before changing the font
    private $maxBaseLengthOfInfo = 450;
    //minimum font-size of info
    private $minFontSizeOfInfo = 10;
    
    //
    //calculate font of text, if is needed to lower default given font
    //$type - type of text (title, header_title, info, .....)
    //$fontSize - default font size of text
    //$text - text to be judged
    //
    //return method which recognize if text needs to be rounded or not
    function checkText($type, $fontSize, $text) {
    
        $values = array(
            "font" => $fontSize,
            "text" => $text
        );
        
        $strLengthOfText = mb_strlen($text);
        $maxLength = 0;
        
        switch($type) {
            case Types::TITLE:
                $maxLength = $this->maxBaseLengthOfTitle;
                break;
            case Types::HEADER_TITLE:
                $maxLength = $this->maxBaseLengthOfHeaderTitle;
                break;
            case Types::INFO:
                $maxLength = $this->maxBaseLengthOfInfo;
                break;
            default:
                throw new Exception("Bad type of text selected.");
                break;
        }    
        
        if($strLengthOfText <= $maxLength) {
            return $values;
        }
        else {
            //calculate font and round it down
            $newFontSize = round(($fontSize * $maxLength) / $strLengthOfText, 0, PHP_ROUND_HALF_DOWN);             
            return $this->reduceTextIfTooLong($type, $fontSize, $maxLength, $newFontSize, $text);
        }
    }
    
    //
    //calculate font of text, if is needed to lower default given font
    //$type - type of text (title, header_title, info, .....)
    //$oldfFontSize - default font size of text
    //$oldMaxBaseLengthOfText - default max length of text, depends on type
    //$newFontSize - calculated font size
    //$text - text to be judged
    //
    //return $values - array contains font size of given text and given text (if font is lower than minimum(which is set), then calculate new length and reduce text to calculated length)
    function reduceTextIfTooLong($type, $oldFontSize, $oldMaxBaseLengthOfText, $newFontSize, $text) {
        $minFontSize = 0;
        switch($type) {
            case Types::TITLE:
                $minFontSize = $this->minFontSizeOfTitle;
                break;
            case Types::HEADER_TITLE:
                $minFontSize = $this->minFontSizeOfHeaderTitle;
                break;
            case Types::INFO:
                $minFontSize = $this->minFontSizeOfInfo;
                break;
            default:
                throw new Exception("Bad type of text selected.");
                break;
        }
        
        
        $values = array(
            "font" => $newFontSize,
            "text" => $text
        );

        if ($newFontSize < $minFontSize) {
            $newLengthOfText = round(($oldFontSize / $minFontSize) * $oldMaxBaseLengthOfText, 0, PHP_ROUND_HALF_DOWN);    
            //-3 because we want last 3 characters as 3 dots
            $roundedText = substr($text, 0, $newLengthOfText - 3);
            $roundedText .= "...";  
                                                                                                                      
            $values["font"] = $minFontSize;
            $values["text"] = $roundedText;
        }                            
        
        return $values;  
    }
    
    /*
    function displaySpecialCharacters($text) {
        return iconv("UTF-8", "Windows-1250", $text);
    }
    */
}
?>