+function() {
    $('.pwstrength').pwstrength({bootstrap3: true, usernameField: '#signup input[name=email]'});
    
    function confirmPassword() {
        var form = $(this).is('form') ? this : this.form;
        var password         = $(form).find('input[name=password]');
        var password_confirm = $(form).find('input[name=password-confirm]');
        
        if (password.val() && password_confirm.val() && password.val() !== password_confirm.val()) {
            password_confirm[0].setCustomValidity('Passwords should match');
        } else {
            password_confirm[0].setCustomValidity('');
        }
    }
    
    $('#reset-password :password').on('change', confirmPassword);
}();
