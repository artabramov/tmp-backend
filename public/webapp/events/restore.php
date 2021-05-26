<script>
    $(document).ready(function(){
        $("#restore-submit").click(function(){
            user_email = $("#restore-user-email").val();

            $.ajax({
                method: "GET",
                url: ECHIDNA_URI + "/pass?user_email=" + user_email,
                dataType: 'json'

            }).done(function( msg ) {
                if(ECHIDNA_DEBUG) {
                    console.log(msg);
                }

                if(msg.success == "false") {
                    $("#restore-error").removeClass('d-none');
                    $("#restore-error").addClass('d-block');
                    $("#restore-error").text(msg.error);

                } else {
                    $('#restore-modal').modal('hide');
                    $('#restored-modal').modal('show');

                    $("#restore-error").removeClass('d-block');
                    $("#restore-error").addClass('d-none');
                    $("#restore-error").text("");

                    $("#signin-user-email").val( $("#restore-user-email").val() );
                    $("#restore-user-email").val("");
                }
            });
        });
    });
</script>
