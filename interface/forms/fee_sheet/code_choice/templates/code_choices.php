<script type="text/html" id="code-choice-options">
    &nbsp;
    <div data-bind="foreach:categories">
        <div class="category-display">
            <button data-bind="text:title,click: set_active_category"></button>
        </div>
    </div>
    <!-- ko if: active_category -->
    <div class='active-category' data-bind='visible: show_choices'>
        <div data-bind="template: {name: 'category-options', data: active_category}">
        </div>
    </div>
    <!-- /ko -->
</script>

<script type="text/html" id="category-options">
    <div>
        <div data-bind="text:title"></div>
        <div data-bind="foreach:codes">
            <div class='code-choice'>
                <input type="checkbox" data-bind="checked: selected"/>
                <span data-bind="text:description,click: toggle_code"></span>
            </div>
        </div>
        <div style="clear: both;">
            <button data-bind="click:codes_ok"><?php echo xlt("OK")?></button>
            <button data-bind="click:codes_cancel"><?php echo xlt("Cancel")?></button>
        </div>
    </div>
</script>