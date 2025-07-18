<?php
/**
 * @var array $field The field data to be rendered.
 */
$name = htmlspecialchars($field['name']);
$type = htmlspecialchars($field['type']);
$required = $field['required'] ? 'required' : '';
$mask = htmlspecialchars($field['mask']);
$maxLength = $field['maxLength'] ? 'maxlength="' . htmlspecialchars($field['maxLength']) . '"' : '';
?>
<input
    class="u-half-width <?= $mask ?>"
    type="<?= $type ?>"
    id="<?= $name ?>"
    name="<?= $name ?>"
    <?= $required ?>
    <?= $maxLength ?>
/>