    <!-- header -->
    <?php require_once('./header.php'); ?>

    <!-- navbar -->
    <?php require_once('./navbar.php'); ?>

    <!-- body -->
    <div class="align_center">
        <div class="align_center_to_left">
            <div class="align_center_to_right">

                <!-- signin form -->
                <div id="signin-form" class="d-inline">
                    <h1>Signin</h1>
                    <span id="error"></span>
                    <div class="form-group">
                        <label for="user_email">Email address</label>
                        <input id="user_email" type="text" class="form-control" aria-describedby="email_help" placeholder="">
                        <small id="email_help" class="form-text text-muted">We'll never share your email with anyone else.</small>
                    </div>
                    <div class="form-group">
                        <label for="user_pass">User pass</label>
                        <input id="user_pass" class="form-control" type="text" placeholder="">
                    </div>
                    <div class="form-group">
                        Get the <a href="http://project.local/restore">one-time password</a>.
                    </div>
                    <button id="signin" type="submit" class="btn btn-primary">Submit</button>
                </div>
                    
                <!-- done -->
                <div id="signin-text" class="d-none">
                    <h1>Done!</h1>
                    You signed in.<br/>
                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $("#signin").click(function(){

                user_email = $("#user_email").val();
                user_pass = $("#user_pass").val();

                $.ajax({
                    method: "POST",
                    url: "http://project.local/pass?user_email=" + user_email + "&user_pass=" + user_pass,
                    dataType: 'json'

                }).done(function( msg ) {
                    console.log(msg);

                    if(msg.success == 'false') {
                        $("#error").html('<div class="alert alert-warning" role="alert">' + msg.error + '</div>');

                    } else {
                        $("#error").text('');

                        $("#signin-form").removeClass('d-inline');
                        $("#signin-form").addClass('d-none');

                        $("#signin-text").removeClass('d-none');
                        $("#signin-text").addClass('d-inline');

                        $.cookie("echidna-token", msg.user_token);
                    }
                });

            });
        });
    </script>
        

  </body>
</html>