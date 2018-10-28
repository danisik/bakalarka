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
//		Last update on:		28-10-2018
//      Encoding: utf-8 no BOM
//

//
//      Class using XML Reader to get saved values from XML file (configuration.xml preffered)
// 
class OwnXmlReader {
    
    //XML attribute which contains text
    private $XML_text_attribute = 'Text';

    //year of actual conference
    private $year_of_conference = 0;
    //instruction header above evaluation
    private $instruction_header = '';
    //instruction text above evaluation
    private $instruction_text = '';
    //warning instruction text above evaluation
    private $instruction_warning = '';
    //text for watermark
    private $watermark_text = '';
    
    //
    //read given xml file (configuration.xml) and extract saved values into variables
    //$configuration_path - path of configuration file
    //
    function read_XML_file($configuration_path) {
        $reader = simplexml_load_file($configuration_path);
        
        $this->year_of_conference = $reader->year_of_conference;
        $this->instruction_header = $reader->instruction_header;
        $this->instruction_text = $reader->instruction_text;
        $this->instruction_warning = $reader->instruction_warning;
        $this->watermark_text = strtoupper($reader->watermark_text);                                      
    }
    
    function getInstruction_header() {
        return $this->instruction_header;
    }
        
    function getInstruction_text() {
        return $this->instruction_text;
    } 
    
    function getInstruction_warning() {
        return $this->instruction_warning;
    }
    
    function getYear_of_conference() {
        return $this->year_of_conference;
    }
    
    function getWatermark_text() {
        return $this->watermark_text;
    }
}
?>