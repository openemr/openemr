<?php
/**
 * HTML2PDF Library - example
 *
 * HTML => PDF convertor
 * distributed under the LGPL License
 *
 * @package   Html2pdf
 * @author    Laurent MINGUET <webmaster@html2pdf.fr>
 * @copyright 2016 Laurent MINGUET
 *
 * isset($_GET['vuehtml']) is not mandatory
 * it allow to display the result in the HTML format
 */

$content = '
<page>
    <draw style="margin: auto; width:180mm; height:240mm; background: #444444; border: solid 0.5mm #777777;">
        <g transform="translate(90mm,120mm) scale(1.8) translate(-250,-245)">
            <path d="M 157.219 412.757 C 158.128 465.467 355.334 465.467 354.425 413.666 C 353.516 358.23 159.036 357.322 157.219 412.757 z " id="path749" sodipodi:nodetypes="ccc" style="fill:#623e35;fill-rule:evenodd;stroke:black;stroke-opacity:1;stroke-width:3.75;stroke-linejoin:bevel;stroke-linecap:butt;fill-opacity:1;stroke-dasharray:none;"/>
            <path d="M 212.655 306.43 L 211.746 413.665 C 219.694 425.479 288.517 427.298 299.898 412.757 L 299.898 305.521 C 289.901 320.97 232.647 326.423 212.655 306.43 z " id="path748" sodipodi:nodetypes="ccccc" style="fill:#623e35;fill-rule:evenodd;stroke:black;stroke-opacity:1;stroke-width:4.76806;stroke-linejoin:bevel;stroke-linecap:butt;fill-opacity:1;stroke-dasharray:none;" transform="matrix(0.618557,0.000000,0.000000,1.000000,98.94439,-0.908813)"/>
            <path d="M 248.097 24.7078 C 244.462 35.6132 134.5 111.951 71.7941 150.119 C 109.053 250.994 394.412 247.359 425.31 150.121 C 371.692 134.671 250.823 34.7044 248.097 24.7078 z " id="path747" sodipodi:nodetypes="cccc" style="fill:#009500;fill-rule:evenodd;stroke:black;stroke-opacity:1;stroke-width:3.75;stroke-linejoin:bevel;stroke-linecap:butt;fill-opacity:1;stroke-dasharray:none;" transform="translate(0.908775,139.0434)"/>
            <path d="M 248.097 24.7078 C 244.462 35.6132 168.125 111.951 105.419 150.119 C 144.496 206.464 356.243 212.826 390.776 148.303 C 337.158 132.853 250.823 34.7044 248.097 24.7078 z " id="path746" sodipodi:nodetypes="cccc" style="fill:#009500;fill-rule:evenodd;stroke:black;stroke-opacity:1;stroke-width:3.75;stroke-linejoin:bevel;stroke-linecap:butt;fill-opacity:1;stroke-dasharray:none;" transform="translate(-3.725290e-7,71.79367)"/>
            <path d="M 157.343 412.757 L 158.596 431.841 C 201.308 481.824 340.324 469.226 353.047 431.966 L 354.205 412.757 C 356.023 466.375 160.07 463.649 157.343 412.757 z " id="path750" sodipodi:nodetypes="ccccc" style="fill:#623e35;fill-rule:evenodd;stroke:black;stroke-opacity:1;stroke-width:3.75;stroke-linejoin:bevel;stroke-linecap:butt;fill-opacity:1;stroke-dasharray:none;"/>
            <path d="M 248.097 24.7078 C 244.462 35.6132 207.202 107.407 144.497 145.575 C 181.756 190.106 322.618 193.742 357.151 146.485 C 303.533 131.035 250.823 34.7044 248.097 24.7078 z " id="path745" sodipodi:nodetypes="cccc" style="fill:#009500;fill-rule:evenodd;stroke:black;stroke-opacity:1;stroke-width:3.75;stroke-linejoin:bevel;stroke-linecap:butt;fill-opacity:1;stroke-dasharray:none;"/>
            <path d="M 247.188 38.3395 C 219.016 91.0487 170.85 136.488 151.766 145.575 C 159.946 160.116 206.293 174.657 225.378 171.931 C 213.564 141.032 216.289 111.951 247.188 38.3395 z " id="path751" sodipodi:nodetypes="cccc" style="fill:#ffffff;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.5;"/>
            <path d="M 179.938 171.93 C 168.124 181.927 125.412 215.552 114.506 222.822 C 106.327 223.731 157.219 251.903 175.395 251.903 C 135.408 228.275 217.199 177.383 179.938 171.93 z " id="path752" sodipodi:nodetypes="cccc" style="fill:#ffffff;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.498039;"/>
            <path d="M 140.861 250.994 C 126.321 260.082 102.692 277.349 78.1551 290.071 C 83.6078 310.065 123.594 335.511 132.682 338.237 C 96.331 292.798 175.395 260.082 140.861 250.994 z " id="path753" sodipodi:nodetypes="cccc" style="fill:#ffffff;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.498039;"/>
            <path d="M 233.308 366.285 C 234.121 379.008 231.643 403.487 233.623 413.293 C 247.255 420.056 256.276 418.554 266.962 418.114 C 253.33 412.661 242.329 402.388 233.308 366.285 z " id="path755" sodipodi:nodetypes="cccc" style="fill:#ffffff;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.3;"/>
            <path d="M 222.459 376.374 C 201.797 379.129 155.308 391.181 161.506 415.975 C 167.705 438.703 213.505 443.18 226.247 446.624 C 220.048 439.392 171.837 405.988 222.459 376.374 z " id="path756" sodipodi:nodetypes="cccc" style="fill:#ffffff;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.3;"/>
            <path d="M 161.162 428.028 C 163.573 429.061 172.871 441.803 208.685 456.955 C 193.532 452.822 163.228 440.425 161.162 428.028 z " id="path757" sodipodi:nodetypes="ccc" style="fill:#ffffff;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.3;" transform=""/>
            <path d="M 315.437 454.544 C 329.556 448.345 346.774 443.524 349.873 432.505 C 355.039 422.174 348.496 436.292 315.437 454.544 z " id="path758" sodipodi:nodetypes="ccc" style="fill:#000011;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.25;"/>
            <path d="M 287.888 377.407 C 326.457 377.407 383.965 411.154 327.834 438.359 C 362.615 398.413 287.888 378.44 287.888 377.407 z " id="path759" sodipodi:nodetypes="ccc" style="fill:#000011;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.247059;"/>
            <path d="M 259.65 366.043 C 259.65 366.043 279.967 363.288 281.345 365.698 C 282.722 368.109 281.689 387.049 281.001 391.87 C 279.968 382.572 282.378 368.798 259.65 366.043 z " id="path760" sodipodi:nodetypes="cccc" style="fill:#000011;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.247059;"/>
            <path d="M 387.929 328.061 C 401.191 322.851 418.716 301.536 421.559 292.536 C 418.243 288.747 385.56 275.484 377.982 268.853 C 383.666 278.326 409.717 297.747 387.929 328.061 z " id="path761" sodipodi:nodetypes="cccc" style="fill:#000011;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.247059;"/>
            <path d="M 352.404 245.644 C 370.876 238.539 382.244 229.065 384.613 222.908 C 375.613 219.119 355.246 205.856 345.299 200.172 C 352.878 208.224 368.509 221.487 352.404 245.644 z " id="path762" sodipodi:nodetypes="cccc" style="fill:#000011;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.247059;"/>
            <path d="M 305.038 115.861 C 312.617 126.755 346.72 147.595 350.51 148.543 C 345.773 156.122 322.09 168.437 313.09 169.384 C 308.354 168.911 336.773 152.806 305.038 115.861 z " id="path763" sodipodi:nodetypes="cccc" style="fill:#000011;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.247059;"/>
            <g id="g771" transform="translate(-249.1874,169.4743)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path772" sodipodi:nodetypes="ccc" style="fill:#ff0000;fill-rule:evenodd;stroke:black;stroke-opacity:1;stroke-width:3.75;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:1;stroke-dasharray:none;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path773" sodipodi:nodetypes="ccc" style="fill:#ffffff;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.7;"/>
            </g>
            <g id="g774" transform="translate(-46.89011,243.8286)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path775" sodipodi:nodetypes="ccc" style="fill:#ff0000;fill-rule:evenodd;stroke:black;stroke-opacity:1;stroke-width:3.75;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:1;stroke-dasharray:none;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path776" sodipodi:nodetypes="ccc" style="fill:#ffffff;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.7;"/>
            </g>
            <g id="g777" transform="translate(-226.4122,271.2928)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path778" sodipodi:nodetypes="ccc" style="fill:#ff0000;fill-rule:evenodd;stroke:black;stroke-opacity:1;stroke-width:3.75;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:1;stroke-dasharray:none;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path779" sodipodi:nodetypes="ccc" style="fill:#ffffff;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.7;"/>
            </g>
            <g id="g780" transform="translate(-117.8951,83.73234)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path781" sodipodi:nodetypes="ccc" style="fill:#ff0000;fill-rule:evenodd;stroke:black;stroke-opacity:1;stroke-width:3.75;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:1;stroke-dasharray:none;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path782" sodipodi:nodetypes="ccc" style="fill:#ffffff;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.7;"/>
            </g>
            <g id="g791" transform="translate(-236.4601,103.1582)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path792" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ff00ff;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path793" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g794" transform="translate(-85.74192,124.5937)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path795" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ff00ff;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path796" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g797" transform="translate(-144.0196,236.4601)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path798" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ff00ff;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path799" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g808" transform="translate(-158.7565,60.28728)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path809" sodipodi:nodetypes="ccc" style="font-size:12;fill:#0000ff;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path810" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g811" transform="translate(-300.7665,212.3452)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path812" sodipodi:nodetypes="ccc" style="font-size:12;fill:#0000ff;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path813" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g814" transform="translate(-109.1869,185.5508)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path815" sodipodi:nodetypes="ccc" style="font-size:12;fill:#0000ff;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path816" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g822" transform="translate(-192.9193,111.8663)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path823" sodipodi:nodetypes="ccc" style="font-size:12;fill:#00ffff;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path824" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g825" transform="translate(-101.1486,279.3311)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path826" sodipodi:nodetypes="ccc" style="font-size:12;fill:#00ffff;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path827" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g836" transform="translate(-293.3981,267.9435)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path837" sodipodi:nodetypes="ccc" style="font-size:12;fill:#00ff00;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path838" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g839" transform="translate(-203.6370,194.9289)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path840" sodipodi:nodetypes="ccc" style="font-size:12;fill:#00ff00;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path841" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g851" transform="translate(-81.05289,214.3548)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path852" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffff00;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path853" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g854" transform="translate(-146.0292,146.0292)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path855" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffff00;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path856" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g857" transform="translate(-231.7711,227.7519)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path858" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffff00;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path859" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g865" transform="translate(-202.9672,64.97627)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path866" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ff9300;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path867" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g868" transform="translate(-158.0866,287.3694)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path869" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ff9300;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path870" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g871" transform="translate(-152.7278,188.2303)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path872" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ff9300;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path873" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
            <g id="g880" transform="translate(-140.0000,25.00000)">
                <polygon id="polygon878" points="418.662,76.6493 399.532,56.5037 376.859,72.5587 390.107,48.1396 367.832,31.538 395.15,36.5918 404.055,10.2764 407.69,37.819 435.47,38.1568 410.399,50.1252 418.662,76.6493 " sodipodi:arg1="1.04002345" sodipodi:arg2="1.66834199" sodipodi:cx="400.575500" sodipodi:cy="45.8358383" sodipodi:r1="35.7292557" sodipodi:r2="10.7187767" sodipodi:sides="5" sodipodi:type="star" style="font-size:12;fill:#ff0000;fill-rule:evenodd;stroke-width:3.75;stroke:#000000;stroke-opacity:1;stroke-dasharray:none;fill-opacity:1;" transform="matrix(0.994342,-0.106230,0.106230,0.994342,-2.344353,42.91328)"/>
                <path d="M 388.532 62.8101 C 401.79 40.713 380.796 41.775 381.792 39.3513 C 399.139 44.4405 399.022 35.5462 400.79 24.014 C 403.441 40.3242 406.708 43.765 388.532 62.8101 z " id="path879" sodipodi:nodetypes="cccc" style="fill:#ffffff;fill-rule:evenodd;stroke:none;stroke-opacity:1;stroke-width:1pt;stroke-linejoin:miter;stroke-linecap:butt;fill-opacity:0.5;"/>
            </g>
            <g id="g883" transform="translate(-216.1370,24.92889)">
                <path d="M 424.69 52.5344 C 445.456 51.8645 445.456 82.0082 426.03 82.0082 C 401.245 82.678 404.594 53.2043 424.69 52.5344 z " id="path884" sodipodi:nodetypes="ccc" style="font-size:12;fill:#00ff00;fill-rule:evenodd;stroke:#000000;stroke-width:3.75;fill-opacity:1;"/>
                <path d="M 425.36 55.2139 C 423.35 53.2043 401.915 63.922 415.982 75.3096 C 424.691 74.6397 427.37 57.2234 425.36 55.2139 z " id="path885" sodipodi:nodetypes="ccc" style="font-size:12;fill:#ffffff;fill-opacity:0.7;fill-rule:evenodd;stroke-width:1pt;"/>
            </g>
        </g>
    </draw>
</page>';

    // onvert to PDF
    require_once(dirname(__FILE__).'/../vendor/autoload.php');
    try
    {
        $html2pdf = new HTML2PDF('P', 'A4', 'fr');
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
        $html2pdf->Output('svg_tree.pdf');
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }
