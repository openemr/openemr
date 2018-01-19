1. You can use "TTFdump.php" to dump the glyphs that exist inside the TTF file. A typical usage:

$ php TTFdump.php inp.ttf inp

This will create 'inp.pdf' and 'inp.png'. 'inp.pdf' will display all glyphs, for each glyph, the id and the unicode value (if applicable). 'inp.png' will display all glyphs mapped by Unicode characters.

2. You can use "TTFsubset.php" to subset a TTF file. Two typical usages:

The code below creates a subset that contains only glyphs with ids in (3,8,13)

$t = new TTFsubset();
$subset = $t->doSubset('inp.ttf', null, array(3,8,13));
file_put_contents('out.ttf', $subset);

The code below creates a subset that contains characters in (0xe1d1, 0xe1d2)

$t = new TTFsubset();
$subset = $t->doSubset('inp.ttf', "\xe1\xd1\xe1\xd2", null);
file_put_contents('out.ttf', $subset);
