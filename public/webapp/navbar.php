<!-- navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">

  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">

      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Users and hubs
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="#">Action</a>
          <a class="dropdown-item" href="#">Another action</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="#">Something else here</a>
        </div>
      </li>

      <li class="pl-2 nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Documents <span class="badge badge-pill badge-light">+2</span>
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="#">Action</a>
          <a class="dropdown-item" href="#">Another action</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="#">Something else here</a>
        </div>
      </li>

      <li class="pl-4 nav-item">
        <a href="#" class="btn btn-outline-light" type="submit"><i class="material-icons">add_circle_outline</i> Add document</a>
      </li>

    </ul>

    <ul class="navbar-nav ml-auto">

      <!-- MODAL register -->
      <li id="navbar-modal-register" class="nav-item d-inline">
          <a href="#" class="btn btn-outline-light" data-toggle="modal" data-target="#exampleModal">Modal</a>
      </li>

      <!-- register -->
      <li id="navbar-register" class="pl-2 nav-item d-none">
          <a href="http://project.local/register" class="btn btn-outline-light">Register</a>
      </li>

      <!-- signin -->
      <li id="navbar-signin" class="pl-2 nav-item d-none">
        <a class="nav-link" href="http://project.local/signin">Signin</a>
      </li>

      <!-- user -->
      <li id="navbar-user" class="nav-item dropdown d-none">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <span id="navbar-user-name"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="#">Select user</a>
          <a class="dropdown-item" href="#">Update user</a>
        </div>
      </li>

      <!-- signout -->
      <li id="navbar-signout" class="pl-2 nav-item d-none">
        <a class="nav-link" href="http://project.local/signout">Signout</a>
      </li>

    </ul>

  </div>
</nav>

<!-- MODALS -->



<!-------------------------------------------------------------------------------------------------------------->

<script>
  $(document).ready(function(){

    // user auth
    function user_auth() {

      if( typeof $.cookie("echidna-user-token") !== 'undefined' && $.cookie("echidna-user-token") !== null ) {
        $.ajax({
            method: "GET",
            url: "http://project.local/auth?user_token=" + $.cookie("echidna-user-token"),
            dataType: 'json'

        }).done(function( msg ) {
          console.log(msg);

          if(msg.success == 'true') {
            $.cookie("echidna-user-id", msg.user.id);
            $.cookie("echidna-user-email", msg.user.user_email);
            $.cookie("echidna-user-name", msg.user.user_name);

          } else {
            $.cookie("echidna-user-token", null);
            $.cookie("echidna-user-id", null);
            $.cookie("echidna-user-email", null);
            $.cookie("echidna-user-name", null);
          }
            
        });
      }



    }

    // update navbar
    function update_navbar() {

      if( typeof $.cookie("echidna-user-token") !== 'undefined' && $.cookie("echidna-user-token") !== null ) {

        $("#navbar-user").removeClass('d-none');
        $("#navbar-user").addClass('d-inline');

        $("#navbar-signout").removeClass('d-none');
        $("#navbar-signout").addClass('d-inline');

      } else {

        $("#navbar-register").removeClass('d-none');
        $("#navbar-register").addClass('d-inline');

        $("#navbar-signin").removeClass('d-none');
        $("#navbar-signin").addClass('d-inline');

        $("#navbar-user-name").text( $.cookie("echidna-user-name") );
      }
    }

    /*
    // signout
    $("#a-signout").click(function() {
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
    });
    */

    // main code
    user_auth();
    update_navbar();

    


  });
</script>