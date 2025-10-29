/**
 * Domain Management - Toggle Individual Domains
 */
$(document).on('rex:ready', function() {
    $('#all-domains-locked').on('change', function() {
        if ($(this).val() == '1') {
            $('#individual-domains').slideUp();
        } else {
            $('#individual-domains').slideDown();
        }
    });
});
