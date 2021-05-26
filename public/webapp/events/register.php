<script>
    $(document).ready(function(){
        $("#register-submit").click(function(){

            /*
            $.ajax({
                method: "POST",
                url: "http://project.local/user?user_email=" + $("#user_email").val() + "&user_name=" + $("#user_name").val(),
                dataType: 'json'

            }).done(function( msg ) {
                console.log(msg);

                if(msg.success == 'false') {
                    $("#error").html('<div class="alert alert-warning" role="alert">' + msg.error + '</div>');

                } else {
                    $("#error").text('');

                    $("#register-form").removeClass('d-inline');
                    $("#register-form").addClass('d-none');

                    $("#register-text").removeClass('d-none');
                    $("#register-text").addClass('d-inline');
                }
            });
            */

            console.log('register');

        });
    });
</script>
