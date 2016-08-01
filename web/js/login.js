/**
 * Created by Ruben Hazenbosch on 01-Aug-16.
 */

$('#login').submit(function(e) {

    // e.preventDefault();

    var url = '/ajax/login/login.php';

    $.ajax({
        type: 'post',
        url: url,
        data: $('#login').serialize(),
        success: function (response) {
           alert(response);
        }
    });

});
