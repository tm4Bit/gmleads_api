<?php
/**
 * @var array $field Array fields to be rendered.
 */
$name = htmlspecialchars($field['name']);
$required = $field['required'] ? 'required' : '';
?>
<div class="evaluation-scale">
    <?php foreach ($field['options'] as $value): ?>
        <label class="evaluation-label">
            <input type="radio" name="<?= $name ?>" value="<?= htmlspecialchars($value) ?>" <?= $required ?>>
            <span><?= htmlspecialchars($value) ?></span>
        </label>
    <?php endforeach; ?>
</div>