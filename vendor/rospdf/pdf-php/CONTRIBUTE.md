# CONTRIBUTE

To take part of the Development we recommend you to use the following tools

- Visual Studo Code (<a href="https://code.visualstudio.com/">https://code.visualstudio.com/</a>)
- PHP 5.6 (<a href="http://php.net/downloads.php">http://php.net/downloads.php</a>)
- PHP Debug extensions for Visual Studio Code (requires xdebug)

## Run from Visual Studio Code

This project comes with a tasks.json for VSC (Visual Studio Code).<br />
It uses the <a href="http://php.net/manual/en/features.commandline.webserver.php">built-in web server</a> from PHP >= 5.4

These are the steps on how to run the project
- press F1 and type "task" followed by "Run PHP Server"
- Open the URL <a href="http://localhost:5000">http://localhost:5000</a> in your favorite browser

## Run from docker

Alternatively, you can use docker to populate the website incl. xdebug

- Install [docker](https://www.docker.com/products/docker-desktop)
- Execute `docker-compose up` to run the container
- Open the URL <a href="http://localhost:5000">http://localhost:5000 (through port mapping)</a>

## Debugging with Visual Studio Code

For debugging purposes Visual Studio Code uses the launch.json with xdebug.

- Install the [PHP Debug](https://marketplace.visualstudio.com/items?itemName=felixfbecker.php-debug) extension for vscode
- Open the "Debug" panel (Ctrl + Shift + D) and select "Listen for XDebug" or "Listen for XDebug (docker)" for docker configuration
- Pick a breakpoint on the file you which to debug
- Refresh the webpage

More details on how to debug PHP (using xdebug) please read <a hreF="https://marketplace.visualstudio.com/items?itemName=felixfbecker.php-debug">here</a>