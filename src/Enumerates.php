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
?>