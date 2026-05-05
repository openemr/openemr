<label><?php echo xlt('Providers'); ?>:</label>
<div class="checkbox-group" id="provider-filter">
    <?php foreach ($authorizedProviders as $provider): ?>
        <label class="checkbox-label">
            <input type="checkbox" name="providers[]" value="<?php echo attr($provider['id']); ?>" <?php echo $checked; ?>>
            <?php echo text($provider['lname'] . ', ' . $provider['fname']); ?>
        </label>
    <?php endforeach; ?>
</div>
