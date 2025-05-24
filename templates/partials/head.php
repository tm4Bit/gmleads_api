<?php /** @var string $eventName */ ?>
<?php /** @var string $lang */ ?>
<head>
    <script
      async
      src="https://www.googletagmanager.com/gtag/js?id=G-ZSW3JEYM3T"
    ></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag() {
        dataLayer.push(arguments);
      }
      gtag("js", new Date());
      gtag("config", "G-ZSW3JEYM3T");
    </script>
	<meta charset="UTF-8" />
	<title><?= $eventName ?> - GM lead show</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta
		name="viewport"
		content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=0" 
	/>
	<!-- CSS -->
	<link rel="shortcut icon" href="../../img/logo-chevrolet.svg" type="image/x-icon" />
	<link rel="stylesheet" href="../../css/normalize.css" />
	<link rel="stylesheet" href="../../css/aos.css" />
	<link rel="stylesheet" href="../../css/tw9.css?v=1.24" />
	<!-- JS libs -->
	<script
		src="https://www.google.com/recaptcha/api.js?hl=<?= $lang === 'pt-br' ? 'pt-BR' : 'es' ?>"
		type="text/javascript">
	</script>
	<script
		type="text/javascript"
		src="https://www.termsfeed.com/public/cookie-consent/4.0.0/cookie-consent.js?lang=<?= $lang === 'pt-br' ? 'pt-BR' : 'es' ?>"
		charset="UTF-8">
	</script>
	<script src="../../jquery.min.js"></script>
	<script src="../../jquery.mask.min.js"></script>
	<script src="../../aos.js"></script>
	<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
	<script>
	if (!localStorage.getItem('briefing_id') || !localStorage.getItem('briefing_key')) {
		alert('Acesso não autorizado. Redirecionando...');
		window.location.href = 'index.html';
	}
	</script>
</head>
