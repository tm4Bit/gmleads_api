<?php
/**
 *
 * This template first processes the columns of the database into a form schema
 * and then renders the HTML using partials.
 *
 * @var array $columns      Raw columns from the database.
 * @var array $eventInfo    Event information.
 * @var array $translations Text translations.
 */

$formFields = [];
$skippedColumns = ['id', 'mom'];

foreach ($columns as $column) {
    $fieldName = $column['Field'];

    // Skip columns that should be skipped
    if (in_array(strtolower($fieldName), $skippedColumns)) {
        continue;
    }

    $fieldType = strtolower($column['Type']);
    $field = [
        'name' => $fieldName,
        'label' => get_translation($fieldName, $translations),
        'required' => $column['Null'] === 'NO',
        'options' => [],
        'mask' => '',
        'maxLength' => '',
    ];

    // Logic to determine the field type and its options
    if (str_starts_with($fieldType, 'enum')) {
        $field['type'] = 'radio';
        preg_match_all("/'([^']+)'/", $column['Type'], $matches);
        $field['options'] = $matches[1];

        // Logic to determine if the field is an evaluation scale
        $isEvaluation = count($field['options']) === 11 && array_reduce($field['options'], fn($c, $i) => is_numeric($i) && $c, true);
        if ($isEvaluation) {
            $field['type'] = 'evaluation';
        }

    } elseif (str_starts_with($fieldType, 'set')) {
        $field['type'] = 'checkbox';
        preg_match_all("/'([^']+)'/", $column['Type'], $matches);
        $field['options'] = $matches[1];

    } elseif (str_starts_with($fieldType, 'varchar')) {
        $field['type'] = 'text';
        if (preg_match('/\((\d+)\)/', $fieldType, $matches)) {
            $field['maxLength'] = $matches[1];
        }
        // Adjust based on field type
        if ($fieldName === 'email') $field['type'] = 'email';
        if ($fieldName === 'cel') {
            $field['type'] = 'tel';
            $field['mask'] = 'cel';
        }
        if ($fieldName === 'cep') $field['mask'] = 'cep';
        if ($fieldName === 'doc') $field['mask'] = 'cpf';

    } elseif (str_starts_with($fieldType, 'text')) {
        $field['type'] = 'textarea';
    } elseif (str_starts_with($fieldType, 'int')) {
        $field['type'] = 'number';
    } elseif (str_starts_with($fieldType, 'date')) {
        $field['type'] = 'date';
    } else {
        $field['type'] = 'text'; // Fallback
    }

    $formFields[] = $field;
}

// ==================================================================
// Template to render the form
// ==================================================================
?>

<form action="http://localhost:8080/api/leads" name="form" id="form" method="POST">
	<input type="hidden" name="briefing" value="<?= htmlspecialchars($eventInfo['id']) ?>" />
	<?php if (isset($eventInfo['k'])) : ?>
		<input type="hidden" name="k" value<?= htmlspecialchars($eventInfo['k']) ?>" />
	<?php endif; ?>
	<input type="hidden" name="country" value="<?= $eventInfo['pais'] ?>" />

	<div id="campos-cadastro" class="row">
		<div class="twelve columns">
			<?php foreach ($formFields as $field): ?>
				<div class="campo">
					<label for="<?= htmlspecialchars($field['name']) ?>"><?= htmlspecialchars($field['label']) ?></label>
					<br />
					<?php
                        // Include partial based on field type
                        $partialPath = __DIR__ . '/partials/_' . $field['type'] . '.php';
                        if (file_exists($partialPath)) {
                            include $partialPath;
                        } else {
                            // Fallback to a default partial
                            include __DIR__ . '/partials/_text.php';
                        }
                    ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="row" style="margin: 30px auto">
		<p>
            <input type="checkbox" value="1" style="display:inline-block; margin-bottom:0" name="optin" id="optin" required/>
            &nbsp;<?= get_translation('TERMS_AND_PRIVACY_POLICY_0', $translations) ?>
        </p>
		<div align="center">
			<input type="submit" name="sub2" id="sub2" value="<?= get_translation('SUBMIT_BUTTON', $translations)?>" data-rotulo="cadastro-button" />
		</div>
	</div>
</form>