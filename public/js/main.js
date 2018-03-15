+function() {
    $('a.read-more').on('click', function(e) {
        e.preventDefault();
        $(this).parent().find('.read-more')[$(this).hasClass('in') ? 'removeClass' : 'addClass']('in');
    });
    
    setTimeout(function() {
        $('.alert-fixed-top').fadeOut();
    }, 3000);
}();
