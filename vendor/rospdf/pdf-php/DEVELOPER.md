# Developer Notes

To take part of the Development we recommend you to use the following tools

- Visual Studo Code (<a href="https://code.visualstudio.com/">https://code.visualstudio.com/</a>)
- PHP 5.6 (<a href="http://php.net/downloads.php">http://php.net/downloads.php</a>)
- PHP Debug extensions for Visual Studio Code (requires xdebug)

## Run from Visual Studio Code

This project comes with a tasks.json for VSC (Visual Studio Code).<br />
It uses the <a href="http://php.net/manual/en/features.commandline.webserver.php">built-in web server</a> from PHP >= 5.4

These are the steps on how to run the project
- press F1 and type "task" followed by "Run PHP Server"
- Open the URL <a href="http://localhost:9000">http://localhost:9000</a> in your favorite browser

## Debugging with Visual Studio Code

For debugging purposes Visual Studio Code uses the launch.json and the PHP Debug extensions.

- press F5 to debug the PHP file (optional: PHP Debug extension is required)
- add breakpoints to any php file
- Open the URL <a href="http://localhost:9000">http://localhost:9000</a> in your favorite browser

More details on how to debug PHP (using xdebug) please read <a hreF="https://marketplace.visualstudio.com/items?itemName=felixfbecker.php-debug">here</a>