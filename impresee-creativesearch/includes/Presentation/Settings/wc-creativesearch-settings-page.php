<?php defined( 'ABSPATH' ) or exit; ?>
<div class="wrap">
    <div class="icon32" id="icon-options-general"><br /></div>
    <h2>Impresee's Smart Search Bar</h2>
    <style>
        .impresee-plan-bar{
            height: 60px;
            background-color: #EA9078;
            color: #471F23;
            margin: 10px -20px;
            display: flex;
            font-size: 1.1em;
        }
        .impresee-plan-bar .impresee-plan-text{
            flex: 3;
            flex-direction: column;
            align-content: start;
            align-items: baseline;
            display: flex;
            justify-content: start;
            padding: 10px 20px;
        }
        .impresee-plan-bar .impresee-plan-button-container{
            flex: 1;
            display: flex;
            align-items: center;
        }
        .impresee-plan-bar .impresee-plan-button-container .impresee-trial-days {
            flex: 1;
            font-size: 1.2em;
        }
        .impresee-plan-bar .impresee-plan-button-container .impresee-button {
            flex: 1;
            padding: 0 15px;
            height: 47px;
            line-height: 38px;
            text-align: center;
        }
        .impresee-plan-bar .impresee-plan-button-container .impresee-button .impresee-link {
            height: calc(100% - 8px);
            display: inline-block;
            background-color: #E5D2CF;
            width: 100%;
            border-color: #AF5258;
            border-width: 4px;
            border-style: solid;
            color: #471F23;
            font-weight: 900;
            text-decoration: none;
            min-width: 150px;
        }
    </style>
    <div class="impresee-plan-bar">
        <div class="impresee-plan-text">
            <span><?php echo $bar_title; ?></span>
            <span style="font-size: 0.9em; font-style: italic; margin: 5px 0;">(*) Remember to cancel your plan before uninstalling the plugin!</span>
        </div>
        <div class="impresee-plan-button-container">
            <span class="impresee-trial-days"><?php echo $trial_days_left_title; ?></span>
            <div style="width: 10px;"></div>

            <div class="impresee-button">
                <a class="impresee-link" rel="noopener noreferrer" target="_blank" href="<?php echo $button_url; ?>">
                    <?php echo $button_text; ?>
                </a>
            </div>
        </div>
    </div>
    <h2 class="nav-tab-wrapper">
    <?php
    foreach ($settings_tabs as $tab_slug => $tab_title ) {
        $tab_link = esc_url("?page={$page_id}&tab={$tab_slug}");
        printf('<a href="%1$s" class="nav-tab nav-tab-%2$s %3$s">%4$s</a>', $tab_link, $tab_slug, (($active_tab == $tab_slug) ? 'nav-tab-active' : ''), $tab_title);
    }
    ?>
    </h2>
    <?php 
        if (!empty($error_message)){
            printf('<div class="error notice is-dismissible"><p>%1$s</p></div>', $error_message);
        } 
    ?>
    <?php 
        printf('<form method="post" action="%1$s" id="see-wccs-settings" class="%2$s">', esc_attr('admin-post.php'), $active_tab);
        do_action( 'see_wccs_settings_output_'.$active_tab );
    ?>
</form>
</div>
<script type="text/javascript">
	jQuery( function( $ ) {
		$("#footer-thankyou").html( 'If you have any questions or extra requirements don\'t doubt to contact us at <a href="mailto:support@impresee.com">support@impresee.com</>');
	} );	
</script>