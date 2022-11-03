jQuery(document).ready(function(){

  // Add Color Picker to all inputs that have 'color-field' class
  jQuery('.color-picker').wpColorPicker();


  if ( true === jQuery("#bns_is_expirable").is(':checked') ) {
    jQuery("#bns-expiry-wrap").show();
  }
  else {
    jQuery("#bns-expiry-wrap").hide();
  }

  jQuery('body').on('click', '#bns_is_expirable', function() {
    if( jQuery(this).is(':checked')) {
      jQuery("#bns-expiry-wrap").show();
    } else {
      jQuery("#bns-expiry-wrap").hide();
    }
  });

});
