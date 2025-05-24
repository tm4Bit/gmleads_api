<?php /** @var string $lang */ ?>
<?php /** @var string $tableName */ ?>
<?php /** @var mixed[] $columns */ ?>
<?php /** @var string $eventInfo */ ?>
<script>
	///////////////////////////////////////////////////////////////////////////
	///// AOS initialization
	///////////////////////////////////////////////////////////////////////////
	AOS.init({
		easing: "ease-out-back",
		duration: 850
	});
</script>
<script>
	///////////////////////////////////////////////////////////////////////////
	///// Scroll button
	///////////////////////////////////////////////////////////////////////////
	$(window).scroll(function() {
		if ($(this).scrollTop() >= 50) {
			$("#return-to-top").fadeIn(200);
		} else {
			$("#return-to-top").fadeOut(200);
		}
	});
	$("#return-to-top").click(function() {
		$("body,html").animate({
			scrollTop: 0
		}, 500);
	});
</script>
<script type="text/javascript" charset="UTF-8">
	///////////////////////////////////////////////////////////////////////////
	///// Cookie Consent
	///////////////////////////////////////////////////////////////////////////
	document.addEventListener("DOMContentLoaded", function() {
		cookieconsent.run({
			notice_banner_type: "simple",
			consent_type: "implied",
			palette: "dark",
			language: "<?= $lang ?>",
			page_load_consent_levels: [
				"strictly-necessary",
				"functionality",
				"tracking",
				"targeting",
			],
			notice_banner_reject_button_hide: false,
			preferences_center_close_button_hide: false,
			page_refresh_confirmation_buttons: false,
			website_name: "Pesquisa de Satisfação Chevrolet",
		});
	});
</script>
<noscript>ePrivacy and GPDR Cookie Consent by
	<a href="https://www.TermsFeed.com/" rel="nofollow">
		TermsFeed Generator
	</a>
</noscript>

<script>
	$(document).ready(function() {
		$(".cep").mask("00.000-000");
		$(".cel").mask("(00) 9 0000-0000");
		$(".cpf").mask("000.000.000-00");
		$(".data").mask("00/00/0000");
	});

	function validacpf() {
		if (valida_cpf(document.getElementById("cpf").value))
			return true;
		else alert("CPF inválido, digite-o corretamente");
		cpf.value = "";
	}

	function valida_cpf(cpf_raw) {
		var cpf = "";
		if (cpf_raw) {
			cpf = cpf_raw.replace(/[^0-9]/g, "");
		}
		var numeros, digitos, soma, i, resultado, digitos_iguais;
		digitos_iguais = 1;
		if (cpf.length < 11) return false;
		for (i = 0; i < cpf.length - 1; i++)
			if (cpf.charAt(i) != cpf.charAt(i + 1)) {
				digitos_iguais = 0;
				break;
			}
		if (!digitos_iguais) {
			numeros = cpf.substring(0, 9);
			digitos = cpf.substring(9);
			soma = 0;
			for (i = 10; i > 1; i--) soma += numeros.charAt(10 - i) * i;
			resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
			if (resultado != digitos.charAt(0)) return false;
			numeros = cpf.substring(0, 10);
			soma = 0;
			for (i = 11; i > 1; i--) soma += numeros.charAt(11 - i) * i;
			resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
			if (resultado != digitos.charAt(1)) return false;
			return true;
		} else return false;
	}
</script>

<script type="text/javascript">
	(function($, config) {
		function aplicarMascaraTelefone(numero) {
			numero = numero.replace(/\D/g, "");
			numero = numero.replace(/^(\d{2})(\d)/g, "($1) $2");
			numero = numero.replace(/(\d{5})(\d)/, "$1-$2");
			return numero;
		}

		function aplicarMascaraData(numero) {
			numero = numero.replace(/\D/g, "");
			numero = numero.replace(/(\d{4})(\d{2})(\d{2})/, "$3/$2/$1");
			return numero;
		}

		function aplicarMascaraCep(numero) {
			numero = numero.replace(/\D/g, "");
			numero = numero.replace(/(\d{2})(\d{3})(\d{3})/, "$1.$2-$3");
			return numero;
		}

		$(document).ready(function() {
			$(".email").autoEmail(
				[
					"gmail.com",
					"hotmail.com",
					"outlook.com",
					"yahoo.com.br",
					"globo.com",
				],
				false,
			);
			$("#form").submit(function(e) {
				e.preventDefault();
				$.ajax({
						type: "POST",
						url: config.formAction,
						data: $("#form").serialize(),
					})
					.done(function(response) {
						localStorage.setItem(
							"cadastro",
							JSON.stringify({
								nome: $("#nome").val()
							}),
						);
						window.location.href = "#obrigado";
					})
					.fail(function(xhr, textStatus, errorThrown) {
						if (window.grecaptcha) window.grecaptcha.reset();
						var response = JSON.parse(xhr.responseText);
						if (response.errors) {
							alert(response.errors.join("\n"));
						} else {
							alert(
								"Não foi possível realizar o cadastro. Tente mais tarde",
							);
						}
					});
			});
		});

		const inputs = document.querySelectorAll("input, select, textarea");
		for (let input of inputs) {
			input.addEventListener(
				"invalid",
				(event) => {
					input.classList.add("error");
				},
				false,
			);

			// Optional: Check validity onblur
			input.addEventListener("blur", (event) => {
				input.checkValidity();
			});
		}
	})(jQuery, {
		baseUrl: window.location.href.indexOf("http://localhost") == 0 ?
			"http://localhost" :
			"https://gm.ovlk.com.br",
		formAction: window.location.href.indexOf("http://localhost") == 0 ?
			"http://localhost/cadastro" :
			jQuery("#form").attr("action"),
	});
</script>
<script type="text/javascript">
	$("[data-rotulo]").click(function() {
		if (window.location.href.indexOf("localhost") > -1) {
			console.log("[user_id]", "");
			console.log("[form_id]", $(this).closest("form").attr("id") || "");
			console.log("[click_id]", $(this).attr("id") || "");
			console.log("[event]", "click-" + $(this).data("rotulo"));
		}
		window.dataLayer.push({
			user_id: "",
			form_id: $(this).closest("form").attr("id") || "",
			click_id: $(this).attr("id") || "",
			event: "click-" + $(this).data("rotulo"),
		});
	});
</script>
<script>
	function validarCEP() {
		var cepInput = document.getElementById("cep");
		var cep = cepInput.value;
		cep = cep.replace(/[.-]/g, "");
		var cepRegex = /^[0-9]{8}$/;

		if (cepRegex.test(cep)) {
			fetch(`https://viacep.com.br/ws/${cep}/json/`)
				.then((response) => response.json())
				.then((data) => {
					if (data.erro) {
						cepInput.value = "";
						alert("CEP inválido. Por favor, insira um CEP válido.");
						$("#campos-cadastro").show();
					} else {
						$('form [name="uf"]').val(data.uf);
						$('form [name="cidade"]').val(data.localidade);
					}
				})
				.catch((error) =>
					console.error("Erro na solicitação à API:", error),
				);
		} else {
			cepInput.value = "";
			alert("CEP inválido. Por favor, insira um CEP válido.");
		}
	}
</script>
<script>
	///////////////////////////////////////////////////////////////////////////
	///// IndexedDB Form Storage with Dexie.js
	///////////////////////////////////////////////////////////////////////////
	(function() {
		console.log("Dexie initialization starting");
		if (!window.Dexie) {
			console.log("Dexie not found, loading script");
			const script = document.createElement('script');
			script.src = '../../dexie.js';
			script.onload = function() {
				console.log("Dexie script loaded successfully");
				initDexie();
			};
			script.onerror = function(e) {
				console.error("Failed to load Dexie.js", e);
				alert("Failed to load database functionality. Form will submit normally but data may not be saved locally.");
			};
			document.head.appendChild(script);
		} else {
			console.log("Dexie already available");
			initDexie();
		}

		function initDexie() {
			try {
				console.log("Creating Dexie database");
				const db = new Dexie("GMLeadsDB");
				const tableName = "<?= htmlspecialchars($tableName) ?>";
				const schema = {};
				schema[tableName] = "++id,timestamp";  // Primary key and timestamp are always included
				
				console.log("Setting up schema", schema);
				db.version(3).stores(schema);
				console.log("Schema setup complete");
				
				function saveFormData(formData) {
					console.log("Saving form data to IndexedDB", formData);
					formData.timestamp = new Date().getTime();
					
					return db[tableName].put(formData)
						.then((id) => {
							console.log("Form data saved to IndexedDB with ID:", id);
							return id;
						})
						.catch(error => {
							console.error("Error saving form data:", error);
							alert("Não foi possível salvar os dados localmente: " + error.message);
							throw error;
						});
				}
				
				function loadFormData() {
					console.log("Loading form data from IndexedDB");
					return db[tableName]
						.orderBy("timestamp")
						.reverse()
						.limit(1)
						.toArray()
						.then(results => {
							console.log("Form data loaded", results);
							if (results.length === 0) {
								console.log("No saved form data found");
								return;
							}
							const formData = results[0];
							const form = document.getElementById("form");
							if (form) {
								console.log("Populating form with saved data");
								<?php foreach ($columns as $column): ?>
								<?php 
									$fieldName = $column['Field'];
									if (in_array(strtolower($fieldName), ['id', 'mom'])) continue;
								?>
								if (formData["<?= htmlspecialchars($fieldName) ?>"]) {
									const input = form.elements["<?= htmlspecialchars($fieldName) ?>"];
									if (input) {
										if (input.type === "checkbox" || input.type === "radio") {
											input.checked = formData["<?= htmlspecialchars($fieldName) ?>"] === "1";
										} else {
											input.value = formData["<?= htmlspecialchars($fieldName) ?>"];
										}
										console.log("Set field <?= htmlspecialchars($fieldName) ?> to", input.type === "checkbox" ? input.checked : input.value);
									}
								}
								<?php endforeach; ?>
								console.log("Form populated successfully");
							} else {
								console.error("Form element not found");
							}
						})
						.catch(error => {
							console.error("Error loading form data:", error);
						});
				}

				console.log("Setting up event listeners");
				// Test if the DOM is already loaded
				if (document.readyState === 'loading') {
					console.log("Document still loading, waiting for DOMContentLoaded");
					document.addEventListener("DOMContentLoaded", initializeForm);
				} else {
					console.log("Document already loaded, DOMContentLoaded already fired");
					// Call directly since the event has already fired
					initializeForm();
				}
				
				// Function to initialize form handlers
				function initializeForm() {
					console.log("Initializing form handlers");
					const form = document.getElementById("form");
					if (form) {
						console.log("Form found, id:", form.id);
						
						// Test database connection
						db.open().then(function() {
							console.log("Database connection successful");
						}).catch(function(err) {
							console.error("Database connection failed:", err);
							alert("Database connection failed: " + err.message);
						});
						
						loadFormData();
						
						const formInputs = form.querySelectorAll("input, select, textarea");
						console.log("Found", formInputs.length, "form elements");
						
						formInputs.forEach(input => {
							input.addEventListener("change", function() {
								console.log("Input changed:", input.name);
								const formData = {};
								<?php foreach ($columns as $column): ?>
								<?php
									$fieldName = $column['Field'];
									if (in_array(strtolower($fieldName), ['id', 'mom'])) continue;
								?>
								if (form.elements["<?= htmlspecialchars($fieldName) ?>"]) {
									const element = form.elements["<?= htmlspecialchars($fieldName) ?>"];
									formData["<?= htmlspecialchars($fieldName) ?>"] = element.type === "checkbox" ? 
										(element.checked ? "1" : "0") : 
										element.value;
								}
								<?php endforeach; ?>
								
								console.log("Change event triggered, saving form data");
								saveFormData(formData).then(() => {
									console.log("Successfully saved form data on change");
								});
							});
						});
						
						form.addEventListener("submit", function(e) {
							console.log("Form submit event triggered");
							e.preventDefault(); // Prevent standard form submission
							
							const formData = {};
							<?php foreach ($columns as $column): ?>
							<?php
								$fieldName = $column['Field'];
								if (in_array(strtolower($fieldName), ['id', 'mom'])) continue;
							?>
							if (form.elements["<?= htmlspecialchars($fieldName) ?>"]) {
								const element = form.elements["<?= htmlspecialchars($fieldName) ?>"];
								formData["<?= htmlspecialchars($fieldName) ?>"] = element.type === "checkbox" ? 
									(element.checked ? "1" : "0") : 
									element.value;
							}
							<?php endforeach; ?>

							// Save data to IndexedDB
							saveFormData(formData).then(() => {
								console.log("Form data saved, proceeding with AJAX submission");
								
								// Now handle server-side submission with AJAX
								$.ajax({
									type: "POST",
									url: form.action || window.location.href,
									data: $("#form").serialize(),
									success: function(response) {
										console.log("Form submitted successfully", response);
										// Show success message
										window.location.href = "#obrigado";
										
										// Clear form after successful submission
										form.reset();
									},
									error: function(xhr, textStatus, errorThrown) {
										console.error("Submission error:", textStatus, errorThrown);
										alert("Não foi possível enviar o formulário. Tente novamente mais tarde.");
									}
								});
							}).catch(err => {
								console.error("Failed to save to IndexedDB, attempting direct submission", err);
								// If IndexedDB fails, still try to submit the form
								$.ajax({
									type: "POST",
									url: form.action || window.location.href,
									data: $("#form").serialize(),
									success: function(response) {
										console.log("Form submitted successfully despite IndexedDB failure", response);
										window.location.href = "#obrigado";
										form.reset();
									},
									error: function(xhr, textStatus, errorThrown) {
										console.error("Submission error:", textStatus, errorThrown);
										alert("Não foi possível enviar o formulário. Tente novamente mais tarde.");
									}
								});
							});
						});
					} else {
						console.error("Form not found on the page");
					}
				}
			} catch (e) {
				console.error("Error in Dexie initialization:", e);
				alert("Database initialization error: " + e.message);
			}
		}
	})();
</script>
