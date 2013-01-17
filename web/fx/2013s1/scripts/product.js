(function(document, $) {


  var product = (function($) {
    var pub = {};

    pub.initZoom = function() {
      var currentColor = $('.productimage-large a').data('color');
      $('.productimage-large a').fullImageBox({'selector':'a[rel=full-image].color-'+currentColor});

      $('.productimage-large a, a.picture-zoom').on('click', function(e){
        e.preventDefault();
        $('.productimage-large a').first().fullImageBox('open');
      });
    };

    pub.initColors = function() {
      var currentColor = $('.productimage-large a').data('color');
      $('.productimage-small a').hide();
      $('.productimage-small a.color-'+currentColor).show();

      $('a.product-color').click(function(e){
        e.preventDefault();
        var currentNumber = $('.productimage-large a').data('number');
        var currentType = $('.productimage-large a').data('type');
        if(!$(this).hasClass('current')){
          currentColor = $(this).data('color');
          $('.product-color.current').removeClass('current');
          $(this).addClass('current');

          var $swapped = $('.productimage-small a.color-'+currentColor +'.number-'+currentNumber+'.type-'+currentType);
          if($swapped.length > 0){
            product.swapImages($swapped);
          }else{
            $swapped = $('.productimage-small a.color-'+currentColor+'.type-'+currentType);
            product.swapImages($swapped.first());
          }

          $('.productimage-small a').hide();
          $('.productimage-small a.color-'+currentColor).show();
          product.initZoom();
        }
      });
    };

    pub.swapImages = function(current) {
      var $small = $(current);
      var $small_img = $small.find('img').first();
      var small = {
        small  : $small_img.attr('src'),
        medium : $small.data('src'),
        large  : $small.attr('href'),
        id     : $small.data('id'),
        color  : $small.data('color'),
        number : $small.data('number'),
        type : $small.data('type')
      };

      var $large = $('.productimage-large a');
      var $large_img = $large.find('img').first();
      var large = {
        small  : $large.data('src'),
        medium : $large_img.attr('src'),
        large  : $large.attr('href'),
        id     : $large.data('id'),
        color  : $large.data('color'),
        number : $large.data('number'),
        type : $large.data('type')
      };

      $large.data('src', small.small);
      $large.attr('href', small.large);
      $large.data('id', small.id);
      $large.data('color', small.color);
      $large.data('number', small.number);
      $large.data('type', small.type);
      $large.removeClass('color-'+large.color);
      $large.addClass('color-'+small.color);
      $large.removeClass('number-'+large.number);
      $large.addClass('number-'+small.number);
      $large.removeClass('type-'+large.type);
      $large.addClass('type-'+small.type);
      $large_img.attr('src', small.medium);

      $small.data('src', large.medium);
      $small.attr('href', large.large);
      $small.data('id', large.id);
      $small.data('color', large.color);
      $small.data('number', large.number);
      $small.data('type', large.type);
      $small.removeClass('color-'+small.color);
      $small.addClass('color-'+large.color);
      $small.removeClass('number-'+small.number);
      $small.addClass('number-'+large.number);
      $small.removeClass('type-'+small.type);
      $small.addClass('type-'+large.type);
      $small_img.attr('src', large.small);

      $('.style-guide .element').hide();
      $('.style-guide .' + small.id).show();

      product.initStyleGuide();
    };
    // style guides
    pub.initStyleGuide = function() {
      $('.productimage-large a').each(function() {
        var $id = $(this).data('id');
        var $parent = $('.style-guide');
        var $guide = $('.'+ $id, $parent);
        $parent.hide();

        if ($guide.length) {
          $parent.show();
          $guide.show();
        }
      });
    };

    // tabs (description and washing instructions)
    pub.initTabs = function() {
      $("ul.tabs").tabs("div.panes > div");
    };

    // make a slideshow out of all product images.
    pub.initSlideshow = function() {
      var images = [];
      $('.productimage-small a').each(function() {
        images.push(this.href);
      });

      var contailer = '';
      for (var i=0; i<images.length; i++) {
        contailer += '<a href="'+images[i]+'" rel="slideshow"></a>';
      }
      $('#colorbox-slideshow').append(contailer);
      $('#colorbox-slideshow a').colorbox({
        rel:'slideshow',
        previous: '««',
        next: '»»',
        close: 'x',
        current: '{current} / {total}'
      });
    };

    // handle "add to basket"
    pub.initPurchase = function() {
      _resetForm();
      $('form.buy select.size, form.buy select.color').on('change', function() {
        var name = this.name;
        var value = this.value;

        dialoug.loading($(this));

        // make shure the form is updated!
        if ((name === 'size') && (value !== '')) {
          _resetForm(name);
        }

        var $form = $(this).closest('form');
        $.ajax({
          url: base_url + 'rest/v1/stock-check',
          dataType: 'json',
          data: $form.serialize(),
          async: false,
          success: function(response, textStatus, jqXHR) {
            if (false === response.status) {
              if (response.message) {
                dialoug.alert(Translator.get('js:notice'), response.message);
              }
              return;
            }

            if (undefined === response.data.products) {
              $('div', $form).replaceWith(Translator.get('js:out.of.stock'));
              return;
            }

            // populate color select with options
            if (name === 'size') {
              $.each(response.data.products, function(index, product) {
                $('select.color', $form).append('<option value="'+product.color+'">'+product.color+'</option>');
              });
              $('select.color', $form).closest('label').removeClass('off');
            }

            if (name == 'color') {
              var product = response.data.products[0];
              if (product.date) {
                dialoug.confirm(Translator.get('js:notice'), response.message, function(c) {
                  if (c == 'ok') {
                    $('select.quantity', $form).closest('label').removeClass('off');
                    $form.append('<input type="hidden" name="date" value="' + product.date + '">');
                  }
                });
              }
              else {
                $('select.quantity', $form).closest('label').removeClass('off');
              }
            }
          }
        });
        dialoug.stopLoading();
      });

      $('form.buy').on('submit', function(event) {
        event.preventDefault();

        var $form = $(this);
        if($('select.size', $form).val() && $('select.color', $form).val() && $('select.quantity', $form).val()){
          $.ajax({
            url: $form.attr('action'),
            dataType: 'json',
            type: 'POST',
            data: $form.serialize(),
            async: false,
            success: function(response, textStatus, jqXHR) {
              if (false === response.status) {
                if (response.message) {
                  dialoug.alert(Translator.get('js:notice'), response.message);
                }
              }
              else {
                window.scrollTo(window.scrollMinX, window.scrollMinY);
                $('#mini-basket a').html(response.data);
                dialoug.slideNotice(response.message, undefined, '.container > header');
              }
              _resetForm();
            },
            error: function(jqXHR, textStatus, errorThrown) {
              dialoug.error(Translator.get('js:notice!'), Translator.get('js:an.error.occurred'));
            }
          });
        }else{
          dialoug.notice(Translator.get('js:form.buy.choose.first'), 'error',3000, $('.button', $form).parent());
        }
      });
    };


    /**
     * track products the visitor has last seen.
     * currently we track the latest 10 products.
     */
    pub.initLastSeen = function() {
      if($('input#master').length) {
        var data = $.cookie('last_viewed') || { images:[], keys:[] };
        var id = $('input#master').val().replace(/[^a-z0-9]+/gi, '');

        if (-1 === data.keys.indexOf(id)) {
          data.images.push({
            title : $('h1').text(),
            url   : document.location.href,
            image : $('.productimage-large a').data('src')
          });

          data.keys.push(id);

          if (data.keys.length > 10) {
            data.keys.shift();
            data.images.shift();
          }

          $.cookie('last_viewed', data);
        }

        $.each(data.images, function(index, data) {
          $('.latest-seen-poducts').append('<a href="'+data.url+'"><img src="'+data.image+'" alt="'+data.title+'"></a> ');
        });
      }
    };


    var _resetForm = function(section) {
      var $this = $('form.buy');

      if ( (section !== undefined) && (section !== 'size') ) {
        $this.find('select.size option').each(function(index) {
          $(this).removeProp('selected');
        });
      }

      $this.find('select.color option').each(function(index) {
        if (this.value !== ''){
          $(this).remove();
        }
      });

      $this.find('label').each(function() {
        if (this.htmlFor !== 'size') {
          $(this).addClass('off');
        }
      });

      $this.find('select.quantity option').each(function(index) {
        $(this).removeProp('selected');
      });
      $('select.quantity option:first', $this).prop('selected', true);

      if (section === undefined) {
        $('select.size option:first', $this).prop('selected', true);
        $('select.color option:first', $this).prop('selected', true);
      }
    };

    return pub;
  })(jQuery);

  product.initZoom();
  product.initColors();
  product.initStyleGuide();
  product.initTabs();
  product.initSlideshow();
  product.initPurchase();
  product.initLastSeen();

  // icon toggler
  $('.productimage-small a').click(function(e) {
    e.preventDefault();
    product.swapImages(this);
  });

})(document, jQuery);
