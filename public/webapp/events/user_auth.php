<script>
  $(document).ready(function(){
    function user_auth() {

      $.ajax({
          method: "GET",
          url: ECHIDNA_URI + "/auth?user_token=" + $.cookie("user-token"),
          dataType: 'json'

      }).done(function( msg ) {
        if(ECHIDNA_DEBUG) {
          console.log(msg);
        }

        if(msg.success == 'true') {

          // update cookie
          $.cookie("user-token", msg.user.user_token);

          // update navbar
          $("#users-and-hubs-navbar").removeClass('d-none');
          $("#users-and-hubs-navbar").addClass('d-inline');
          $("#documents-navbar").removeClass('d-none');
          $("#documents-navbar").addClass('d-inline');
          $("#add-document-navbar").removeClass('d-none');
          $("#add-document-navbar").addClass('d-inline');

          $("#user-navbar").removeClass('d-none');
          $("#user-navbar").addClass('d-inline');
          $("#signout-navbar").removeClass('d-none');
          $("#signout-navbar").addClass('d-inline');
          
          $("#register-navbar").removeClass('d-inline');
          $("#register-navbar").addClass('d-none');
          $("#restore-navbar").removeClass('d-inline');
          $("#restore-navbar").addClass('d-none');
          $("#signin-navbar").removeClass('d-inline');
          $("#signin-navbar").addClass('d-none');

          // set user name
          $("#user-name-navbar").text(msg.user.user_name);

          // show navbar
          $("#navbar").removeClass('d-none');
          $("#navbar").addClass('d-block');

        } else {

          // update cookie
          $.cookie("user-token", '');

          // update navbar
          $("#users-and-hubs-navbar").removeClass('d-inline');
          $("#users-and-hubs-navbar").addClass('d-none');
          $("#documents-navbar").removeClass('d-inline');
          $("#documents-navbar").addClass('d-none');
          $("#add-document-navbar").removeClass('d-inline');
          $("#add-document-navbar").addClass('d-none');

          $("#user-navbar").removeClass('d-inline');
          $("#user-navbar").addClass('d-none');
          $("#signout-navbar").removeClass('d-inline');
          $("#signout-navbar").addClass('d-none');
          
          $("#register-navbar").removeClass('d-none');
          $("#register-navbar").addClass('d-inline');
          $("#restore-navbar").removeClass('d-none');
          $("#restore-navbar").addClass('d-inline');
          $("#signin-navbar").removeClass('d-none');
          $("#signin-navbar").addClass('d-inline');

          // unset user name
          $("#user-name-navbar").text('');

          // show navbar
          $("#navbar").removeClass('d-none');
          $("#navbar").addClass('d-block');
        }
          
      });


    }


    user_auth();
  });
</script>