Contributions are more than welcome! Please review issues if you want to pick up from existing tickets. New tickets/features/CRs are welcome!

Contributors are requested to add or update phpdoc for any new or existing methods being worked on.

It'll be great if you update the API documentation as well (else I'll do it for you), in case if a phpdoc is changed.

##### Generate/update API documentation (in Markdown format) from docblocks
* Install [clean/phpdoc-md](https://github.com/clean/phpdoc-md): `composer require --dev clean/phpdoc-md`
* Update _.phpdoc-md_ if a new class is added
* Issue `vendor/bin/phpdoc-md`. This generates .md files under doc/ folder
