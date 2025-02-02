App.register('ProductFinder', function() {
    "use strict";

    var publicMethods = {};

    // private jquery elements.
    var $_element,
        identifiers;

    /**
     * Initirat
     * @param $element
     */
    publicMethods.init = function($element) {
        $_element = $element;
        identifiers = {
            form             : 'form',
            searchField      : 'input[name="q"]',
            masterField      : 'input[name="master"]',
            productIdField   : 'input[name="product_id"]',
            activeRangeField : 'input[name="active_product_range"]',
            sizeSelect       : 'select[name="size"]',
            colorSelect      : 'select[name="color"]',
            quantitySelect   : 'select[name="quantity"]'
        };

        setupSearch();
        setupListeners();
    };

    /**
     * Reset the form elements this module cares about
     */
    publicMethods.resetForm = function($form) {

        if(!$form) {
            return false;
        }

        var $sizeSelect  = $(identifiers.sizeSelect, $form),
            $colorSelect = $(identifiers.colorSelect, $form),
            $quantitySelect = $(identifiers.quantitySelect, $form),
            $searchField = $(identifiers.searchField, $form),
            $masterField = $(identifiers.masterField, $form),
            $productIdField = $(identifiers.productIdField, $form);

        var $_ttdd = $('.tt-dropdown-menu', $form);
        $_ttdd.html('');
        $_ttdd.css('display', 'none');

        $searchField.val('');
        $masterField.val('');
        $productIdField.val('');

        $sizeSelect.prop('disabled', true);
        $('option:first', $sizeSelect).prop('selected', true);

        $colorSelect.prop('disabled', true);
        $('option:first', $colorSelect).prop('selected', true);

        $quantitySelect.prop('disabled', true);
        $('option:first', $quantitySelect).prop('selected', true);
    };


    /**
     * Setup the search form
     */
    var setupSearch = function() {
        var url = base_url + "quickorder/get-sku?name=%QUERY",
                $activeRangeField = $(identifiers.activeRangeField);

        // Override active product range, e.g. wishlist
        if (typeof $activeRangeField != 'undefined' && $activeRangeField.length == 1) {
          url = base_url + "quickorder/get-sku?active_product_range="+$activeRangeField.val()+"&name=%QUERY";
        }

        // setup typeahead search
        $_element.find(identifiers.searchField).typeahead({
            limit  : 30,
            name   : "sku",
            remote : {
                url: url,
                cache: false,
                beforeSend : function(jqXHR, settings) {
                    var query = settings.url.split('?')[1];
                    var params = {};
                    $.each(query.split('&'), function(index, element) {
                        var x = element.split('=');
                        params[x[0]] = x[1];
                    });

                    if ((typeof params.name === "undefined") ||
                        (params.name.length < 3) ||
                        (params.name.indexOf(' ') !== -1)
                    ) {
                        return false;
                    }
                }
            }
        });
    };

    /**
     * Setup listeners
     */
    var setupListeners = function() {

        // handle typeahead requests
        $_element.find(identifiers.searchField).on('typeahead:autocompleted typeahead:selected', function(event, item) {

            var $scope = $(this),
                $form = $scope.parents(identifiers.form),
                $masterField = $(identifiers.masterField, $form),
                $activeRangeField = $(identifiers.activeRangeField, $form);

            $masterField.val(item.name);

            var data = {
              master: item.name
            }

            // Override active product range, e.g. wishlist
            if (typeof $activeRangeField != 'undefined' && $activeRangeField.length == 1) {
              data.active_product_range = $activeRangeField.val();
            }

            publicMethods.stockCheck(data, 'size', $scope);
        });

        // handle found products ...
        $_element.on('on-products-found', function(event, data) {
            var $target,
                label,
                $scope = data.scope,
                $form = $scope.parents(identifiers.form),
                $sizeSelect = $(identifiers.sizeSelect, $form),
                $colorSelect = $(identifiers.colorSelect, $form);

            switch (data.target) {
                case 'size':
                    $target = $sizeSelect;
                    label   = Translator.trans('wishlist.select.size');
                    break;
                case 'color':
                    $target = $colorSelect;
                    label   = Translator.trans('wishlist.select.color');
                    break;
            }

            $('option', $target).remove();

            $target.append('<option value="">'+label+'</option>');
            $target.prop('disabled', false);

            $.each(data.data.products, function(key, value) {
                // we need this to filter out dubbs
                if ($('option[value="'+value[data.target]+'"]', $target).length) {
                    return true;
                }

                var labelOption = value[data.target];

                if ('size' === data.target) {
                    labelOption = value.size_label || value[data.target];
                }

                $target.append('<option value="'+value[data.target]+'" data-master="'+value.master+'" data-product-id="'+value.product_id+'">'+labelOption+'</option>');
            });

            $target.focus();
        });

        // handle not-found cases
        $_element.on('on-no-products-found', function(event, data) {
            console.log(data);
        });

        // look up colors from a master and a size
        $_element.find(identifiers.sizeSelect).on('change', function() {

            var $scope = $(this),
                $form = $scope.parents(identifiers.form),
                $masterField = $(identifiers.masterField, $form),
                $sizeSelect = $(identifiers.sizeSelect, $form),
                $activeRangeField = $(identifiers.activeRangeField, $form);

            var data = {
                master : $masterField.val(),
                size   : $sizeSelect.val()
            };

            // Override active product range, e.g. wishlist
            if (typeof $activeRangeField != 'undefined' && $activeRangeField.length == 1) {
              data.active_product_range = $activeRangeField.val();
            }

            publicMethods.stockCheck(data, 'color', $scope);
        });

        // set field state and focus
        $_element.find(identifiers.colorSelect).on('change', function() {

            var $scope          = $(this),
                $form           = $scope.parents(identifiers.form),
                $productIdField = $(identifiers.productIdField, $form),
                $colorSelect    = $scope,
                $quantitySelect = $(identifiers.quantitySelect, $form);

            $productIdField.val($(':selected', $colorSelect).data('productId'));
            $quantitySelect.prop('disabled', false);
            $quantitySelect.focus();
        });
    };

    /**
     * Perform the stock check
     *
     * @param data
     * @param target
     */
    publicMethods.stockCheck = function(data, target, scope) {
        var xhr = $.ajax({
            url      : base_url + "stock-check",
            dataType : 'json',
            type     : 'GET',
            data     : data,
            async    : false,
            cache    : false
        });

        xhr.done(function(response) {
            response.target = target;
            response.scope = scope;
            if (response.status) {
                return $_element.trigger('on-products-found', response);
            }

            $_element.trigger('on-no-products-found', response);
        });

        xhr.fail(function() {
            dialoug.error(Translator.trans('notice'), Translator.trans('an.error.occurred'));
        });
    };

    return publicMethods;
});
