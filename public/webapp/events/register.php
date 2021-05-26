<script>
    $(document).ready(function(){
        $("#register-submit").click(function(){
            user_email = $("#register-user-email").val();
            user_name = $("#register-user-name").val();

            $.ajax({
                method: "POST",
                url: ECHIDNA_URI + "/user?user_email=" + user_email + "&user_name=" + user_name,
                dataType: 'json'

            }).done(function( msg ) {
                if(ECHIDNA_DEBUG) {
                    console.log(msg);
                }

                if(msg.success == "false") {
                    $("#register-error").removeClass('d-none');
                    $("#register-error").addClass('d-block');
                    $("#register-error").text(msg.error);

                } else {
                    $('#register-modal').modal('hide');
                    $('#registered-modal').modal('show');

                    $("#register-error").removeClass('d-block');
                    $("#register-error").addClass('d-none');
                    $("#register-error").text("");

                    $("#register-user-email").val("");
                    $("#register-user-name").val("");
                }
            });
        });
    });
</script>
