<script>
  $(document).ready(function(){

    $("#signout-navbar").click(function() {
        $.ajax({
            method: "PUT",
            url: ECHIDNA_URI + "/token?user_token=" + $.cookie("user-token"),
            dataType: 'json'

        }).done(function( msg ) {
          if(ECHIDNA_DEBUG) {
            console.log(msg);
        }

          if(msg.success == 'true') {
              user_token = $.cookie("user-token", '');
              
              $('#signouted-modal').modal('show');

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
          }
        });
    });


  });
</script>