# Frequently asked questions

###### *Q*: It does not work and everything is broken.

*A*: Please, try to execute the command manually in your shell. Snappy is a thin PHP wrapper and most likely your issue is with wkhtmltopdf itself or is already described in this FAQ. If not, feel free to open the issue in Snappy issue tracker.

How to get the command to execute - 

```php
var_dump($snappy->getCommand('http://google.com', 'test.pdf'), array('some' => 'option'));
```

Please, note that wkhtmltopdf takes only input url or file name as a source.


###### *Q*: My tables are broken when it is rendered on multiple pages with break.

*A*: Add ```thead``` and ```tbody``` tags. Add the following css 
```css
table, tr, td, th, tbody, thead, tfoot {
    page-break-inside: avoid !important;
}
```


###### *Q*: I have a PNG with a transparent background. When generating a PDF, the background turns black.

*A*: It is wkhtmltopdf bug as described in https://github.com/wkhtmltopdf/wkhtmltopdf/issues/2214. You should update wkhtmltopdf to at least 0.12.3-dev


###### *Q*: Is there a way to secure the pdf so it can't be edited?

*A*:  There is no way to add a password via wkhtmltopdf, but there is a way via other linux tools like pdftk


###### *Q*: We are using wkhtmltopdf to export html to pdf. It breaks the HTML in two pages of pdf. Can we add a break?

*A*: It is known problem of `wkhtmltopdf`. You can use css `page-break-after`, like:
```html
<style type="text/css">
    .page {
        overflow: hidden;
        page-break-after: always;
    }
</style>

<div class="page">
   new page
</div>
```


###### *Q*: It says `wkhtmltopdf: cannot connect to X server` or `xvfb-run: error: Xvfb failed to start.`

*A*: Please, check your `wkhtmltopdf` version. It is recommended to use at least `0.12.2.1` and what is important - starting from `wkhtmltopdf >= 0.12.2` it doesn't require X server or emulation anymore. You can download new version from http://wkhtmltopdf.org/downloads.html or install via composer for Linux servers as stated in [README](https://github.com/KnpLabs/snappy#wkhtmltopdf-binary-as-composer-dependencies). If there is no possibility to update `wkhtmltopdf`, please check http://stackoverflow.com/questions/9604625/wkhtmltopdf-cannot-connect-to-x-server


###### *Q*: My PDF is always generated for a small screen resolution\I always receive a mobile version.

*A*: It is well-known issue of wkhtmltopdf, you can check https://github.com/wkhtmltopdf/wkhtmltopdf/issues/1508. One of solutions is to use xvbf and to setup xvbf resolution to desired one though a simple bit of css such as `zoom: .75;` would be sufficient in most cases.

###### *Q*: My chars with accents in HTML document are not correctly rendered.

*A*: Make sure that you have set `<meta charset="UTF-8" />`

###### *Q*: My document text is not correctly rendered, it is just black squares

*A*: Make sure you have installed `xfonts-base`, `xfonts-75dpi` and `urw-fonts`
