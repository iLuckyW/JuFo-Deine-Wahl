<?php
require_once "modules/pdf/StudentPrintout.php";
//handle Button functions
if(isset($_POST["function"])) {
    switch($_POST["function"]) {
        case("printPdf"):
            define('FPDF_FONTPATH', 'libaries/font/');
            $pdf = new StudentPrintout();
            $pdf->configureStandard();
            $pdf->printHead();
            $pdf->showPdf();
            break;
        default:
    }
}
?>