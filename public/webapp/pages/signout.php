    <!-- header -->
    <?php require_once('./header.php'); ?>

    <!-- navbar -->
    <?php require_once('./navbar.php'); ?>

    <!-- body -->
    <div class="align_center">
        <div class="align_center_to_left">
            <div class="align_center_to_right">
                    
                <!-- done -->
                <div id="signin-text">
                    <h1>Done!</h1>
                    You signed out.<br/>
                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){

            $.ajax({
                method: "PUT",
                url: "http://project.local/token?user_token=" + user_token,
                dataType: 'json'

            }).done(function( msg ) {
                console.log(msg);
                if(msg.success == 'true') {
                    user_token = $.cookie("echidna-token", null);
                    window.location.href = "http://project.local/signout";
                }
            });

            update_navbar();
        });
    </script>

  </body>
</html>