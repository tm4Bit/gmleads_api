<?php
/**
 * // Variables passed into `render` funtion
 * @var array $eventInfo
 * @var array $translations
 * @var string $lang
 */
?>

<!doctype html>
<html class="no-js" lang="<?= $lang ?>">

<head>
    <meta charset="UTF-8">
    <title><?= $eventInfo['nome_evento'] ?> - GM lead show</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=0">
    <link rel="shortcut icon" href="../../favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="../../css/normalize.css" />
    <link rel="stylesheet" href="../../css/aos.css" />
    <link rel="stylesheet" href="../../css/tw9.css?v=1.24" />
    <script
        src='https://www.google.com/recaptcha/api.js?hl=<?= $lang === 'pt-br' ? 'pt-BR' : 'es' ?>'
        type="text/javascript"
    ></script>
</head>

<body>

    <div id="loader" style="display:none">
        <div class="lds-ripple">
            <div></div>
            <div></div>
        </div>
        <div align="center"><h4 class="br">Por favor,<br>aguarde o processamento</h4></div>
    </div>

    <div id="obrigado" class="modal-window"><div class="total" align="center"><a href="index.html" title="Close" class="modal-close">(X) FECHAR</a>
        <br><img src="../../img/logo-chevrolet.svg"><h2>Obrigado pelo cadastro ;)</h2><p>Aguarde os novos comunicados Chevrolet, especiais para você.</p></div>
    </div>

    <div class="container-fluid top" align="center"><img src="../../img/logo-chevrolet.svg" data-aos="flip-right"></div>
    <div class="container-fluid hero" style="background-image:url(../../img/s10.webp)">
        <div class="container">
            <div class="row">
                <h1 data-aos="fade-right"><?= $eventInfo['nome_evento'] ?></h1>
            </div>
        </div>
    </div>

    <div class="container" style="padding:30px 20px">
        <div class="row">
            <div class="twelve columns" align="center" id="tit">
                <form data-aos="fade-down">
                    <label for="pswd"><?= get_translation('ENTER_ACCESS_KEY', $translations) ?></label>
                    <input type="text" id="pswd" class="u-full-width" value="" style="background-color:#FFF;color:#000" required>
                    <input type="hidden" id="briefing_id_hidden" value="<?= $eventInfo['id'] ?>">
                    <input type="button" value="<?= get_translation('OK_BUTTON', $translations) ?>" onclick="checkPswd();">
                </form>
            </div>
        </div>
    </div>

    <div class="container-fluid pe">
        <div class="container">
            <div class="row">
               <div class="eight columns"><img class="logope" src="../../img/juntos-na-direcao.webp"></div>
               <div class="four columns links"><h5><a href="#atendimento" data-rotulo="menu-footer-atendimento"><?= get_translation('CUSTOMER_SERVICE', $translations) ?></a></h5><h5><a href="https://www.chevrolet.com.br/ajuda/politica-de-privacidade" target="_blank"><?= get_translation('PRIVACY_POLICY', $translations) ?></a></h5></div>
            </div>
            <div class="row" align="center">
                <hr style="margin:20px 0 40px 0;border-color:#fff"><p>Copyright © Chevrolet  &nbsp; &nbsp; | &nbsp; &nbsp; por <a href="//ovlk.com.br" target="_blank">Overlock</a></p>
            </div>
        </div>
    </div>
    <script src="../../jquery.min.js"></script><script src="../../aos.js"></script><script>AOS.init({ easing:"ease-out-back", duration:850 });</script>
    <a href="javascript:" id="return-to-top"><i class="arrow"></i></a><script>$(window).scroll(function() { if ($(this).scrollTop() >= 50) { $("#return-to-top").fadeIn(200); } else { $("#return-to-top").fadeOut(200); } });$("#return-to-top").click(function() { $("body,html").animate({ scrollTop : 0  }, 500); });</script>
    <script type="text/javascript" src="https://www.termsfeed.com/public/cookie-consent/4.0.0/cookie-consent.js" charset="UTF-8"></script><script type="text/javascript" charset="UTF-8">document.addEventListener("DOMContentLoaded", function () {cookieconsent.run({"notice_banner_type":"simple","consent_type":"implied","palette":"dark","language":"<?= $lang === 'pt-br' ? 'pt' : 'es' ?>","page_load_consent_levels":["strictly-necessary","functionality","tracking","targeting"],"notice_banner_reject_button_hide":false,"preferences_center_close_button_hide":false,"page_refresh_confirmation_buttons":false,"website_name":"Pesquisa de Satisfação Chevrolet"});});</script><noscript>ePrivacy and GPDR Cookie Consent by <a href="https://www.TermsFeed.com/" rel="nofollow">TermsFeed Generator</a></noscript>
    <script src="../../jquery.mask.min.js"></script>

    <script>
    function checkPswd() {
        const key = document.getElementById('pswd').value.trim();
        const id = document.getElementById('briefing_id_hidden').value;

        // if (!key) {
        //     alert('<?= get_translation('ENTER_KEY_MESSAGE', $translations) ?>');
        //     return;
        // }

        $('#loader').show();

        window.location.href = 'form.html';
        localStorage.setItem('briefing_id', '9');
        localStorage.setItem('briefing_key', 'user1');
        return;
        $.post('https://leadshowgm.ovlk.com.br/verifica-chave.php', { key: key, id: id }, function(response) {
            $('#loader').hide();

            try {
                const res = JSON.parse(response);
                if (res.success) {
                    localStorage.setItem('briefing_id', res.id);
                    localStorage.setItem('briefing_key', res.key);
                    window.location.href = 'form.html';
                } else {
                    alert(res.message || '<?= get_translation('INVALID_KEY', $translations) ?>');
                }
            } catch (e) {
                alert('<?= get_translation('SERVER_ERROR', $translations) ?>');
            }
        });
    }
    </script>

</body>
</html>
