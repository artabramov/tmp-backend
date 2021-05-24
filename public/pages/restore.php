<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Hello, world!</title>
  </head>
  <body>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <!-- body -->

    <div class="align_center">
        <div class="align_center_to_left">
            <div class="align_center_to_right">
                <h1>Restore</h1>
                <span id="error"></span>
                <div class="form-group">
                    <label for="user_email">Email address</label>
                    <input id="user_email" type="text" class="form-control" aria-describedby="email_help" placeholder="noreply@noreply.no">
                </div>
                <button id="restore" type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $("#restore").click(function(){

                user_email = $("#user_email").val();

                $.ajax({
                    method: "GET",
                    url: "http://project.local/pass?user_email=" + user_email,
                    dataType: 'json'

                }).done(function( msg ) {
                    console.log(msg);
                    console.log(msg.error);

                    if(msg.success == 'false') {
                        $("#error").html('<div class="alert alert-warning" role="alert">' + msg.error + '</div>');

                    } else {
                        $("#error").text('');
                        
                    }
                });

            });
        });
    </script>
        

  </body>
</html>