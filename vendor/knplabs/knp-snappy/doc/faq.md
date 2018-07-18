# Frequently asked questions

###### *Q*: It does not work and everything is broken.

*A*: Please, try to execute the command manually in your shell. Snappy is a thin PHP wrapper and most likely your issue is with wkhtmltopdf itself or is already described in this FAQ. If not, feel free to open the issue in Snappy issue tracker.

Please, note that wkhtmltopdf takes only input URL(s) or file name(s) as source.


###### *Q*: How to get the command executed by Snappy?

*A*: You need to install any PSR-3 compliant logging library and call `setLogger()` method on the generator. It will 
log every command executed, its env vars and timeout. It will also log stdout and stderr whenever a command finishes, even if it fails.


###### *Q*: My tables are broken when it is rendered on multiple pages with break.

*A*: Add ```thead``` and ```tbody``` tags. Add the following css 
```css
table { page-break-inside:auto; }
tr    { page-break-inside:avoid; page-break-after:auto; }
thead { display:table-header-group; }
tfoot { display:table-footer-group; }
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

*A*: It is well-known issue of wkhtmltopdf, you can check https://github.com/wkhtmltopdf/wkhtmltopdf/issues/1508. One of solutions is to use xvfb and to setup xvfb resolution to desired one though a simple bit of css such as `zoom: .75;` would be sufficient in most cases.

###### *Q*: My chars with accents in HTML document are not correctly rendered.

*A*: Make sure that you have set `<meta charset="UTF-8" />` in your HTML document, and you used the option `"encoding" => "utf-8"`.

###### *Q*: My document text is not correctly rendered, it is just black squares

*A*: Make sure you have installed `xfonts-base`, `xfonts-75dpi` and `urw-fonts`

###### *Q*: How to convert page with relative links from stdin / How to use relative media URLs

*A*: When you convert an HTML file with relative links/media URLs into PDF, you need to either:
* Switch to absolute links/media URLs
* Or use `<base></base>` tag to specify what's the base URL of those relative links.

###### *Q*: How to generate a single PDF from multiple sources?

*A*: Snappy and wkhtmltopdf both support generating a single PDF from multiple sources. To do so, you need to provide an array of input rather than a string.

```php
<?php

$pdf = new \Knp\Snappy\Pdf(__DIR__ . '/vendor/bin/wkhtmltopdf-amd64');
$pdf->generate(['https://google.com', 'https://google.jp'], '/tmp/out/test.pdf');
// or
$pdf->generateFromHtml(['<html><body>Doc 1</body></html>', '<html><body>Doc 2</body></html>'], '/tmp/out/test.pdf');
```

###### *Q*: My chars with accents passed to wkhtmltopdf options are not correctly rendered, i.e. `footer-right => 'Página [page] de [toPage]'` is converted to 'PÃ¡gina 1 de 1'.

*A*: The answer is long here. We use `escapeshellarg` function to escape all the option value passed to `wkhtmltox`. `escapeshellarg` makes its escape based on server locale, so if you are  experiencing this issue - you can set 
```php
setlocale(LC_CTYPE, 'es_ES.UTF-8')
``` 

or any locale which is suitable for you. You should take into account that if given locale is not configured on the server - you will still have an issue. Check your locales installed via running
```bash
locale -a
```
If the needed locale is missing on the server - you should install/configure it.

###### *Q*: How to put an header/footer on every page of the PDF?

*A*: You need to provide either a valid file path or some HTML content. Note that your HTML document(s) needs to start with a valid doctype and have html, head and body tags, or wkhtmltopdf will fail to render the PDF properly.

*Note that this feature does not work with wkhtmltopdf compiled against unpatched Qt. Most of the time, wkhtmltopdf packages from Linux distributions are not fine. You should rather rely on the 
official version available on [wkhtmltopdf.org](https://wkhtmltopdf.org) or the version available from `h4cc/wkhtmltopdf` package.*

```php
<?php
require __DIR__ . '/vendor/autoload.php';

$header = <<<HTML
<!DOCTYPE html>
<html>
  <head><style type="text/css">p { color: #FF0000; }</style></head>
  <body><p>Lorem ipsum</p></body>
</html>
HTML;

$footer = <<<HTML
<!DOCTYPE html>
<html>
  <head><style type="text/css">p { color: #0000FF; }</style></head>
  <body><p>Lorem ipsum</p></body>
</html>
HTML;

// Without html extension you might face following error:
// Exit with code 1, due to unknown error.
$footerPath = tempnam('/tmp', 'footer') . '.html';
file_put_contents($footerPath, $footer);

$pdf = new \Knp\Snappy\Pdf(__DIR__ . '/vendor/bin/wkhtmltopdf-amd64');
$pdf->generateFromHtml('', '/tmp/out/test.pdf', ['header-html' => $header, 'footer-html' => $footerPath], true);
```

###### *Q*: Is it possible to include an header and/or footer only on some specific pages?

*A*: No, wkhtmtopdf does not allow this.

###### *Q*: When running wkhtmltopdf through Snappy, I got an exit code 5 or 6

*A*: It's usually due to bad environment variables. For example, on MacOS, you need to check the value of `DYLD_LIBRARY_PATH` (see [#27](https://github.com/KnpLabs/snappy/issues/27#issuecomment-7199659)). 
On Linux, you should check the value of `LD_LIBRARY_PATH`. Also note that, depending on the way you execute PHP, your environment variables might be reset for security reasons (for instance, look at `clear_env` on php-fpm).

###### *Q*: On Windows, when I generate a PDF nothing happens (there's no PDF file written)

*A*: You should check with sysinternals procmon if you experience `ACCESS_DENIED` error. If that's the case, you need to give execution permission to IIS users on wkhtmltopdf binary. Also, your user(s) should have write permissions on the temporary folder.

For more details see [#123](https://github.com/KnpLabs/snappy/issues/123).

###### *Q*: Snappy takes an endless amount of time to generate a PDF and eventually fails due to timeout

*A*: This is generally indicating some networking issues. It might be bad DNS record(s), some sporadic packet losses, unresponsive HTTP server ...

Note that if you use the PHP embedded server, you can't generate a PDF from an HTML page accessible from the same embedded server. 
Indeed, the embedded server never forks and does not use threads. That means it's not able to process two requests 
at the same time: it processes the first one, send the first response and only then starts to process the second one. 

###### *Q*: How to proceed when experiencing `ContentNotFound`, `ConnectionRefusedError` or timeouts?

*A*: When you experience errors like `ContentNotFound`, `ConnectionRefusedError` or timeouts it is hard to know what is failing. The best you can do to narrow the scope of the bug is to slightly change your HTML code until you found the culprit. 
Start by removing whole parts, like document body. If that's now working, re-add it but now remove one half of its content. And repeat again and again until you find which URLs is buggy.

There's one more (better) way though: fire up tcpdump or wireshark and listen for http requests. You should see which request(s) is failing, and you can even check the content of the request/response.
