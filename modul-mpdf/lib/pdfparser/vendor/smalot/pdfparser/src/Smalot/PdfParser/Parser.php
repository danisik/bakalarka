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
        //unset($parser);
        //ob_end_clean();
    
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
                      
            //print_r($part);        
            switch ($part[0]) {
                case '[':                
                    $elements = array();

                    foreach ($part[1] as $sub_element) {
                        $sub_type   = $sub_element[0];
                        $sub_value  = $sub_element[1];
                        $elements[] = $this->parseHeaderElement($sub_type, $sub_value, $document);
                    }

                    $header = new Header($elements, $document);
                    
                    $key = null;
                    $value = null;
                    $type = null;
                            
                    list($key, $value, $type) = $this->extractElement($header);
                            
                    if ($key != null && $value != null && $type != null) {
                        $this->formElementsData[$type][$key] = $value;        
                    }
                    break;

                case '<<':                    
                    $header = $this->parseHeader($part[1], $document);
                            //print_r($header->getElements());
                            //echo "<br>2<br>";
                            
                    $key = null;
                    $value = null;
                    $type = null;
                            
                    list($key, $value, $type) = $this->extractElement($header);
                            
                    if ($key != null && $value != null && $type != null) {
                        $this->formElementsData[$type][$key] = $value;        
                    }
                    
                    break;

                case 'stream':                                
                    $content = isset($part[3][0]) ? $part[3][0] : $part[1];                                                         
                                   
                    if ($header->get('Type')->equals('ObjStm')) {
                        $match = array();              
                        //echo "here";                                           
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
                            
                            $key = null;
                            $value = null;
                            $type = null;
                            
                            list($key, $value, $type) = $this->extractElement($sub_header);
                            
                            if ($key != null && $value != null && $type != null) {
                                $this->formElementsData[$type][$key] = $value;        
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
     *
     * @return key, value and type of element
     */
    protected function extractElement($header) {
        $key = null;
        $value = null;
        $type = null;
        
        @$elementKey = $header->getElements()['T'];                                                      
        if ($elementKey != null) {
            if (strpos($elementKey->getContent(), 'group') !== false) $type = 'groups';
            else if (strpos($elementKey->getContent(), 'textarea') !== false) $type = 'textareas';
                       
            if ($type != null) {
                $key = $elementKey->getContent();
                @$elementValue = $header->getElements()['V'];
                if ($elementValue != null) {
                    $value = $elementValue->getContent();
                }    
            }
        }
        
        return array($key, $value, $type);
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