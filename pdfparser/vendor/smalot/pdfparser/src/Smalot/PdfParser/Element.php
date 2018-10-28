<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2017-01-03
 * @license LGPLv3
 * @url     <https://github.com/smalot/pdfparser>
 *
 *  PdfParser is a pdf library written in PHP, extraction oriented.
 *  Copyright (C) 2017 - Sébastien MALOT <sebastien@malot.fr>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program.
 *  If not, see <http://www.pdfparser.org/sites/default/LICENSE.txt>.
 *
 */

namespace Smalot\PdfParser;

use Smalot\PdfParser\Element\ElementArray;
use Smalot\PdfParser\Element\ElementBoolean;
use Smalot\PdfParser\Element\ElementDate;
use Smalot\PdfParser\Element\ElementHexa;
use Smalot\PdfParser\Element\ElementName;
use Smalot\PdfParser\Element\ElementNull;
use Smalot\PdfParser\Element\ElementNumeric;
use Smalot\PdfParser\Element\ElementString;
use Smalot\PdfParser\Element\ElementStruct;
use Smalot\PdfParser\Element\ElementXRef;

/**
 * Class Element
 *
 * @package Smalot\PdfParser
 */
class Element
{
    /**
     * @var Document
     */
    protected $document = null;

    /**
     * @var mixed
     */
    protected $value = null;

    /**
     * @param mixed    $value
     * @param Document $document
     */
    public function __construct($value, Document $document = null)
    {
        $this->value    = $value;
        $this->document = $document;
    }

    /**
     *
     */
    public function init()
    {

    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function equals($value)
    {
        return ($value == $this->value);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function contains($value)
    {
        if (is_array($this->value)) {
            /** @var Element $val */
            foreach ($this->value as $val) {
                if ($val->equals($value)) {
                    return true;
                }
            }

            return false;
        } else {
            return $this->equals($value);
        }
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)($this->value);
    }

    /**
     * @param string   $content
     * @param Document $document
     * @param int      $position
     *
     * @return array
     * @throws \Exception
     */
    public static function parse($content, Document $document = null, &$position = 0)
    {
        $args        = func_get_args();
        $only_values = isset($args[3]) ? $args[3] : false;
        $content     = trim($content);
        $values      = array('groups' => array(), 'textareas' => array());

        do {
            $old_position = $position;

            if (!$only_values) {
                if (!preg_match('/^\s*(?P<name>\/[A-Z0-9\._]+)(?P<value>.*)/si', substr($content, $position), $match)) {
                    break;
                } else {
                    $name     = ltrim($match['name'], '/');
                    $value    = $match['value'];
                    $position = strpos($content, $value, $position + strlen($match['name']));
                }
            } else {
                $name  = count($values);
                $value = substr($content, $position);
            }
            
            if ($element = ElementName::parse($value, $document, $position)) {
                if (strpos($value, 'Ff 4096') && strpos($value, 'Widget')) echo ('TEXTAREA - '.$value.'<br>');
                $values = Element::findTextarea($values, $value);
                $values[$name] = $element;
            } elseif ($element = ElementXRef::parse($value, $document, $position)) {
                $values[$name] = $element;
            } elseif ($element = ElementNumeric::parse($value, $document, $position)) {
                $values[$name] = $element;
            } elseif ($element = ElementStruct::parse($value, $document, $position)) {
                $values[$name] = $element;
            } elseif ($element = ElementBoolean::parse($value, $document, $position)) {
                $values[$name] = $element;
            } elseif ($element = ElementNull::parse($value, $document, $position)) {
                $values[$name] = $element;
            } elseif ($element = ElementDate::parse($value, $document, $position)) {
                $values[$name] = $element; 
            } elseif ($element = ElementString::parse($value, $document, $position)) {
                if (strpos($value, 'radioButton') && strpos($value, '/ZaDb 8.23 Tf 0 g')) echo ('RADIO - '.$value.'<br>');
                $values = Element::findRadioGroup($values, $value);
                $values[$name] = $element;
            } elseif ($element = ElementHexa::parse($value, $document, $position)) {
                $values[$name] = $element;
            } elseif ($element = ElementArray::parse($value, $document, $position)) {
                $values[$name] = $element;
            } else {
                $position = $old_position;
                break;
            }
        } while ($position < strlen($content));
        
        return $values;
    }
    
    
    //format - (group0)/TU()/V/10
    //split to array by '/', where [0] = group0 - which group (which evaluation (like Originality, Significance, ..))
    //                             [1] = TU - unnecessary
    //                             [2] = V - unnecessary
    //                             [3] = 10 - selected group value (group of radiobuttons)  
        
    //other formats - unnecessary, because containts value too, so we filter them out
    //(/F4 0 Tf 0.0 0.0 0.4 rg)/DV/Off/FT/Btn/Ff 49152/Kids[449 0 R 450 0 R 451 0 R 452 0 R 453 0 R 454 0 R 455 0 R 456 0 R 457 0 R 458 0 R 459 0 R]/T(group0)/TU()/V/10
    //[449 0 R 450 0 R 451 0 R 452 0 R 453 0 R 454 0 R 455 0 R 456 0 R 457 0 R 458 0 R 459 0 R]/T(group0)/TU()/V/10
    
    //find group of radiobutton
    //$values - all values from pdf
    //$value - text to be assessed
    public static function findRadioGroup($values, $value) {
        if (strpos($value, 'group') && !strpos($value, '[') && !strpos($value, ']')) {
            $string = str_replace('(', '', $value);
            $string = str_replace(')', '', $string);
            $parsedData = explode('/', $string);
                    
            $groupName = $parsedData[0];
            $groupValue = $parsedData[3];
                    
            $values['groups'][$groupName] = $groupValue;    
        }
        return $values;
    }
    
    //format - /Widget/T(textarea0)/TU()/Type/Annot/V(main contributions)
    //split array by '/', where [0] = blank - unnecessary
    //                          [1] = Widget - unnecessary 
    //                          [2] = T(textarea0) - next we split this by '(' and on [1] we get name of textarea
    //                          [3] = TU() - unnecessary
    //                          [4] = Type - unnecessary
    //                          [5] = Annot - unnecessary
    //                          [6] = V(main contributions) - next we split this by '(' and on [1] we get text written on textarea
        
        
    //other formats - unnecessary, because containts value too, so we filter them out
    ///Tx/Ff 4198400/H/N/M(D:20181023190906)/MK<>/NM(0297-5091)/P 56 0 R/R 0/Rect[42.52 80.997 552.76 204.747]/Subtype/Widget/T(textarea0)/TU()/Type/Annot/V(main contributions)
    ///N/M(D:20181023190906)/MK<>/NM(0297-5091)/P 56 0 R/R 0/Rect[42.52 80.997 552.76 204.747]/Subtype/Widget/T(textarea0)/TU()/Type/Annot/V(main contributions)
    
    //find textarea
    //$values - all values from pdf
    //$value - text to be assessed
    public static function findTextarea($values, $value) {
        if (strpos($value, 'textarea') && !strpos($value, '[') && !strpos($value, ']')) {
            $parsedData = explode('/', $value);
                    
            $textareaName = explode('(', $parsedData[2])[1];
            $textareaName = str_replace(')', '', $textareaName);
                    
            @$textareaValue = explode('(', $parsedData[6])[1];
            $textareaValue = str_replace(')', '', $textareaValue);
            $values['textareas'][$textareaName] = $textareaValue;
        }
        
        return $values;
    }
}
