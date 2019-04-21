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
?>