var account = (function($) {
  var pub = {};

  pub.init = function() {
    nnoInit();
    pub.zipToCityInit();

    if($('form.create').length) {
      var $form = $('form.create');
      var $a = $('form.create a');
      $form.find('label[for="customers_accept"]').append($a);

      // email address validation - check existing status
      $('#customers_email_email_address', $form).blur(function() {
        $form.removeClass('hasError');
        var $element = $('#customers_email_email_address', $form);
        $element.removeClass('error');

        // regex source: http://stackoverflow.com/questions/46155/validate-email-address-in-javascript
        var email_regex = RegExp(/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
        if (!$element.val()) {
          return;
        }else if(!email_regex.test($element.val())){
            $form.addClass('hasError');
            dialoug.notice(Translator.get('js:email.invalid'), 'error', 4800, $element.parent());
            $element.css({borderColor : '#f00' });
        }

        $.ajax({
          url: base_url+'account/check/email',
          type: 'post',
          dataType: 'json',
          data: { email: $element.val() },
          async: false,
          cache: false,
          success: function(response) {
            if (response.status === false) {
              $form.addClass('hasError');
              dialoug.notice(response.message, 'error', 4800, $form);
              $element.addClass('error');
            }
          }
        });
      });

      $('#customers_email_email_address_repeated', $form).blur(function() {
        $form.removeClass('hasError');
        $email = $('#customers_email_email_address', $form);
        $element = $('#customers_email_email_address_repeated', $form);

        if($element.val() && $email.val() !== $element.val()){
            $form.addClass('hasError');
            dialoug.notice(Translator.get('js:email.repeat.invalid'), 'error', 4800, $form);
            $element.addClass('error');
        }

      });

      $('#customers_password_pass', $form).blur(function() {
        $form.removeClass('hasError');
        $element = $('#customers_password_pass', $form);
        $element.removeClass('error');

        if($element.val() && $element.val().length < 5){
            $form.addClass('hasError');
            dialoug.notice(Translator.get('js:password.min.length'), 'error', 4800, $form);
            $element.addClass('error');
        }

      });

      $('#customers_password_pass_repeated', $form).blur(function() {
        $form.removeClass('hasError');
        $password = $('#customers_password_pass', $form);
        $element = $('#customers_password_pass_repeated', $form);
        $element.removeClass('error');

        if($element.val() && $password.val() !== $element.val()){
            $form.addClass('hasError');
            dialoug.notice(Translator.get('js:password.invalid.match'), 'error', 4800, $form);
            $element.addClass('error');
        }

      });

      $('#customers_phone', $form).blur(function() {
        $form.removeClass('hasError');
        $element = $('#customers_phone', $form);
        $element.removeClass('error');

        if($element.val() && (/^\d+$/.test($element.val()) !== true || $element.val().length < 8)){
            $form.addClass('hasError');
            dialoug.notice(Translator.get('js:phone.invalid'), 'error', 4800, $form);
            $element.addClass('error');
        }
      });

      $form.on('submit', function(e){
        $('input[required]', $form).each(function(i){
            if(!$(this).val()){
                e.preventDefault();
                $form.addClass('hasError');
                dialoug.notice(Translator.get('js:field.required'), 'error', 4800, $form);
                $(this).focus();
                $(this).select();
                return false;
            }
            if($(this).is(':checkbox') && !$(this).attr('checked')){
                e.preventDefault();
                $form.addClass('hasError');
                dialoug.notice(Translator.get('js:approve.conditions.required'), 'error', 4800, $form);
            }
        });
      });
    }

    $('a[rel="colorbox"]').colorbox();
  };

  function nnoInit()
  {
    $('form.nno').on('submit', function(event) {
      event.preventDefault();
      var $form = $(this);
      var $input = $('input[name="phone"]', $form);
      if ($input.val()) {
        // trigger loading box
        dialoug.loading('form.nno input[type="submit"]', 'søger efter adresse...');
        // fetch data
        $.getJSON(this.action + '/' + $input.val() , function(result) {
          if (result.status) {
            var $target = $('form.create');
            $target.find('#customers_first_name').val(result.data.christianname);
            $target.find('#customers_last_name').val(result.data.surname);
            $target.find('#customers_addresses_0_address_line_1').val(result.data.address);
            $target.find('#customers_addresses_0_postal_code').val(result.data.zipcode);
            $target.find('#customers_addresses_0_city').val(result.data.district);
            $target.find('#customers_phone').val(result.data.phone);
          }
          else {
            dialoug.alert('Woops!', result.message);
          }
          // reset form and kill loader
          $input.val('');
          dialoug.stopLoading();
        });
      }

      return false;
    });
  }

  pub.zipToCityInit = function ()
  {
    /**
     * auto complete city names when entering zip codes.
     * only works for se/no/dk so if the request is made via .com we skip this step
     */
    var tld_match = /\/([a-z]{2}_[A-Z]{2})\//;
    var tld = document.location.href.match(tld_match);
    try {
      if (tld[1] == 'da_DK' || tld[1] == 'sv_SE' || tld[1] == 'nb_NO' ) {
        $('#customers_addresses_0_city').attr('readonly', 'readonly');

        // set city to readonly
        $('#customers_addresses_0_city').focus(function () {
          dialoug.loading( '#customers_addresses_0_city', Translator.get('js:please.wait') );
          this.value = '';
          if ($('#customers_addresses_0_postal_code').val() === '') {
            $('#customers_addresses_0_postal_code')
              .css('border-color', '#a10000')
              .fadeOut(100).fadeIn(100)
              .fadeOut(100).fadeIn(100)
              .fadeOut(100).fadeIn(100)
              .focus();
            dialoug.stopLoading();
            return;
          }
          $.getJSON( base_url+'service/get-city-from-zip/'+$('#customers_addresses_0_postal_code').val(), function(data) {
            if (data.status && data.data.city) {
              $('#customers_addresses_0_city').val(data.data.city);
              $('#customers_addresses_0_postal_code').css('border-color', '#444345');
              try {
                $('#customers_phone').focus();
              } catch (e) {}
            }
            else {
              $('#customers_addresses_0_postal_code')
                .css('border-color', '#a10000')
                .fadeOut(100)
                .fadeIn(100)
                .fadeOut(100)
                .fadeIn(100)
                .fadeOut(100)
                .fadeIn(100)
                .focus();
            }
            dialoug.stopLoading();
          });
        });
      }
    } catch (e) {}
  };

  pub.orderHistoryInit = function() {
    $('a.edit').on('click', function(event) {
      event.preventDefault();
      var href = this.href;
      dialoug.confirm(Translator.get('js:notice'), Translator.get('js:edit.order.notice'), function(c) {
        if (c == 'ok') {
          document.location.href = href;
        }
      }, { maxWidth : '600px' });
    });

    $('a.delete').on('click', function(event) {
      event.preventDefault();
      var href = this.href;
      dialoug.confirm(Translator.get('js:notice'), Translator.get('js:delete.order.notice'), function(c) {
        if (c == 'ok') {
          document.location.href = href;
        }
      }, { maxWidth : '550px' });
    });
  };

  return pub;
})(jQuery);

if ($("#body-create-account").length) {
  account.init();
}
if ($("#body-events-create-customer").length) {
  account.zipToCityInit();
}

if ($("table#order-status").length) {
  account.orderHistoryInit();
}
