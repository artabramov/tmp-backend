    <!-- header -->
    <?php require_once('./header.php'); ?>

    <!-- navbar -->
    <?php require_once('./navbar.php'); ?>

    <!-- body -->
    <div class="align_center">
        <div class="align_center_to_left">
            <div class="align_center_to_right">

                <!-- restore form -->
                <div id="restore-form" class="d-inline">
                    <h1>Restore</h1>
                    <span id="error"></span>
                    <div class="form-group">
                        <label for="user_email">Email address</label>
                        <input id="user_email" type="text" class="form-control" aria-describedby="email_help" placeholder="">
                    </div>
                    <button id="restore" type="submit" class="btn btn-primary">Submit</button>
                </div>

                <!-- done -->
                <div id="restore-text" class="d-none">
                    <h1>Done!</h1>
                    We sended one-time pass to email.<br/>
                    Please, check it and <a href="http://project.local/signin">signin</a>.
                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $("#restore").click(function(){

                $.ajax({
                    method: "GET",
                    url: "http://project.local/pass?user_email=" + $("#user_email").val(),
                    dataType: 'json'

                }).done(function( msg ) {
                    console.log(msg);

                    if(msg.success == 'false') {
                        $("#error").html('<div class="alert alert-warning" role="alert">' + msg.error + '</div>');

                    } else {
                        $("#error").text('');

                        $("#restore-form").removeClass('d-inline');
                        $("#restore-form").addClass('d-none');

                        $("#restore-text").removeClass('d-none');
                        $("#restore-text").addClass('d-inline');
                    }
                });

            });
        });
    </script>
        

  </body>
</html>