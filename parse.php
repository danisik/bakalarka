<?php

    error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE); 
    //path to working directory
    $root = $_SERVER['DOCUMENT_ROOT'].'/';
    //path to mpdf in working directory
    $path = $root.'TSD/';
    
    $parser = $_GET['parser'];
    $autoload = '';                         
    
    include ($path.'src/Enumerates.php');
    include ($path.'src/OwnXmlReader.php');
    include ($path.'src/TextConversion.php');
    include ($path.'src/HTMLElements.php');
    
    switch($parser) {
        case 'tcpdf':
            $autoload .= 'tcpdf';
            include ($path.'parsers/tcpdf/tcpdf_parser.php');
            break;
        case 'pdf2html':
            $autoload .= 'pdf2html';
            $pathAUTOLOAD = $path.'parsers/'.$autoload.'/';
            require ($pathAUTOLOAD.'vendor/autoload.php');
            break;    
        case 'pdftk':
            $autoload .= 'php_pdftk';
            $pathAUTOLOAD = $path.'parsers/'.$autoload.'/';
            require ($pathAUTOLOAD.'vendor/autoload.php');
            break;
        case 'pdf-parser':
            $autoload .= 'tc-lib-pdf-parser';
            $pathAUTOLOAD = $path.'parsers/'.$autoload.'/';
            require ($pathAUTOLOAD.'vendor/autoload.php');
            break;
    }
      
    

    $file = $path.'mpdf.pdf';
    
    switch($parser) {
        case 'tcpdf':
            $autoload .= 'tcpdf';
            break;
        case 'pdf2html':
            // change pdftohtml bin location
            Gufy\PdfToHtml\Config::set('pdftohtml.bin', $pathAUTOLOAD.'bin/pdftohtml.exe');
            
            // change pdfinfo bin location
            Gufy\PdfToHtml\Config::set('pdfinfo.bin', $pathAUTOLOAD.'bin/pdfinfo.exe');
            // initiate
            $pdf = new Gufy\PdfToHtml\Pdf($file);
            
            // convert to html and return it as [Dom Object](https://github.com/paquettg/php-html-parser)
            $html = $pdf->html();
            //var_dump($html);
            break;    
        case 'pdftk':     
            // Get form data fields
            $pdf = new mikehaertl\pdftk\Pdf($file);
            $data = $pdf->getDataFields();

            // Get data as array
            $arr = (array) $data;
            //$arr = $data->__toArray();
            var_dump($arr);
            break;
        case 'pdf-parser':
            $autoload .= 'tc-lib-pdf-parser';
            break;
    }
?>