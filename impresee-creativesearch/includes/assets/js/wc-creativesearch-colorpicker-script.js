jQuery(document).ready(function($){
    var colorPickerOptions = {
        // you can declare a default color here,
        // or in the data-default-color attribute on the input
        defaultColor: '#9CD333',
        // hide the color picker controls on load
        hide: true,
        // show a group of common colors beneath the square
        // or, supply an array of colors to customize further
        palettes: true
    };
 
    $('#impresee_main_color_picker').wpColorPicker(colorPickerOptions);
    $('#impresee_on_sale_label_color').wpColorPicker(colorPickerOptions);
});