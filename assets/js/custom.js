

jQuery(document).ready(function () {
    jQuery('body').on('click', '.mobile_toggle', function (e) {
        console.log('ds');
        jQuery('.gutena-forms__header-container').toggleClass('show-nav');
    });

});