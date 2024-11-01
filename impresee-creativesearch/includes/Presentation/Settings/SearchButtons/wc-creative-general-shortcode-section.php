<div id="<?php echo $shortcode_id; ?>-container">
    <h2><?php echo $title; ?></h2>
    <span><?php echo $short_description; ?></span>
    <br>
    <span style="font-weight: bold">Shortcode: </span><span id="<?php echo $shortcode_id; ?>"><?php echo $shortcode; ?></span><a href="#" class="click copy">Copy to clipboard</a>
    <h4>Optional attributes</h4>
   <?php echo $long_description; ?>
   <style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;}
.tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
.tg .tg-1wig{font-weight:bold;text-align:left;vertical-align:top}
.tg .tg-fymr{font-weight:bold;border-color:inherit;text-align:left;vertical-align:top}
.tg .tg-0pky{border-color:inherit;text-align:left;vertical-align:top}
.tg .tg-0lax{text-align:left;vertical-align:top}
</style>
<table class="tg">
  <tr>
    <th class="tg-fymr">Attribute</th>
    <th class="tg-fymr">Description</th>
    <th class="tg-1wig">Default value</th>
  </tr>
  <tr>
    <td class="tg-fymr">disable_photo</td>
    <td class="tg-0pky">Disable visual search</td>
    <td class="tg-0lax">false</td>
  </tr>
  <tr>
    <td class="tg-fymr">disable_sketch</td>
    <td class="tg-0pky">Disable Creative Search</td>
    <td class="tg-0lax">false</td>
  </tr>
  <tr>
    <td class="tg-1wig">photo_icon</td>
    <td class="tg-0lax">Icon of the button that'll trigger the visual search</td>
    <td class="tg-0lax"><a href="<?php echo $default_photo_icon; ?>" target="blank"><?php echo $default_photo_icon; ?></a></td>
  </tr>
  <tr>
    <td class="tg-1wig">sketch_icon</td>
    <td class="tg-0lax">Icon of the button that'll trigger the search by drawing</td>
    <td class="tg-0lax"><a href="<?php echo $default_sketch_icon; ?>" target="blank"><?php echo $default_sketch_icon; ?></a></td>
  </tr>
  <tr>
    <td class="tg-fymr">photo_class</td>
    <td class="tg-0pky">CSS class of the button that'll trigger the visual search</td>
    <td class="tg-0lax"><?php echo $default_photo_class; ?></td>
  </tr>
  <tr>
    <td class="tg-1wig">sketch_class</td>
    <td class="tg-0lax">CSS class of the button that'll trigger the search by drawing</td>
    <td class="tg-0lax"><?php echo $default_sketch_class; ?></td>
  </tr>
  <tr>
    <td class="tg-1wig">buttons_height</td>
    <td class="tg-0lax">Default height of the search buttons</td>
    <td class="tg-0lax"><?php echo $default_buttons_height; ?></td>
  </tr>
</table>
<script type="text/javascript">
jQuery(document).ready(function($) {
$(".click.copy").click(function(event){
    var tempElement = $("<input>");
    $("#<?php echo $shortcode_id; ?>").append(tempElement);
    tempElement.val($("#<?php echo $shortcode_id; ?>").text()).select();
    document.execCommand("Copy");
    tempElement.remove();
});
} );
</script>
</div>