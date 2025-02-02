var account = (function ($) {
    var pub = {};

    pub.init = function () {
        nnoInit();
        pub.zipToCityInit();

        if ($('form.create').length) {
            var $form = $('form.create');

            // email address validation - check existing status
            $('#customers_email_email_address', $form).blur(function () {
                $form.removeClass('hasError');
                var $element = $('#customers_email_email_address', $form);
                $element.removeClass('error');

                // regex source: http://stackoverflow.com/questions/46155/validate-email-address-in-javascript
                var email_regex = RegExp(/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
                if (!$element.val()) {
                    return;
                } else if (!email_regex.test($element.val())) {
                    scrollToAndShowError($element, $form, Translator.trans('email.invalid'));
                }

                $.ajax({
                    url: base_url + 'account/check/email',
                    type: 'post',
                    dataType: 'json',
                    data: {email: $element.val()},
                    async: false,
                    cache: false,
                    success: function (response) {
                        if (response.status === false) {
                            scrollToAndShowError($element, $form, response.message);
                        }
                    }
                });
            });

            $('#customers_email_email_address_repeated', $form).blur(function () {
                $form.removeClass('hasError');
                $email = $('#customers_email_email_address', $form);
                $element = $('#customers_email_email_address_repeated', $form);

                if ($element.val() && $email.val() !== $element.val()) {
                    scrollToAndShowError($element, $form, Translator.trans('email.repeat.invalid'));
                }
            });

            $('#customers_password_pass', $form).blur(function () {
                $form.removeClass('hasError');
                $element = $('#customers_password_pass', $form);
                $element.removeClass('error');

                if ($element.val() && $element.val().length < 5) {
                    scrollToAndShowError($element, $form, Translator.trans('password.min.length'));
                }
            });

            $('#customers_password_pass_repeated', $form).blur(function () {
                $form.removeClass('hasError');
                $password = $('#customers_password_pass', $form);
                $element = $('#customers_password_pass_repeated', $form);
                $element.removeClass('error');

                if ($element.val() && $password.val() !== $element.val()) {
                    scrollToAndShowError($element, $form, Translator.trans('password.invalid.match'));
                }

            });

            $('#customers_phone', $form).blur(function () {
                $form.removeClass('hasError');
                $element = $('#customers_phone', $form);
                $element.removeClass('error');

                if ($element.val() && (/^\d+$/.test($element.val()) !== true || $element.val().length < 8)) {
                    scrollToAndShowError($element, $form, Translator.trans('phone.invalid'));
                }
            });

            $form.on('submit', function (e) {
                $('input[required]', $form).each(function (i) {
                    if (!$(this).val()) {
                        e.preventDefault();
                        $form.addClass('hasError');
                        dialoug.notice(Translator.trans('field.required'), 'error', 4800, $form);
                        $(this).focus();
                        $(this).select();
                        return false;
                    }
                    if ($(this).is(':checkbox') && !$(this).attr('checked')) {
                        e.preventDefault();
                        scrollToAndShowError($(this), $form, Translator.trans('approve.conditions.required'));
                    }
                });
            });
        }
    };

    function nnoInit() {
        $('form.nno').on('submit', function (event) {
            event.preventDefault();
            var $form = $(this);
            var $input = $('input[name="phone"]', $form);
            if ($input.val()) {
                // trigger loading box
                dialoug.loading('form.nno input[type="submit"]', Translator.trans('please.wait'));
                // fetch data
                $.getJSON(this.action + '/' + $input.val(), function (result) {
                    if (result.status) {
                        var data = result.data.number;
                        var $target = $('form.create');
                        $target.find('#customers_first_name').val(data.christianname);
                        $target.find('#customers_last_name').val(data.surname);
                        $target.find('#customers_addresses_0_address_line_1').val(data.address);
                        $target.find('#customers_addresses_0_postal_code').val(data.zipcode);
                        $target.find('#customers_addresses_0_city').val(data.district);
                        $target.find('#customers_phone').val(data.phone);
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

    function scrollToAndShowError($element, $form, error) {
        $form.addClass('hasError');
        dialoug.notice(error, 'error', 4800, $element);
        $element.addClass('error');
    }

    pub.zipToCityInit = function () {
        /**
         * auto complete city names when entering zip codes.
         * only works for se/no/dk so if the request is made via .com we skip this step
         */
        var tld_match = /\/([a-z]{2}_[A-Z]{2})\//;
        var tld = document.location.href.match(tld_match);
        try {
            // set city to readonly
            if (jQuery.inArray(tld[1], ['da_DK', 'fi_FI', 'sv_SE', 'nb_NO', 'nl_NL', 'de_DE', 'de_CH', 'de_AT']) > -1) {
                $('#customers_addresses_0_city').attr('readonly', 'readonly');
                var $elm = $('#customers_addresses_0_postal_code');

                $(document).on('focusout blur', '#customers_addresses_0_postal_code', function () {
                    if ($elm.val() === '') {
                        animateAndFocus($elm);
                        return;
                    }

                    dialoug.loading('#customers_addresses_0_city', Translator.trans('please.wait'));

                    $.getJSON(base_url + 'muneris/gpc/' + $elm.val(), function (data) {
                        if (data.status && data.data.postcodes.length) {
                            var $city = $('#customers_addresses_0_city');
                            var $select_city = $('#customers_addresses_0_city_select_temp');
                            if (data.data.postcodes.length > 1) {
                                // Many cities with same zip.
                                // Hide the city field and add a dropdown with all the cities.
                                $city.hide();
                                if ($select_city.length === 0) {
                                    $('<select id="customers_addresses_0_city_select_temp"></select>')
                                        .appendTo($city.parent())
                                        .on('change', function (e) {
                                            $city.val(this.value);
                                        });
                                } else {
                                    $('option', $select_city).remove();
                                    $select_city.show();
                                }

                                $.each(data.data.postcodes, function (index, postcode) {
                                    // Add all cities as an option.
                                    $select_city.append($('<option value="' + postcode.city + '">' + postcode.city + '</option>'));
                                });
                            } else {
                                // Only 1 result.
                                $select_city.hide();
                                $city.show();
                            }

                            $city.val(data.data.postcodes[0].city);
                            $elm.css('border-color', '#444345');

                            try {
                                $('#customers_phone').focus();
                            } catch (e) {}
                        } else {
                            animateAndFocus($elm);
                        }

                        dialoug.stopLoading();
                    });
                });

                $(document).on('focus', '#customers_addresses_0_city', function () {
                    if ($elm.val() === '') {
                        animateAndFocus($elm);
                        dialoug.stopLoading();
                    }
                });
            }
        } catch (e) {}
    };

    var animateAndFocus = function($element) {
        $element
            .animate({backgroundColor: '#c18185', color: '#fff'})
            .animate({backgroundColor: '#fff', color: '#444'}, function() {
                $element.focus();
            });
    };

    pub.orderHistoryInit = function () {
        $('a.edit').on('click', function (event) {
            event.preventDefault();
            var $a = $(this);
            var href = this.href;
            dialoug.confirm(Translator.trans('notice'), Translator.trans('edit.order.notice'), function (c) {
                if (c == 'ok') {
                    dialoug.loading($a, '', 'prepend');
                    document.location.href = href;
                }
            }, {maxWidth: '600px'});
        });

        $('a.delete').on('click', function (event) {
            event.preventDefault();
            var href = this.href;
            dialoug.confirm(Translator.trans('notice'), Translator.trans('delete.order.notice'), function (c) {
                if (c == 'ok') {
                    document.location.href = href;
                }
            }, {maxWidth: '550px'});
        });
    };

    return pub;
})(jQuery);

if ($("#body-create-account, #body-edit-account").length) {
    account.init();
}

if ($("#body-events-create-customer").length) {
    account.zipToCityInit();
}

if ($("table#order-status").length) {
    account.orderHistoryInit();
}

