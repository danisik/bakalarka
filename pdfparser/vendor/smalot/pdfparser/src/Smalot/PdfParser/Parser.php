<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  SÃ©bastien MALOT <sebastien@malot.fr>
 * @date    2017-01-03
 * @license LGPLv3
 * @url     <https://github.com/smalot/pdfparser>
 *
 *  PdfParser is a pdf library written in PHP, extraction oriented.
 *  Copyright (C) 2017 - SÃ©bastien MALOT <sebastien@malot.fr>
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
use Smalot\PdfParser\Element\ElementXRef;
    
/**
 * Class Parser
 *
 * @package Smalot\PdfParser
 */
class Parser
{   
    /**
     * @var PDFObject[]
     */
    protected $objects = array();
    
    protected $formElementsData = array('groups' => array(), 'textareas' => array());
    
    protected $groups = array();
    
    protected $textareas = array(); 

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * @param $filename
     * @return Document
     * @throws \Exception
     */
    public function parseFile($filename)
    {
        $content = file_get_contents($filename);
        /*
         * 2018/06/20 @doganoo as multiple times a
         * users have complained that the parseFile()
         * method dies silently, it is an better option
         * to remove the error control operator (@) and
         * let the users know that the method throws an exception
         * by adding @throws tag to PHPDoc.
         *
         * See here for an example: https://github.com/smalot/pdfparser/issues/204
         */
        return $this->parseContent($content);
    }

    /**
     * @param $content
     * @return Document
     * @throws \Exception
     */
    public function parseContent($content)
    {
    
        // Create structure using TCPDF Parser.
        ob_start();
        @$parser = new \TCPDF_PARSER(ltrim($content));
        list($xref, $data) = $parser->getParsedData();

        unset($parser);
        ob_end_clean();
    
        if (isset($xref['trailer']['encrypt'])) {
            throw new \Exception('Secured pdf file are currently not supported.');
        }

        if (empty($data)) {
            throw new \Exception('Object list not found. Possible secured file.');
        }
        
        // Create destination object.
        $document      = new Document();
        $this->objects = array();
        $this->groups = array();
        $this->textareas = array();

       
        foreach ($data as $id => $structure) {               
            $this->parseObject($id, $structure, $document);
            unset($data[$id]);
        }

        $document->setTrailer($this->parseTrailer($xref['trailer'], $document));
        $document->setObjects($this->objects);
        
        if (sizeof($this->formElementsData['groups']) == 0 && sizeof($this->formElementsData['textareas']) == 0) {
            $values = explode("<<", $content);            
            foreach($values as $key => $value) {
            
                $sub_values = explode("/", $value);
                $not_needed_main_index = array("T", "(", ")", " ");
                $not_needed_main_value = array("V", "(", ")", "&#2013266175;", "&#2013266174;"); 
                $main_index = "";
                $index = "";     
                $main_value = "";           
                    
                if (strpos($value, "/DV/Off/FT/Btn/Ff 49152/Kids")) {
                    //<</DA(/F3 0 Tf 0.0 0.0 0.4 rg)/DV/Off/FT/Btn/Ff 49152/Kids[262 0 R 265 0 R 268 0 R 271 0 R 274 0 R 277 0 R 280 0 R 283 0 R 286 0 R 289 0 R 292 0 R]/T(group7)/TU(þÿ)/V/7>>
                    //ACRO PRO
                    
                    $index = str_replace($not_needed_main_index, "", $sub_values[9]);      
                    $main_value = explode(">>", $sub_values[12])[0];      
                    $main_index = "groups";      
                }
                else if (strpos($value, "/FT /Btn /Ff 49152 /Kids")) {
                    //300 0 obj <</Type /Annot /Subtype /Widget /NM (0014-3001) /M (D:20190226105951) /Rect [0 0 0 0 ] /FT /Btn /Ff 49152 /Kids [31 0 R 34 0 R 37 0 R 40 0 R 43 0 R 46 0 R 49 0 R 52 0 R 55 0 R 58 0 R 61 0 R ] /V /0 /DV /Off /T (group0) >> endobj
                    //EVINCE
                    
                    $index = str_replace($not_needed_main_index, "", $sub_values[16]);      
                    $main_value = $sub_values[13];
                    $main_index = "groups";               
                }        
                else if (strpos($value, "/Subtype/Widget/T(textarea")) {
                    //<</AP<</N 751 0 R>>/BS<</S/S/W 1>>/DA(/F1 9.9 Tf 0.000 g)/DV()/F 4/FT/Tx/Ff 4198400/H/N/M(D:20190226100627)/MK<</BC[0.6 0.6 0.72]/BG[0.975 0.975 0.975]>>/NM(0295-5091)/P 3 0 R/R 0/Rect[42.52 91.228 552.76 214.978]/Subtype/Widget/T(textarea0)/TU(þÿ)/Type/Annot/V(Nic)>>
                    //ACRO PRO
                    
                    $index = str_replace($not_needed_main_index, "", $sub_values[9]);      
                    $main_value = explode(">>", $sub_values[13])[0];     
                    $main_value = str_replace($not_needed_main_value, "", $main_value);
                    $main_index = "textareas";           
                }
                else if (strpos($value, "] >> /T (textarea")) {
                    //299 0 obj <</Type /Annot /Subtype /Widget /Rect [42.52 152.382 552.76 276.132 ] /F 4 /FT /Tx /H /N /R 0 /Ff 4198400 /BS <</W 1 /S /S >> /MK <</BC [0.6 0.6 0.72 ] /BG [0.975 0.975 0.975 ] >> /T (textarea4) /TU (þÿ) /DV () /DA (/F1 9.9 Tf 0.000 g) /NM (0299-5095) /M (D:20190226105951) /V (þÿ s h i t t y 5) >> endobj
                    //EVINCE
                    
                    $index = str_replace($not_needed_main_index, "", $sub_values[3]);      
                    $main_value = explode(">>", $sub_values[10])[0];
                    $main_value = mb_convert_encoding($main_value, "HTML-ENTITIES");  
                    $main_value = str_replace($not_needed_main_value, "", $main_value);
                    if ($main_value[0] == ' ') $main_value = substr($main_value, 1, strlen($main_value));
                    $main_index = "textareas";        
                }
                
                $this->formElementsData[$main_index][$index] = $main_value;
            }
        }
        $document->setFormElementsData($this->formElementsData);
        
        return $document;
    }

    protected function parseTrailer($structure, $document)
    {
        $trailer = array();

        foreach ($structure as $name => $values) {
            $name = ucfirst($name);

            if (is_numeric($values)) {
                $trailer[$name] = new ElementNumeric($values, $document);
            } elseif (is_array($values)) {
                $value          = $this->parseTrailer($values, null);
                $trailer[$name] = new ElementArray($value, null);
            } elseif (strpos($values, '_') !== false) {
                $trailer[$name] = new ElementXRef($values, $document);
            } else {
                $trailer[$name] = $this->parseHeaderElement('(', $values, $document);
            }
        }

        return new Header($trailer, $document);
    }

    /**
     * @param string   $id
     * @param array    $structure
     * @param Document $document
     */
    protected function parseObject($id, $structure, $document)
    {
        $header  = new Header(array(), $document);
        $content = '';

        foreach ($structure as $position => $part) {
            switch ($part[0]) {
                case '[':
                    $elements = array();

                    foreach ($part[1] as $sub_element) {
                        $sub_type   = $sub_element[0];
                        $sub_value  = $sub_element[1];
                        $elements[] = $this->parseHeaderElement($sub_type, $sub_value, $document);
                    }

                    $header = new Header($elements, $document);
                    break;

                case '<<':
                    $header = $this->parseHeader($part[1], $document);
                    break;

                case 'stream':
                    $content = isset($part[3][0]) ? $part[3][0] : $part[1];

                    if ($header->get('Type')->equals('ObjStm')) {
                        $match = array();

                        // Split xrefs and contents.
                        preg_match('/^((\d+\s+\d+\s*)*)(.*)$/s', $content, $match);
                        $content = $match[3];

                        // Extract xrefs.
                        $xrefs = preg_split(
                            '/(\d+\s+\d+\s*)/s',
                            $match[1],
                            -1,
                          PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
                        );
                        $table = array();

                        foreach ($xrefs as $xref) {
                            list($id, $position) = explode(' ', trim($xref));
                            $table[$position] = $id;
                        }

                        ksort($table);

                        $ids       = array_values($table);
                        $positions = array_keys($table);

                        foreach ($positions as $index => $position) {
                            $id            = $ids[$index] . '_0';
                            $next_position = isset($positions[$index + 1]) ? $positions[$index + 1] : strlen($content);
                            $sub_content   = substr($content, $position, $next_position - $position);

                            $sub_header         = Header::parse($sub_content, $document);
                            
                            if ($sub_header->getElements()['groups'] != null) {
                                foreach ($sub_header->getElements()['groups'] as $key => $value) {
                                    $this->formElementsData['groups'][$key] = $value;
                                }
                            }
                            if ($sub_header->getElements()['textareas'] != null) {
                                foreach ($sub_header->getElements()['textareas'] as $key => $value) {
                                    $this->formElementsData['textareas'][$key] = $value;
                                }
                            }
                            $object             = PDFObject::factory($document, $sub_header, '');
                            $this->objects[$id] = $object;
                        }

                        // It is not necessary to store this content.
                        $content = '';

                        return;
                    }
                    break;

                default:
                    if ($part != 'null') {
                        $element = $this->parseHeaderElement($part[0], $part[1], $document);

                        if ($element) {
                            $header = new Header(array($element), $document);
                        }
                    }
                    break;

            }
        }
        
        if (!isset($this->objects[$id])) {
            $this->objects[$id] = PDFObject::factory($document, $header, $content);
        }
    }

    /**
     * @param array    $structure
     * @param Document $document
     *
     * @return Header
     * @throws \Exception
     */
    protected function parseHeader($structure, $document)
    {
        $elements = array();
        $count    = count($structure);

        for ($position = 0; $position < $count; $position += 2) {
            $name  = $structure[$position][1];
            $type  = $structure[$position + 1][0];
            $value = $structure[$position + 1][1];

            $elements[$name] = $this->parseHeaderElement($type, $value, $document);
        }

        return new Header($elements, $document);
    }

    /**
     * @param $type
     * @param $value
     * @param $document
     *
     * @return Element|Header
     * @throws \Exception
     */
    protected function parseHeaderElement($type, $value, $document)
    {
        
        switch ($type) {
            case '<<':
                return $this->parseHeader($value, $document);

            case 'numeric':
                return new ElementNumeric($value, $document);

            case 'boolean':
                return new ElementBoolean($value, $document);

            case 'null':
                return new ElementNull($value, $document);

            case '(':
                if ($date = ElementDate::parse('(' . $value . ')', $document)) {
                    return $date;
                } else {
                    return ElementString::parse('(' . $value . ')', $document);
                }

            case '<':
                return $this->parseHeaderElement('(', ElementHexa::decode($value, $document), $document);

            case '/': 
                return ElementName::parse('/' . $value, $document);

            case 'ojbref': // old mistake in tcpdf parser
            case 'objref':
                return new ElementXRef($value, $document);

            case '[':
                $values = array();

                foreach ($value as $sub_element) {
                    $sub_type  = $sub_element[0];
                    $sub_value = $sub_element[1];
                    $values[]  = $this->parseHeaderElement($sub_type, $sub_value, $document);
                }

                return new ElementArray($values, $document);

            case 'endstream':
            case 'obj': //I don't know what it means but got my project fixed.
            case '':
                // Nothing to do with.
                break;

            default:
                throw new \Exception('Invalid type: "' . $type . '".');
        }
    }
    
        public function setFormElementsData($formElementsData) {
        $this->formElementsData = $formElementsData;
    }
    
    public function getFormElementsData() {
        return $this->formElementsData;    
    }
}
