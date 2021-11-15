# Scroller for DataTables with styling for [jQueryUI](http://jqueryui.com/)

This package contains a built distribution of the [Scroller extension](https://datatables.net/extensions/scroller) for [DataTables](https://datatables.net/) with styling for [jQueryUI](http://jqueryui.com/).

Scroller is a virtual rendering plug-in for DataTables which allows large datasets to be drawn on screen very quickly. Virtual rendering means is that only the visible portion of the table is drawn, while the scrolling container gives the visual impression that the whole table is visible, allowing excellent browser performance.


## Installation

### Browser

For inclusion of this library using a standard `<script>` tag, rather than using this package, it is recommended that you use the [DataTables download builder](//datatables.net/download) which can create CDN or locally hosted packages for you, will all dependencies satisfied.

### npm

```
npm install datatables.net-scroller-jqui
```

ES3 Syntax
```
var $ = require( 'jquery' );
var dt = require( 'datatables.net-scroller-jqui' )( window, $ );
```

ES6 Syntax
```
import 'datatables.net-scroller-jqui'
```

### bower

```
bower install --save datatables.net-scroller-jqui
```



## Documentation

Full documentation and examples for Scroller can be found [on the website](https://datatables.net/extensions/scroller).


## Bug / Support

Support for DataTables is available through the [DataTables forums](//datatables.net/forums) and [commercial support options](//datatables.net/support) are available.


### Contributing

If you are thinking of contributing code to DataTables, first of all, thank you! All fixes, patches and enhancements to DataTables are very warmly welcomed. This repository is a distribution repo, so patches and issues sent to this repo will not be accepted. Instead, please direct pull requests to the [DataTables/Scroller](http://github.com/DataTables/Scroller). For issues / bugs, please direct your questions to the [DataTables forums](//datatables.net/forums).


## License

This software is released under the [MIT license](//datatables.net/license). You are free to use, modify and distribute this software, but all copyright information must remain.

