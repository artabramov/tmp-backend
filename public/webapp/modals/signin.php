<div id="signin-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="signin-title" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">

      <!-- modal header -->
      <div class="modal-header">
        <h5 class="modal-title">Signin</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <!-- error -->
        <div id="signin-error" class="alert alert-secondary d-none" role="alert"></div>

        <!-- user email -->
        <div class="form-group">
            <label for="signin-user-email">Email address</label>
            <input id="signin-user-email" type="text" class="form-control" aria-describedby="signin-user-email-help" placeholder="">
        </div>

        <!-- user pass -->
        <div class="form-group">
            <label for="signin-user-pass">One-time pass</label>
            <input id="signin-user-pass" class="form-control" type="text" placeholder="">
        </div>

      </div>

      <!-- modal footer -->
      <div class="modal-footer">
        <button id="signin-submit" type="button" class="btn btn-dark">Done</button>
      </div>

    </div>
  </div>
</div>
