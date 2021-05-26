<script>
  $(document).ready(function(){
    function user_auth() {

      if( typeof $.cookie("user-token") !== 'undefined' && $.cookie("user-token") !== null ) {
        $.ajax({
            method: "GET",
            url: ECHIDNA_URI + "/auth?user_token=" + $.cookie("user-token"),
            dataType: 'json'

        }).done(function( msg ) {
          if(ECHIDNA_DEBUG) {
            console.log(msg);
          }

          if(msg.success == 'true') {
            $.cookie("user-name", msg.user.user_name);

          } else {
            $.cookie("user-token", '');
            $.cookie("user-name", '');
          }
            
        });
      }

      //console.log($.cookie("user-token"));
      //console.log(typeof $.cookie("user-token"));

      if( typeof $.cookie("user-token") !== 'undefined' && $.cookie("user-token") !== '' ) {
        $("#user-name-navbar").text( $.cookie("user-name") );

        $("#user-navbar").removeClass('d-none');
        $("#user-navbar").addClass('d-inline');

        $("#signout-navbar").removeClass('d-none');
        $("#signout-navbar").addClass('d-inline');

      } else {
        $("#register-navbar").removeClass('d-none');
        $("#register-navbar").addClass('d-inline');

        $("#restore-navbar").removeClass('d-none');
        $("#restore-navbar").addClass('d-inline');

        $("#signin-navbar").removeClass('d-none');
        $("#signin-navbar").addClass('d-inline');
      }

    }

    user_auth();
  });
</script>