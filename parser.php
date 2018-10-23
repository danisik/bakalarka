<?php
    //path to working directory
    $root = $_SERVER['DOCUMENT_ROOT'].'/';
    //path to mpdf in working directory
    $path = $root.'TSD/';
    //path for source files
    $pathSource = $path.'src/';
    //path to mpdf library
    $pathMPDF = $path.'parsers/pdfparser/';
    //path to configuration file
    $configurationPath = $path.'config/configuration.xml';
    //path to logo
    $pathToLogo = $path.'img/tsd-logo.png';
                                  
    require ($pathMPDF.'vendor/autoload.php');
    use Smalot\PdfParser\Parser;
    
    $parser = new \Smalot\PdfParser\Parser();                                        
    $pdf = $parser->parseFile($path.'mpdf.pdf');
    
    $data = $pdf->getFormElementsData();
    
    $groups = $data['groups'];
    $textareas = $data['textareas'];
    
    foreach ($groups as $key => $value) {
        echo($key.' => '.$value);
        echo('<br>');
    }
    
    echo ('<br><br>');
    
    foreach ($textareas as $key => $value) {
        echo($key.' => '.$value);
        echo('<br>');
    }
?>