# ColReorder for DataTables 

This package contains distribution files for the [ColReorder extension](https://datatables.net/extensions/colreorder) for [DataTables](https://datatables.net/). Only the core software for this library is contained in this package - to be correctly styled, a styling package for ColReorder must also be included. Styling options include DataTable's native styling, [Bootstrap](http://getbootstrap.com) and [Foundation](http://foundation.zurb.com/).

ColReorder allows the end user to modify the column order of a table through drop-and-drag of column headers.


## Installation

### Browser

For inclusion of this library using a standard `<script>` tag, rather than using this package, it is recommended that you use the [DataTables download builder](//datatables.net/download) which can create CDN or locally hosted packages for you, will all dependencies satisfied.

### npm

```
npm install datatables.net-colreorder
```

ES3 Syntax
```
var $ = require( 'jquery' );
require( 'datatables.net-colreorder' )( window, $ );
```

ES6 Syntax
```
import 'datatables.net-colreorder'
```

### bower

```
bower install --save datatables.net-colreorder
```



## Documentation

Full documentation and examples for ColReorder can be found [on the website](https://datatables.net/extensions/colreorder).

## Bug / Support

Support for DataTables is available through the [DataTables forums](//datatables.net/forums) and [commercial support options](//datatables.net/support) are available.


### Contributing

If you are thinking of contributing code to DataTables, first of all, thank you! All fixes, patches and enhancements to DataTables are very warmly welcomed. This repository is a distribution repo, so patches and issues sent to this repo will not be accepted. Instead, please direct pull requests to the [DataTables/ColReorder](http://github.com/DataTables/ColReorder). For issues / bugs, please direct your questions to the [DataTables forums](//datatables.net/forums).


## License

This software is released under the [MIT license](//datatables.net/license). You are free to use, modify and distribute this software, but all copyright information must remain.
