<?php
$skippedColumns = ['id', 'mom'];

if (! function_exists('is_column_required')) {
    function is_column_required(string $columnNullValue): bool
    {
        return $columnNullValue === 'NO';
    }
}

if (! function_exists('get_varchar_maxlength')) {
    function get_varchar_maxlength(string $fieldType): string
    {
        // Extrai o número de dentro de tipos como varchar(200)
        if (preg_match('/\(([\d]+)\)/', $fieldType, $matches)) {
            return $matches[1];
        }

        return '';
    }
}

if (! function_exists('is_evaluation_enum')) {
    function is_evaluation_enum(array $array): bool
    {
        $expectedIntNumbers = range(0, 10);
        if (count($array) !== count($expectedIntNumbers)) {
            return false;
        }
        $normalizedInputArray = array_map('intval', $array);
        sort($normalizedInputArray);

        return $normalizedInputArray === $expectedIntNumbers;
    }
}
?>

<form action="http://localhost:8080/api/leads" name="form" id="form" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="briefing" value="<?= $eventInfo['id'] ?>" />
	<input type="hidden" name="country" value="<?= $eventInfo['pais'] ?>" />
	<input type="hidden" name="k" value="<?= $eventInfo['k'] ?>" />
	<div id="campos-cadastro" class="row">
		<div class="twelve columns">
			<?php foreach ($columns as $column) { ?>
			<?php
            $fieldName = $column['Field'];
			    $fieldType = strtolower($column['Type']);
			    $isRequired = is_column_required($column['Null']);
			    if (in_array(strtolower($fieldName), $skippedColumns)) {
			        continue;
			    }
			    $requiredAttribute = $isRequired ? 'required' : '';
			    ?>
			<div class="campo">
				<label for="<?= htmlspecialchars($fieldName) ?>"><?= get_translation($fieldName, $translations) ?></label>
				<br />
				<?php if (strpos($fieldType, 'enum') === 0) { ?>
				<?php
			        preg_match_all("/'([^']+)'/", $column['Type'], $matches);
				    $enumValues = $matches[1];
				    $isEvaluation = is_evaluation_enum($enumValues);
				    if (! $isEvaluation) {
				        $enumPrefix = 'enum_'.$fieldName.'_';
				    }
				    ?>
				<?php foreach ($enumValues as $value) { ?>
				<?php
				    if (! $isEvaluation) {
				        $valueToTranslate = normalize_string($value);
				        $enumKey = $enumPrefix.$valueToTranslate;
				    }
				    ?>
				<br/>
				<?php if ($isEvaluation) { ?>
				<input
					type="radio"
					name="<?= $fieldName ?>"
					value="<?= $value ?>"
				/>
				<label><?= $value ?></label>
				<?php } else { ?>
				<input
					type="radio"
					name="<?= $fieldName ?>"
					value="<?= $value ?>"
				/>
				<label><?= get_translation($enumKey, $translations) ?></label>
				<?php } ?>
				<?php } ?>
				<?php } elseif (strpos($fieldType, 'set') === 0) { ?>
				<?php
				    preg_match_all("/'([^']+)'/", $column['Type'], $matches);
				    $setValues = $matches[1];
				    $setPrefix = 'set_'.$fieldName.'_';
				    ?>
				<?php foreach ($setValues as $value) { ?>
				<?php
				    $value = normalize_string($value);
				    $setKey = $setPrefix.$value;
				    ?>
				<br/>
				<input
					type="checkbox"
					name="<?= htmlspecialchars($fieldName) ?>"
					value="<?= get_translation($setKey, $translations) ?>"
				/>
				<label><?= get_translation($setKey, $translations) ?></label>
				<?php } ?>
				<?php } elseif (strpos($fieldType, 'varchar') !== false) { ?>
				<?php
				    $inputTextType = 'text';
				    $mask = '';
				    if (strtolower($fieldName) === 'email') {
				        $inputTextType = 'email';
				    } elseif (strtolower($fieldName) === 'cel') {
				        $inputTextType = 'tel';
				        $mask = 'cel';
				    } elseif (strtolower($fieldName) === 'cep') {
				        $mask = 'cep';
				    } elseif (strtolower($fieldName) === 'cpf') {
				        $mask = 'cpf';
				    }
				    $maxLength = get_varchar_maxlength($fieldType);
				    $maxLengthAttribute = $maxLength ? "maxlength=\"{$maxLength}\"" : '';
				    ?>
				<input
					class="u-half-width <?= $mask ?>"
					type="<?= $inputTextType ?>"
					id="<?= htmlspecialchars($fieldName) ?>"
					name="<?= htmlspecialchars($fieldName) ?>"
					placeholder="<?= htmlspecialchars($fieldName) ?>"
					<?= $requiredAttribute ?>
					<?= $maxLengthAttribute ?>
				/>
				<?php } elseif (strpos($fieldType, 'text') !== false) { ?>
				<textarea
					class="u-half-width"
					id="<?= htmlspecialchars($fieldName) ?>"
					name="<?= htmlspecialchars($fieldName) ?>"
					<?= $requiredAttribute ?>></textarea>
				<?php } elseif (strpos($fieldType, 'int') !== false) { ?>
				<input
					class="u-half-width"
					type="number"
					id="<?= htmlspecialchars($fieldName) ?>"
					name="<?= htmlspecialchars($fieldName) ?>"
					<?= $requiredAttribute ?>
				/>
				<?php } elseif (strpos($fieldType, 'date') !== false) { ?>
				<input
					class="u-half-width"
					type="date"
					id="<?= htmlspecialchars($fieldName) ?>"
					name="<?= htmlspecialchars($fieldName) ?>"
					<?= $requiredAttribute ?>
				/>
				<?php } else { ?>
					<input
						class="u-half-width"
						type="text"
						name="<?= htmlspecialchars($fieldName) ?>"
						id="<?= htmlspecialchars($fieldName) ?>"
						<?= $requiredAttribute ?>
				/>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
	</div>

	<div class="row" style="margin: 30px auto">
		<p><input type="checkbox" value="1" style="display:inline-block; margin-bottom:0" name="optin" id="optin" required/>&nbsp;Declaro que li e concordo com a Política de Privacidade da General Motors do Brasil, e que aceito receber e-mails com dicas, ofertas, promoções, conteúdos e materiais informativos da General Motors do Brasil e suas marcas.</p>
		<div align="center">
			<input type="submit" name="sub2" id="sub2" value="<?= get_translation('SUBMIT_BUTTON', $translations)?>" data-rotulo="cadastro-button" />
		</div>
	</div>
</form>
