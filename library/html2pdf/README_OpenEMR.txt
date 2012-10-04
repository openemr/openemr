This html2pdf directory was created by:

1. Downloading HTML2PDF 3.31 from:
   https://sourceforge.net/projects/html2fpdf/
   and installing it as the library/html2pdf directory.

2. Downloading FPDI 1.4.2 and FPDF_TPL 1.2 from:
   http://www.setasign.de/products/pdf-php-solutions/fpdi/downloads/
   and installing them into the library/html2pdf/fpdi directory.

3. Removing the examples directory from the html2pdf directory.

4. Modifying library/html2pdf/fpdi/fpdf_tpl.php so that
   FPDF_TPL extends MyPDF instead of FPDF.  Note that FPDI extends
   FPDF_TPL.

5. Modifying library/html2pdf/html2pdf.class.php to create its member
   pdf object as an instance of FPDI instead of MyPDF.

At this point the HTML2PDF class now includes the FPDI features.

