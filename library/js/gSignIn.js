var GoogleSigin = {
  client_id: '',
  google_signin : false,
  ele: null,
  hide_element : '.gs-hide-element',
  signin_btn : '.form-google-sign-button',
  error_container: null, 
  init: function(client_id, params = {}) {
    this.client_id = client_id;
    this.ele = Object.prototype.hasOwnProperty.call(params, 'ele') ? $(params['ele']) : null;
    this.hide_element = Object.prototype.hasOwnProperty.call(params, 'hide_element') ? params['hide_element'] : this.hide_element;
    this.signin_btn = Object.prototype.hasOwnProperty.call(params, 'signin_btn') ? params['signin_btn'] : this.signin_btn;
    this.error_container = Object.prototype.hasOwnProperty.call(params, 'error_container') ? params['error_container'] : this.error_container;

    $(this.ele).find('#google-signout').hide();

    google.accounts.id.initialize({
      client_id: this.client_id,
      callback: (googleUser) => { this.onSignInSuccess(googleUser, this) }
    });

    this.renderButton();
  },
  renderButton: function() {
      google.accounts.id.renderButton(
        $(this.ele).find("#google-signin")[0], 
        { 
          'theme': 'outline',
          'prompt': 'select_account',
          'scope': 'profile email',
          'onsuccess': (googleUser) => { this.onSignInSuccess(googleUser, this) },
          'onfailure': (error) => { this.onSignInFailure(error, this) }
        }  // customization attributes
      );
  },
  onSignInSuccess: function(googleUser, thisele) {
      
          const id_token = googleUser.credential;
          
          if(thisele.error_container != "") {
             $(thisele.error_container).find("#form-error-container").remove();
          }

          $(thisele.ele).find('#used-google-signin').val(true);
          $(thisele.ele).find('#google-signin-token').val(id_token);
          $(thisele.ele).find('#google-signout').show();
          $(thisele.ele).find(this.hide_element).hide();

          var element = thisele.ele[0].querySelector(thisele.signin_btn);
          
          element.click();
          element.disabled = true;
      
  },
  onSignInFailure: function(error, thisele) {
    if(thisele.error_container != "") {
       $(thisele.error_container).find("#form-error-container").remove();
       $(thisele.error_container).append("<div id='form-error-container' class='error'>Please use google log in</div>");
    }


  },
  do_google_signin: function() {
     this.google_signin = true;
  },
  signOut: function() {
      this.google_signin = false;
      const auth2 = gapi.auth2.getAuthInstance();
      auth2.signOut().then(function () {
          $(this.ele).find('#used-google-signin').val('');
          $(this.ele).find('#google-signin-token').val('');
          $(this.ele).find('#google-signout').hide();
          $(this.ele).find(this.hide_element).show();
      });
  }
};