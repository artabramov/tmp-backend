    <!-- header -->
    <?php require_once('./header.php'); ?>

    <!-- navbar -->
    <?php require_once('./navbar.php'); ?>

    <!-- body -->
    <div class="align_center">
        <div class="align_center_to_left">
            <div class="align_center_to_right">

                <!-- register form -->
                <div id="register-form" class="d-inline">
                    <h1>Register</h1>
                    <span id="error"></span>
                    <div class="form-group">
                        <label for="user_email">Email address</label>
                        <input id="user_email" type="text" class="form-control" aria-describedby="email_help" placeholder="">
                        <small id="email_help" class="form-text text-muted">We'll never share your email with anyone else.</small>
                    </div>
                    <div class="form-group">
                        <label for="user_name">User name</label>
                        <input id="user_name" class="form-control" type="text" placeholder="">
                    </div>
                    <button id="register" type="submit" class="btn btn-primary">Submit</button>
                </div>

                <!-- done -->
                <div id="register-text" class="d-none">
                    <h1>Done!</h1>
                    Registration finished. Check your email <br/>
                    and <a href="http://project.local/signin">signin by one-time pass</a>.
                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $("#register").click(function(){

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

            });
        });
    </script>
        

  </body>
</html>