This html2pdf library was updated by:

1. Removing all old files

2. Downloading HTML2PDF 4.5.0 from:
   https://github.com/spipu/html2pdf/releases (composer package)

3. Running composer command "composer install"

4. Requiring "setasign/fpdi": "1.6.*" for latest FPDI package

5. Removing the examples directory from the html2pdf directory.

6. Modifying library/html2pdf/_class/myPdf.class.php so that HTML2PDF_myPdf extends FPDI instead of TCPDF.
   Note that FPDI extends TCPDF.

7. Modifying interface/patient_file/report/custom_report.php line # 52 to require html2pdf/vendor/autoload.php rather html2pdf.class.php

8. Passing two new parameters "unicode and encoding" to HTML2PDF constructor in interface/patient_file/report/custom_report.php on line 57 and 58 respectively

9. Modifying library/html2pdf/vendor/setasign/fpdi/fpdi_bridge.php class removing second parameter "false" of "class_exists" method because TCPDF is autoloading

10. Removed examples directory from the TCPDF package.

At this point the HTML2PDF(version 4.5.0) with TCPDF(version 6.2.12) and FPDI(version 1.6.1) is installed and working.

