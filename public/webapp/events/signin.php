<script>
    $(document).ready(function(){
        $("#signin-submit").click(function(){
            user_email = $("#signin-user-email").val();
            user_pass = $("#signin-user-pass").val();

            $.ajax({
                method: "POST",
                url: ECHIDNA_URI + "/pass?user_email=" + user_email + "&user_pass=" + user_pass,
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
                    $.cookie("user-token", msg.user_token);
                    //$.cookie("user-name", msg.user_name);

                    $('#signin-modal').modal('hide');
                    $('#signined-modal').modal('show');

                    $("#signin-error").removeClass('d-block');
                    $("#signin-error").addClass('d-none');
                    $("#signin-error").text("");

                    $("#signin-user-email").val("");
                    $("#signin-user-pass").val("");

                    $("#user-navbar").removeClass('d-none');
                    $("#user-navbar").addClass('d-inline');
                    $("#signout-navbar").removeClass('d-none');
                    $("#signout-navbar").addClass('d-inline');
                    $("#register-navbar").removeClass('d-inline');
                    $("#register-navbar").addClass('d-none');
                    $("#restore-navbar").removeClass('d-inline');
                    $("#restore-navbar").addClass('d-none');
                    $("#signin-navbar").removeClass('d-inline');
                    $("#signin-navbar").addClass('d-none');
                }
            });
        });
    });
</script>
