<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $analytics = <<<EOT
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-M2KFHW9');</script>
<!-- End Google Tag Manager -->

<script>
    (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:1629997,hjsv:6};
        a=o.getElementsByTagName('head')[0];
        r=o.createElement('script');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
    })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>
EOT;
    if (!$debug) {
        echo $analytics;        
    }
    ?>
    <meta charset="UTF-8">
    <title>Impresee Visual Search</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $main_css; ?>">
    <link rel="stylesheet" href="<?php echo $select_market_css; ?>">
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M2KFHW9"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div class="impresee-select-product-type">
    <div class="impresee-title-container">
        <h1 style="margin-top:2em;">Choose the kind of products your store sells</h1>
        <span class="impresee-subtitle ">We'll use this information to set up our AI, this way we'll be able to give your customers the best search experience.</span>
    </div>
    <form id="choose-product-form" class="impresee-column" method="post" action="<?php echo esc_attr('admin-post.php'); ?>">
        <input type="hidden" name="action" value="<?php echo $step; ?>" />
        <div class="impresee-body">
            <div id="impresee-fashion-card-container" class="impresee-card">
                <header class="impresee-card-header"></header>
                <section class="impresee-card-body">
                    <div class="impresee-step-image">
                        <img src="<?php echo $fashion_image_url; ?>" alt="fashion & apparel">
                    </div>
                </section>
                <footer class="impresee-card-footer">
                    <input type="radio" id="cloth" name="type_catalog" value="<?php echo $apparel_code; ?>"/>
                    <label for="cloth">Fashion & Apparel</label>
                </footer>
                <p><span style="font-weight:900;">For example:</span> Apparel, Sports clothing, shoes</p>
            </div>
            <div id="impresee-homedecor-card-container" class="impresee-card">
                <header class="impresee-card-header"></header>
                <section class="impresee-card-body">
                    <div class="impresee-step-image">
                        <img src="<?php echo $homedecor_image_url; ?>" alt="homedecor">
                    </div>
                </section>
                <footer class="impresee-card-footer">
                    <input type="radio" id="homedecor" name="type_catalog" value="<?php echo $home_decor_code; ?>"/>
                    <label for="homedecor">Home & Decor</label>
                </footer>
                <p><span style="font-weight:900;">For example:</span> Home & decor, lamps, antiques </p>
            </div>
            <div id="impresee-other-card-container"  class="impresee-card">
                <header class="impresee-card-header"></header>
                <section class="impresee-card-body">
                    <div class="impresee-step-image">
                        <img src="<?php echo $other_image_url; ?>" alt="other">
                    </div>
                </section>
                <footer class="impresee-card-footer">
                    <input type="radio" id="other" name="type_catalog" value="<?php echo $other_code; ?>"/>
                    <label for="other">Other</label>
                </footer>
                <p><span style="font-weight:900;">For example:</span> Dropshippers, multi-department, technology</p>
            </div>
        </div>
        <button type="submit" id="impresee-submit-button" class="impresee-disabled-button" disabled>Next</button>
    </form>
</div>

    <script type="text/javascript">
        function markSelectedCard(cardId) {
            var selectedCard = document.getElementById(cardId);
            if (selectedCard.classList.contains('selected-impresee-card')) {
                return;
            }
            /* getElementsByClassName maintins a "live" selection of DOM elements (they reflect changes in DOM automatically). */
            var markedCards = document.getElementsByClassName('selected-impresee-card');
            while (markedCards.length)
                markedCards[0].classList.remove('selected-impresee-card');
            selectedCard.classList.add('selected-impresee-card');
            var disabledButtons = document.getElementsByClassName('impresee-disabled-button');
            while (disabledButtons.length){
                disabledButtons[0].disabled = false;
                disabledButtons[0].classList.add('impresee-enabled-button');
                disabledButtons[0].classList.remove('impresee-disabled-button');
            }

        }

        var clothRadio = document.getElementById('cloth');
        var homedecorRadio = document.getElementById('homedecor');
        var otherRadio = document.getElementById('other');
        document.getElementById('impresee-fashion-card-container').onclick = function() {
            markSelectedCard('impresee-fashion-card-container');
            clothRadio.checked = true;
            homedecorRadio.checked = false;
            otherRadio.checked = false;
        }
        document.getElementById('impresee-homedecor-card-container').onclick = function() {
            markSelectedCard('impresee-homedecor-card-container');
            clothRadio.checked = false;
            homedecorRadio.checked = true;
            otherRadio.checked = false;
        }
        document.getElementById('impresee-other-card-container').onclick = function() {
            markSelectedCard('impresee-other-card-container');
            clothRadio.checked = false;
            homedecorRadio.checked = false;
            otherRadio.checked = true;
        }
        clothRadio.onclick = function(){markSelectedCard('impresee-fashion-card-container')};
        homedecorRadio.onclick = function(){markSelectedCard('impresee-homedecor-card-container')};
        otherRadio.onclick = function(){markSelectedCard('impresee-other-card-container')};
    </script>
</body>
</html>