<script>
    $(document).ready(function(){
        $("#restore-submit").click(function(){

            $.ajax({
                method: "GET",
                url: ECHIDNA_URI + "/pass?user_email=" + $("#restore-user-email").val(),
                dataType: 'json'

            }).done(function( msg ) {
                if(ECHIDNA_DEBUG) {
                    console.log(msg);
                }

                if(msg.success == "false") {

                    // show error
                    $("#restore-error").removeClass('d-none');
                    $("#restore-error").addClass('d-block');
                    $("#restore-error").text(msg.error);

                } else {

                    // switch errors
                    $('#restore-modal').modal('hide');
                    $('#restored-modal').modal('show');

                    // clear error
                    $("#restore-error").removeClass('d-block');
                    $("#restore-error").addClass('d-none');
                    $("#restore-error").text("");

                    // update signin email
                    $("#signin-user-email").val( $("#restore-user-email").val() );

                    // clear input
                    $("#restore-user-email").val("");
                }
            });
        });
    });
</script>
