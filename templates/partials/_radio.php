<?php
/**
 * @var array $field Array fields to be rendered.
 * @var array $translations Translations
 */
$name = htmlspecialchars($field['name']);
$required = $field['required'] ? 'required' : '';
$enumPrefix = 'enum_' . $name . '_';
?>
<?php foreach ($field['options'] as $value): ?>
    <?php
        $optionValue = htmlspecialchars($value);
        $valueToTranslate = normalize_string($value);
        $enumKey = $enumPrefix . $valueToTranslate;
        $optionLabel = htmlspecialchars(get_translation($enumKey, $translations));
    ?>
    <label class="radio-label">
        <input type="radio" name="<?= $name ?>" value="<?= $optionValue ?>" <?= $required ?>>
        <?= $optionLabel ?>
    </label>
<?php endforeach; ?>