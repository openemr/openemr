OpenEMR Bootstrap Project
=====
Goal is to standardize user interface elements. The project is using bootstrap as base. This document will hopefully
make things easier for developers by attempting to standardize code elements.

Themes
-----
There are currently 3 different theme categories in OpenEMR. The `light` theme is the default modern theme. The `manila`
theme is a combination of OpenEMR's legacy themes (which have all been removed) with some modern elements. And then all
the other themes are basically the same theme with different color palettes.

Buttons at bottom of form
-----
Sample code for buttons at the bottom of form:

```php
<div class="form-group clearfix">
    <div class="col-sm-12 col-sm-offset-1 position-override">
        <div class="btn-group oe-opt-btn-group-pinch" role="group">
            <button type='submit' onclick='top.restoreSession()' class="btn btn-default btn-save"><?php echo xlt('Save'); ?></button>
            <button type="button" class="btn btn-link btn-cancel oe-opt-btn-separate-left" onclick="top.restoreSession(); location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>';"><?php echo xlt('Cancel');?></button>
        </div>
    </div>
</div>
```
#### Classes
When adding buttons to the bottom of forms, will be important to incorporate following classes.

`position-override` gives a hook for style to change placement of buttons. In light/manila style this is ignored and buttons go to left positioned under data entry field. Whereas in the other styles this is used to center the buttons.

`oe-opt-btn-group-pinch` gives a hook for style to pinch the buttons (i think make them more rounded). Not used in light/manila, but used in other styles.

`oe-opt-btn-separate-left` gives a hook to place a space between the buttons. Not used in light/manila, but used in other styles.

#### Miscellaneous

(note there is also flexibility in how the Cancel links are shown. For example, in light, it's simple a link (not a button). And in Manila and other styles , some neat work was done to make it a button, but less accented than the Save buttons.)
