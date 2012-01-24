(function(jQuery) {
  var account = (function($) {
    var pub = {}

    pub.init = function() {
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

      if($('form.create').length) {
        var $form = $('form.create');
        var $a = $('form.create a');
        $form.find('label[for="customers_accept"]').append($a)
      }

      $('a[rel="colorbox"]').colorbox();
    };

    return pub;
  })(jQuery);

  account.init();
})(jQuery);
