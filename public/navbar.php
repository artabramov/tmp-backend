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

      <!-- register -->
      <li id="navbar-register" class="nav-item d-none">
          <a href="http://project.local/register" class="btn btn-outline-light" type="submit">Register</a>
      </li>

      <!-- signin -->
      <li id="navbar-signin" class="pl-2 nav-item d-none">
        <a class="nav-link" href="http://project.local/signin">Signin</a>
      </li>

      <!-- user -->
      <li id="navbar-user" class="nav-item dropdown d-none">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <span id="user-name"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="#">Select user</a>
          <a class="dropdown-item" href="#">Update user</a>
        </div>
      </li>

      <!-- signout -->
      <li id="navbar-signout" class="pl-2 nav-item d-none">
        <a id="a-signout" class="nav-link" href="#">Signout</a>
      </li>

    </ul>

  </div>
</nav>

<script>
  $(document).ready(function(){

    // user token
    user_token = typeof $.cookie("echidna-token") !== 'undefined' ? $.cookie("echidna-token") : '';

    // navbar
    $.ajax({
        method: "GET",
        url: "http://project.local/auth?user_token=" + user_token,
        dataType: 'json'

    }).done(function( msg ) {
        console.log(msg);
        console.log(msg.error);

        if(msg.success == 'true') {
            $("#navbar-user").removeClass('d-none');
            $("#navbar-user").addClass('d-inline');

            $("#navbar-signout").removeClass('d-none');
            $("#navbar-signout").addClass('d-inline');

            $("#user-name").text(msg.user.user_name);

        } else {
            $("#navbar-register").removeClass('d-none');
            $("#navbar-register").addClass('d-inline');

            $("#navbar-signin").removeClass('d-none');
            $("#navbar-signin").addClass('d-inline');
        }

    });

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





  });
</script>