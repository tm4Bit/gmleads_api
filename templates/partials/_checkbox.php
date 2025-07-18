<?php
/**
 * @var array $field Array fields to be rendered.
 * @var array $translations Translations
 */
$name = htmlspecialchars($field['name']);
$setPrefix = 'set_' . $name . '_';
?>
<?php foreach ($field['options'] as $value): ?>
    <?php
        $optionValue = htmlspecialchars($value);
        $valueToTranslate = normalize_string($value);
        $setKey = $setPrefix . $valueToTranslate;
        $optionLabel = htmlspecialchars(get_translation($setKey, $translations));
    ?>
    <label class="checkbox-label">
        <input type="checkbox" name="<?= $name ?>[]" value="<?= $optionValue ?>">
        <?= $optionLabel ?>
    </label>
<?php endforeach; ?>