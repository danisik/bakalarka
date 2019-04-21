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
?>