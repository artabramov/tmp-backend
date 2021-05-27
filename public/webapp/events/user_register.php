<script>
    $(document).ready(function(){
        $("#register-submit").click(function(){

            $.ajax({
                method: "POST",
                url: ECHIDNA_URI + "/user?user_email=" + $("#register-user-email").val() + "&user_name=" + $("#register-user-name").val(),
                dataType: 'json'

            }).done(function( msg ) {
                if(ECHIDNA_DEBUG) {
                    console.log(msg);
                }

                if(msg.success == "false") {

                    // show error
                    $("#register-error").removeClass('d-none');
                    $("#register-error").addClass('d-block');
                    $("#register-error").text(msg.error);

                } else {

                    // switch modals
                    $('#register-modal').modal('hide');
                    $('#registered-modal').modal('show');

                    // hide error
                    $("#register-error").removeClass('d-block');
                    $("#register-error").addClass('d-none');
                    $("#register-error").text("");

                    // update signin email
                    $("#signin-user-email").val( $("#register-user-email").val() );

                    // clear inputs
                    $("#register-user-email").val("");
                    $("#register-user-name").val("");
                }
            });
        });
    });
</script>
