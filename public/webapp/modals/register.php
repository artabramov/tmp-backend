<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">

      <!-- header -->
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Register</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <!-- error -->
        <div class="alert alert-secondary" role="alert">error text here</div>

        <!-- user email -->
        <span id="error"></span>
        <div class="form-group">
            <label for="register-user_email">Email address</label>
            <input id="register-user-email" type="text" class="form-control" aria-describedby="register-user-email-help" placeholder="">
            <small id="register-user-email-help" class="form-text text-muted">We'll never share your email with anyone else.</small>
        </div>

        <!-- user name -->
        <div class="form-group">
            <label for="register-user-name">User name</label>
            <input id="register-user-name" class="form-control" type="text" placeholder="">
        </div>

      </div>

      <!-- footer -->
      <div class="modal-footer">
        <button id="register-submit" type="button" class="btn btn-dark">Save changes</button>
      </div>

    </div>
  </div>
</div>
