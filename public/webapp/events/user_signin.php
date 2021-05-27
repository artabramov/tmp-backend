<script>
    $(document).ready(function(){
        $("#signin-submit").click(function(){

            $.ajax({
                method: "POST",
                url: ECHIDNA_URI + "/pass?user_email=" + $("#signin-user-email").val() + "&user_pass=" + $("#signin-user-pass").val(),
                dataType: 'json'

            }).done(function( msg ) {
                if(ECHIDNA_DEBUG) {
                    console.log(msg);
                }

                if(msg.success == "false") {
                    $("#signin-error").removeClass('d-none');
                    $("#signin-error").addClass('d-block');
                    $("#signin-error").text(msg.error);

                } else {
                    $.cookie("user-token", msg.user.user_token);
                    window.location.href = ECHIDNA_URI + '/hello';
                }
            });
        });
    });
</script>
