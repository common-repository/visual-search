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
    <link rel="stylesheet" href="<?php echo $glide_core_css; ?>" />
    <link rel="stylesheet" href="<?php echo $glide_theme_css; ?>" />
    <link rel="stylesheet" href="<?php echo $indexation_css; ?>">
    <style>
        .impresee-step-state-image-pending + span {
            opacity: 0.4;
        }
    </style>
   <style>
        .glide__bullet {
        background-color: #ddd;
        opacity: 0.5;
        width: 12px;
        height: 12px;
        margin: 0 0.75em;
      }
      .glide__bullet--active {
        background-color: #9cd333;
        opacity: 1;
      }
      .glide__slide {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
      }
      .glide__slide img {
        height: 150px;
        line-height: 1.5em;
      }
      .glide__bullets {
        bottom: 0;
      }
      @media only screen and (min-width: 768px) {
        .glide__slide img {
          height: 200px;
        }
      }
    </style>
    <style>
      .impresee-card-content {
        max-width: 100%;
      }
      .impresee-body {
        padding-bottom: 0;
      }
      .impresee-message {
        font-size: 1.2em;
        line-height: 1.5em;
        font-weight: lighter;
        padding: 1em;
      }
    </style>
    <script type="text/javascript">
        var _wseeCompleteUpdate = <?php echo $complete_processing; ?>;
        var _wseeUpdateUrl = '<?php echo $processing_url; ?>';
        var successImage = '<?php echo $success_image; ?>';
        var warningImage = '<?php echo $warning_image; ?>';
    </script>
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
                </div>
                <div class="impresee-card-title">
                </div>
                <div class="impresee-card-content">
                    <div style="max-width:100%;background-color:#FFFFFF;">
                        <div class="glide" style="height:100%;">
                            <div class="glide__track" data-glide-el="track">
                                <ul class="glide__slides">
                                    <?php
                                         echo '<li class="glide__slide">';
                                                echo '<img src="'.$slider_image_base_path.'_boost.jpg" />';
                                                echo '<p class="impresee-message">Boost some products to have them appear at the top of your search results, and <span style="font-weight:bold;">increase your revenue!</span></p>';
                                        echo '</li>';
                                        foreach ( $messages as $index => $message ) {
                                            echo '<li class="glide__slide">';
                                                echo '<img src="'.$slider_image_base_path.($index+1).'.jpg" />';
                                                echo '<p class="impresee-message">'.$message.'</p>';
                                            echo '</li>';

                                        }
                                       
                                    ?>
                                </ul>
                            </div>
                            <div class="glide__bullets" data-glide-el="controls[nav]">
                                <?php
                                    foreach ( $messages as $index => $message ) {
                                        echo '<button class="glide__bullet" data-glide-dir="='.$index.'"></button>';
                                    }
                                    echo '<button class="glide__bullet" data-glide-dir="=6"></button>';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <footer class="impresee-card-footer">
            </footer>
        </div>
    </div>
    <div class="impresee-step-progress">
        <div class="impresee-bar-step-container">
            <span id="impresee-step-count"></span>
            <div class="impresee-progress-bar-container">
                <div id="impresee-progress-bar"></div>
            </div>
        </div>
        <span id="impresee-step-remaining-time"></span>
    </div>

    <div class="impresee-indexation-steps">
        <div class="impresee-step" style="display: none;">
            <span id="get_catalog" class="impresee-step-state-image-pending" data-ready=false data-loading="false"></span>
            <span>Downloading catalog</span>
        </div>
        <div class="impresee-step" style="display: none;">
            <span id="download_images" class="impresee-step-state-image-pending" data-ready=false data-loading="false"></span>
            <span>Downloading images</span>
        </div>
        <div class="impresee-step" style="display: none;">
            <span id="thumbnails" class="impresee-step-state-image-pending" data-ready=false data-loading="false"></span>
            <span>Computing thumbnails</span>
        </div>
        <div class="impresee-step" style="display: none;">
            <span id="photo_visual"  class="impresee-step-state-image-pending"  data-ready=false data-loading="false"></span>
            <span>Processing images for visual search</span>
        </div>
        <div class="impresee-step" style="display: none;" >
            <span id="sketch_visual" class="impresee-step-state-image-pending" data-ready=false data-loading="false"></span>
            <span>Processing images for search by drawing</span>
        </div>
        <div class="impresee-step" style="display: none;">
            <span id="refresh"  class="impresee-step-state-image-pending" data-ready=false data-loading="false"></span>
            <span>Refreshing our servers</span>
        </div>
    </div>
    <div class="impresee-card-footer" style="margin: 0;">
        <form id="choose-product-form" class="impresee-column" method="post" action="<?php echo esc_attr('admin-post.php'); ?>">
            <input type="hidden" name="action" value="<?php echo $step; ?>" />
            <button id="next-screen-button" class="impresee-loading-button" type="submit" >Please wait...</button>
        </form>
        <img id='impresee-loading-gif' src="<?php echo $gif_loading; ?>" style="width:50px; height: 50px;margin-bottom: 5px;">
    </div>
    <script type="text/javascript" src="<?php echo $update_js; ?>"></script>
    <script type="text/javascript" src="<?php echo $glide_js; ?>"></script>

    <script>
      new Glide(".glide", {
        type: "carousel",
        autoplay: 10000
      }).mount();
    </script>
</body>
</html>