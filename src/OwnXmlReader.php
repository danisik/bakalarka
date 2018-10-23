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
//		Last update on:		17-10-2018
//      Encoding: utf-8 no BOM
//

//
//      Class using XML Reader to get saved values from XML file (configuration.xml preffered)
// 
class OwnXmlReader {
    
    //XML attribute which contains text
    private $XMLTextAttribute = 'Text';

    //year of actual conference
    private $yearOfConference = 0;
    //instruction header above evaluation
    private $instructionHeader = '';
    //instruction text above evaluation
    private $instructionText = '';
    //warning instruction text above evaluation
    private $instructionWarning = '';
    //text for watermark
    private $watermarkText = '';
    
    //
    //read given xml file (configuration.xml) and extract saved values into variables
    //$configurationPath - path of configuration file
    //
    function readXMLFile($configurationPath) {
        $reader = simplexml_load_file($configurationPath);
        
        $this->yearOfConference = $reader->yearOfConference;
        $this->instructionHeader = $reader->instructionHeader;
        $this->instructionText = $reader->instructionText;
        $this->instructionWarning = $reader->instructionWarning;
        $this->watermarkText = strtoupper($reader->watermarkText);                                      
    }
    
    function getInstructionHeader() {
        return $this->instructionHeader;
    }
        
    function getInstructionText() {
        return $this->instructionText;
    } 
    
    function getInstructionWarning() {
        return $this->instructionWarning;
    }
    
    function getYearOfConference() {
        return $this->yearOfConference;
    }
    
    function getWatermarkText() {
        return $this->watermarkText;
    }
}
?>