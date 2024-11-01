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
    <link rel="stylesheet" href="<?php echo $welcome_css; ?>">
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M2KFHW9"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div class="impresee-body">
        <div class="impresee-card">
            <header class="impresee-card-header"></header>
            <section class="impresee-card-body">
                <div class="impresee-step-image">
                    <img src="<?php echo $welcome_image_url; ?>" alt="welcome to impresee" />
                </div>
                <div class="impresee-card-title">
                    <h1>Hi! We're happy to <span class="impresee-colored">See</span> you here!</h1>
                </div>
                <div class="impresee-card-content">
                    <span>Thank you for choosing Creative Search Bar.</span>
                    <br>
                    <span>Before you start please make sure your store has at least one published, and on-stock product.</span>
                    <br>
                    <span>Let's get it on with the installation process.</span>
                    <br>
                    <span style="font-weight: 900;">Remember that you have a 14-day trial to test the app.</span>
                </div>
            </section>
            <footer class="impresee-card-footer">
                <a class="impresee-next-button" id="to-choose-product" href="<?php echo $destination; ?>">Next</a>
            </footer>
        </div>
    </div>
</body>
</html>