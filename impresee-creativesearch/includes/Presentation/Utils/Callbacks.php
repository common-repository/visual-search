<?php
namespace SEE\WC\CreativeSearch\Presentation\Utils;

if (! defined('ABSPATH')){
    exit;
}

class Callbacks {

    /**
     * Section null callback.
     *
     * @return void.
     */
    public function section() {
    }

    /**
     * Textarea callback.
     *
     * args:
     *   option_name - name of the main option
     *   id          - key of the setting
     *   width       - width of the text input (em)
     *   height      - height of the text input (lines)
     *   default     - default setting (optional)
     *   description - description (optional)
     *
     * @return void.
     */
    public function textarea( $args ) {
        extract( $this->normalize_settings_args( $args ) );
    
        printf( '<textarea id="%1$s" name="%2$s" cols="%4$s" rows="%5$s" placeholder="%6$s"/>%3$s</textarea>', $id, $setting_name, esc_textarea( $current ), $width, $height, $placeholder );
    
        // output description.
        if ( isset( $description ) ) {
            printf( '<p class="description">%s</p>', $description );
        }
    }

    /**
     * Select element callback.
     *
     * @param  array $args Field arguments.
     *
     * @return string     Select field.
     */
    public function select( $args ) {
        extract( $this->normalize_settings_args( $args ) );
    
        printf( '<select id="%1$s" name="%2$s">', $id, $setting_name);
        foreach ( $options as $key => $label ) {
            printf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
        }

        echo '</select>';

        if (isset($custom)) {
            printf( '<div class="%1$s_custom custom">', $id );

            if (is_callable( array( $this, $custom['type'] ) ) ) {
                $this->{$custom['type']}( $custom['args'] );
            }
            echo '</div>';
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                function check_<?php echo $id; ?>_custom() {
                    var custom = $('#<?php echo $id; ?>').val();
                    if (custom == 'custom') {
                        $( '.<?php echo $id; ?>_custom').show();
                    } else {
                        $( '.<?php echo $id; ?>_custom').hide();
                    }
                }

                check_<?php echo $id; ?>_custom();

                $( '#<?php echo $id; ?>' ).change(function() {
                    check_<?php echo $id; ?>_custom();
                });

            });
            </script>
            <?php
        }

        if (isset($on_select) && isset($hide_args) ) {
            $this->hide_elements($hide_args, $id, $on_select);

        }
    
        // Displays option description.
        if ( isset( $args['description'] ) ) {
            printf( '<p class="description">%s</p>', $args['description'] );
        }

    }

    public function hide_elements($hide_args, $id_trigger, $value_trigger) {

         ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                function check_<?php echo $id_trigger; ?>_hide_elems() {
                    var custom = $('#<?php echo $id_trigger; ?>').val();
                    if (custom == '<?php echo $value_trigger ?>') {
        <?php
        foreach ( $hide_args as $elem  ) {
        ?>
                    
                        $( '#<?php echo $elem; ?>').attr('disabled', true);
                    
        <?php            
        }
        ?>
                    } else {
        <?php
            foreach ( $hide_args as $elem  ) {
        ?>
                        $( '#<?php echo $elem; ?>').attr('disabled', false);
        <?php
        }
        ?> 
                }
            }
                check_<?php echo $id_trigger; ?>_hide_elems();

                $( '#<?php echo $id_trigger; ?>' ).change(function() {
                    check_<?php echo $id_trigger; ?>_hide_elems();
                });
            });
            </script>
        <?php
    }


    /**
     * Text input callback.
     *
     * args:
     *   option_name - name of the main option
     *   text_input_default - default 
     *   id          - key of the setting
     *   size        - size of the text input (em)
     *   default     - default setting (optional)
     *   description - description (optional)
     *   type        - type (optional)
     *
     * @return void.
     */
    public function text_input( $args ) {
        extract( $this->normalize_settings_args( $args ) );

        if (empty($type)) {
            $type = 'text';
        }

        printf( '<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" size="%5$s" placeholder="%6$s" %7$s/>', $type, $id, $setting_name, esc_attr( $current ), isset($size) ? $size : "20", $placeholder, !empty($disabled) ? 'disabled="disabled"' : '' );
    
        // output description.
        if ( isset( $description ) ) {
            printf( '<p class="description">%s</p>', $description );
        }
    }


    /**
    * radio button element callback.
    *
    * @param  array $args Field arguments.
    *
    * @return string     radio button field.
    */
    public function radio_button( $args ) {
        extract( $this->normalize_settings_args( $args ) );
    
        foreach ( $options as $key => $label ) {
            printf( '<input type="radio" class="radio" id="%1$s[%3$s]" name="%2$s" value="%3$s"%4$s />', $id, $setting_name, $key, checked( $current, $key, false ) );
            printf( '<label for="%1$s[%3$s]"> %4$s</label><br>', $id, $setting_name, $key, $label);
        }
        
    
        // Displays option description.
        if ( isset( $args['description'] ) ) {
            printf( '<p class="description">%s</p>', $args['description'] );
        }

    }

    /**
    * Checkbox callback.
    *
    * args:
    *   option_name - name of the main option
    *   id          - key of the setting
    *   value       - value if not 1 (optional)
    *   default     - default setting (optional)
    *   description - description (optional)
    *
    * @return void.
    */
    public function checkbox( $args ) {
        extract( $this->normalize_settings_args( $args ) );

        // output checkbox  
        printf( '<input type="checkbox" id="%1$s" name="%2$s" value="%3$s" %4$s %5$s/>', $id, $setting_name, $value, checked( $value, $current, false ), !empty($disabled) ? 'disabled="disabled"' : '' );
    
        // output description.
        if ( isset( $description ) ) {
            printf( '<p class="description">%s</p>', $description );
        }
    }

    /**
    * processing button element callback
    *
    * @param  array $args Field arguments.
    *
    * @return string  button field.
    */
    public function processing_button( $args ) {
        extract( $this->normalize_settings_args( $args ) );
        $status = "Processing";
        $url_get_catalog_status = get_rest_url( null, SEE_WCCS()->uri_catalog_status ) . $owner;
        printf( '<p><span id="%1$s_span" class="impresee" style="padding-right:10px">%4$s</span><button id="%1$s" type="button">Check again</button><input type="hidden" value="%3$s" /></p>', $id, $setting_name, $current, $status );
        ?>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                function check_catalog_status_<?php echo $id; ?>() {
                    $.get( '<?php echo $url_get_catalog_status; ?>' , function( data ) {
                        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
                        var span_status = $('#<?php echo $id . "_span"; ?>');
                        if ( data.has_error ) {
                            span_status.text('Error');
                            span_status.removeClass( "ready processing" ).addClass( "error" );
                        } else {
                            if ( data.processing ) {
                                span_status.text('Processing');
                                span_status.removeClass( "ready error" ).addClass( "processing" );
                            } else {
                                span_status.text('Ready');
                                span_status.removeClass( "error processing" ).addClass( "ready" );
                            }
                        }
                    } );
                }
                check_catalog_status_<?php echo $id; ?>();
                $('#<?php echo $id; ?>').click(check_catalog_status_<?php echo $id; ?>);
            } );
            </script>
        <?php

    }


    /**
    * update catalog button element callback
    *
    * @param  array $args Field arguments.
    *
    * @return string  button field.
    */
    public function update_button( $args ) {
        extract( $this->normalize_settings_args( $args ) );
        $url_update_catalog = get_rest_url( null, SEE_WCCS()->uri_update_catalog ) . $owner . '/' . $catalog;
        $update_catalog_button = '#'.$catalog_status_id;
        printf( '<p><button id="%1$s" type="button">Update catalog</button><input type="hidden" value="update" />', $id, $setting_name );
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                function update_catalog_<?php echo $id; ?>() {
                    $('#<?php echo $id; ?>').prop("disabled",true);
                    $.post( '<?php echo $url_update_catalog; ?>' , function( data ) {
                        $('<?php echo $update_catalog_button; ?>').click();
                        $('#<?php echo $id; ?>').prop("disabled", false);
                    } );
                }
                $('#<?php echo $id; ?>').click(update_catalog_<?php echo $id; ?>);
            } );
            </script>
        <?php
        // output description.
        if ( isset( $description ) ) {
            printf( '<p class="description">%s</p>', $description );
        }

    }

     /**
    * update dashboard button element callback
    *
    * @param  array $args Field arguments.
    *
    * @return string  button field.
    */
    public function dashboard_button( $args ) {
        extract( $this->normalize_settings_args( $args ) );
        printf( '<p><button id="%1$s" type="button">Go to dashboard</button>', $id, $setting_name );
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                function go_to_dashboard<?php echo $id; ?>() {
                    var goToDashboard = document.createElement('a');
                    goToDashboard.href = '<?php echo $dashboard_url; ?>';
                    goToDashboard.target = '_blank';
                    goToDashboard.rel = 'noopener noreferrer';
                    goToDashboard.click();
                }
                $('#<?php echo $id; ?>').click(go_to_dashboard<?php echo $id; ?>);
            } );
            </script>
        <?php
        // output description.
        if ( isset( $description ) ) {
            printf( '<p class="description">%s</p>', $description );
        }

    }


    /**
    * Validate options.
    *
    * @param  array $input options to valid.
    *
    * @return array        validated options.
    */
    public function validate( $input ) {
        // echo '<pre>';var_dump($input);die('</pre>');
        // Create our array for storing the validated options.
        $output = array();

        if (empty($input) || !is_array($input)) {
            return $input;
        }
    
        // Loop through each of the incoming options.
        foreach ( $input as $key => $value ) {
    
            // Check to see if the current option has a value. If so, process it.
            if ( isset( $input[$key] ) ) {
                if ( is_array( $input[$key] ) ) {
                    foreach ( $input[$key] as $sub_key => $sub_value ) {
                        $output[$key][$sub_key] = $input[$key][$sub_key];
                    }
                } else {
                    $output[$key] = $input[$key];
                }
            }
        }
    
        // Return the array processing any additional functions filtered by this action.
        return apply_filters( 'see_wccs_validate_input', $output, $input );
    }

    /** 
    * normalizes settings values. Can be used to perform translations
    */
    public function normalize_settings_args ( $args ) {
        $args['value'] = isset( $args['value'] ) ? $args['value'] : 1;

        $args['placeholder'] = isset( $args['placeholder'] ) ? $args['placeholder'] : '';

        // get main settings array
        $option = get_option( $args['option_name'] );
    
        $args['setting_name'] = "{$args['option_name']}[{$args['id']}]";
  
        if ( isset( $option[$args['id']] ) ) {
            $args['current'] = $option[$args['id']];
        }
        // falback to default or empty if no value in option
        if ( !isset($args['current']) ) {
            $args['current'] = isset( $args['default'] ) ? $args['default'] : '';
        }

        return $args;
    }
}