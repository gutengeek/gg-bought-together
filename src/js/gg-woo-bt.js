( function ( $ ) {
    var GG_Woo_BT = {
        init: function () {
            GG_Woo_BT.run();
            GG_Woo_BT.access();
            GG_Woo_BT.changeQty();
            GG_Woo_BT.hookFoundVariation();
            GG_Woo_BT.hookResetData();
        },
        run: function () {
            $( document ).ready( function ( $ ) {
                var $wraper = $( '.gg_woo_bt-wrap' );
                if ( !$wraper.length ) {
                    return;
                }

                $wraper.each( function () {
                    GG_Woo_BT.prepareData( $( this ) );
                } );
            } );
        },
        access: function () {
            $( document ).on( 'click touch', '.single_add_to_cart_button', function ( e ) {
                if ( $( this ).hasClass( 'gg_woo_bt-disabled' ) ) {
                    e.preventDefault();
                }
            } );

            $( document ).on( 'change', '.gg_woo_bt-checkbox', function () {
                var $wrap = $( this ).closest( '.gg_woo_bt-wrap' );
                GG_Woo_BT.prepareData( $wrap );
            } );

            $( document ).on( 'change keyup mouseup', '.gg_woo_bt-main-qty', function () {
                var value = $( this ).val();

                $( this ).closest( '.gg_woo_bt-product-main' ).attr( 'data-qty', value );
                $( this ).closest( '.summary' ).find( 'form.cart .quantity .qty' ).val( value ).trigger( 'change' );
            } );

            $( document ).on( 'change keyup mouseup', '.gg_woo_bt-qty', function () {
                var $el = $( this );
                var $wraper = $el.closest( '.gg_woo_bt-wrap' );
                var $product = $el.closest( '.gg_woo_bt-product' );
                var $checkbox = $product.find( '.gg_woo_bt-checkbox' );
                var value = parseFloat( $el.val() );

                if ( $checkbox.prop( 'checked' ) ) {
                    var min_val = parseFloat( $el.attr( 'min' ) );
                    var max_val = parseFloat( $el.attr( 'max' ) );

                    if ( value < min_val ) {
                        $el.val( min_val );
                    }

                    if ( value > max_val ) {
                        $el.val( max_val );
                    }

                    $product.attr( 'data-qty', $el.val() );

                    GG_Woo_BT.prepareData( $wraper );
                }
            } );
        },
        prepareData: function ( $wrap ) {
            var wrap_id = $wrap.attr( 'data-id' );

            if ( wrap_id !== undefined && parseInt( wrap_id ) > 0 ) {
                var container = GG_Woo_BT.getContainer( wrap_id );
                var $container = $wrap.closest( container );

                GG_Woo_BT.isReady( $container );
                GG_Woo_BT.calcPrice( $container );
                GG_Woo_BT.saveIds( $container );

                if ( gg_woo_bt_params.counter !== 'hide' ) {
                    GG_Woo_BT.updateCount( $container );
                }
            }
        },
        hookFoundVariation: function () {
            $( document ).on( 'found_variation', function ( e, t ) {
                var $wrap = $( e[ 'target' ] ).closest( '.gg_woo_bt-wrap' );
                var $products = $( e[ 'target' ] ).closest( '.gg_woo_bt-products' );
                var $product = $( e[ 'target' ] ).closest( '.gg_woo_bt-product' );

                if ( $product.length ) {
                    var new_price = $product.attr( 'data-new-price' );

                    if ( isNaN( new_price ) ) {
                        new_price = t[ 'display_price' ] * parseFloat( new_price ) / 100;
                    }

                    $product.find( '.gg_woo_bt-price-ori' ).hide();
                    $product.find( '.gg_woo_bt-price-new' )
                            .html( GG_Woo_BT.priceHtml( t[ 'display_price' ], new_price ) )
                            .show();

                    if ( t[ 'is_purchasable' ] && t[ 'is_in_stock' ] ) {
                        $product.attr( 'data-id', t[ 'variation_id' ] );
                        $product.attr( 'data-price', t[ 'display_price' ] );
                    } else {
                        $product.attr( 'data-id', 0 );
                    }

                    if ( t[ 'availability_html' ] && t[ 'availability_html' ] !== '' ) {
                        $product.find( '.gg_woo_bt-availability' ).html( t[ 'availability_html' ] ).show();
                    } else {
                        $product.find( '.gg_woo_bt-availability' ).html( '' ).hide();
                    }

                    if ( t[ 'image' ][ 'url' ] && t[ 'image' ][ 'srcset' ] ) {
                        $product.find( '.gg_woo_bt-thumb-ori' ).hide();
                        $product.find( '.gg_woo_bt-thumb-new' )
                                .html( '<img src="' + t[ 'image' ][ 'url' ] + '" srcset="' +
                                    t[ 'image' ][ 'srcset' ] + '"/>' )
                                .show();
                    }

                    $( '.product_meta .sku' ).text( $products.attr( 'data-product-sku' ) );
                    $( e[ 'target' ] ).closest( '.variations_form' ).trigger( 'reset_image' );
                } else {
                    $wrap = $( e[ 'target' ] ).closest( '.summary' ).find( '.gg_woo_bt-wrap' );
                    $products = $( e[ 'target' ] ).closest( '.summary' ).find( '.gg_woo_bt-products' );
                    $products.attr( 'data-product-id', t[ 'variation_id' ] );
                    $products.attr( 'data-product-sku', t[ 'sku' ] );
                    $products.attr( 'data-product-price', t[ 'display_price' ] );
                }

                GG_Woo_BT.prepareData( $wrap );
            } );
        },
        hookResetData: function () {
            $( document ).on( 'reset_data', function ( e ) {
                var $wrap = $( e[ 'target' ] ).closest( '.gg_woo_bt-wrap' );
                var $products = $( e[ 'target' ] ).closest( '.gg_woo_bt-products' );
                var $product = $( e[ 'target' ] ).closest( '.gg_woo_bt-product' );

                if ( $product.length ) {
                    $product.attr( 'data-id', 0 );
                    $( e[ 'target' ] ).closest( '.variations_form' ).find( 'p.stock' ).remove();
                    $( '.product_meta .sku' ).text( $products.attr( 'data-product-sku' ) );
                    $product.find( '.gg_woo_bt-availability' ).html( '' ).hide();
                    $product.find( '.gg_woo_bt-thumb-new' ).hide();
                    $product.find( '.gg_woo_bt-thumb-ori' ).show();
                    $product.find( '.gg_woo_bt-price-new' ).hide();
                    $product.find( '.gg_woo_bt-price-ori' ).show();
                } else {
                    $wrap = $( e[ 'target' ] ).closest( '.summary' ).find( '.gg_woo_bt-wrap' );
                    $products = $( e[ 'target' ] ).closest( '.summary' ).find( '.gg_woo_bt-products' );
                    $products.attr( 'data-product-id', 0 );
                    $products.attr( 'data-product-price', 0 );
                    $products.attr( 'data-product-sku', $products.attr( 'data-product-o-sku' ) );
                }

                GG_Woo_BT.prepareData( $wrap );
            } );
        },
        getContainer: function ( id ) {
            var $wrap_el = $( '.gg_woo_bt-wrap-' + id );
            if ( $wrap_el.closest( '#product-' + id ).length ) {
                return '#product-' + id;
            }

            if ( $wrap_el.closest( '.product.post-' + id ).length ) {
                return '.product.post-' + id;
            }

            if ( $wrap_el.closest( 'div.product' ).length ) {
                return 'div.product';
            }

            return 'body.single-product';
        },
        isReady: function ( $wrap ) {
            var $products = $wrap.find( '.gg_woo_bt-products' );
            var $notice = $wrap.find( '.gg_woo_bt-notice' );
            var $ids = $wrap.find( '.gg_woo_bt-ids' );
            var $btn = $wrap.find( '.single_add_to_cart_button' );
            var is_selection = false;
            var selection_name = '';
            var optional = $products.attr( 'data-optional' );

            if ( ( optional === 'on' ) && ( $products.find( '.gg_woo_bt-product-main' ).length > 0 ) ) {
                $( 'form.cart > .quantity' ).hide();
                $( 'form.cart .woocommerce-variation-add-to-cart > .quantity' ).hide();
            }

            if ( ( gg_woo_bt_params.position === 'before_add_to_cart' ) &&
                ( $products.attr( 'data-product-type' ) === 'variable' ) &&
                ( $products.attr( 'data-variables' ) === 'no' ) ) {
                $products.closest( '.gg_woo_bt-wrap' ).insertAfter( $ids );
                $products.find( '.gg_woo_bt-qty' ).removeClass( 'qty' );
            }

            $products.find( '.gg_woo_bt-product-together' ).each( function () {
                var $el = $( this );
                var is_checked = $el.find( '.gg_woo_bt-checkbox' ).prop( 'checked' );
                var val_id = parseInt( $el.attr( 'data-id' ) );

                if ( !is_checked ) {
                    $el.addClass( 'gg_woo_bt-unchecked' );

                    if ( !$el.hasClass( 'show-variation-select' ) ) {
                        $el.find( '.variations_form' ).hide();
                    }
                } else {
                    $el.removeClass( 'gg_woo_bt-unchecked' );
                    if ( !$el.hasClass( 'show-variation-select' ) ) {
                        $el.find( '.variations_form' ).show();
                    }
                }

                if ( is_checked && ( val_id == 0 ) ) {
                    is_selection = true;

                    if ( selection_name === '' ) {
                        selection_name = $el.attr( 'data-name' );
                    }
                }
            } );

            if ( is_selection ) {
                $btn.addClass( 'gg_woo_bt-disabled gg_woo_bt-selection' );
                $notice.html(
                    gg_woo_bt_params.text.variation_notice.replace( '%s', '<strong>' + selection_name + '</strong>' ) )
                       .slideDown();
            } else {
                $btn.removeClass( 'gg_woo_bt-disabled gg_woo_bt-selection' );
                $notice.html( '' ).slideUp();
            }
        },
        changeQty: function () {
            $( document ).on( 'change', 'form.cart .qty', function () {
                var $el = $( this );
                var qty = parseFloat( $el.val() );

                if ( $el.hasClass( 'gg_woo_bt-qty' ) ) {
                    return;
                }

                if ( !$el.closest( 'form.cart' ).find( '.gg_woo_bt-ids' ).length ) {
                    return;
                }

                var wrap_id = $el.closest( 'form.cart' ).find( '.gg_woo_bt-ids' ).attr( 'data-id' );
                var $wrap = $( '.gg_woo_bt-wrap-' + wrap_id );
                var $products = $wrap.find( '.gg_woo_bt-products' );
                var optional = $products.attr( 'data-optional' );
                var sync_qty = $products.attr( 'data-sync-qty' );

                $products.find( '.gg_woo_bt-product-main' ).attr( 'data-qty', qty );

                if ( ( optional !== 'on' ) && ( sync_qty === 'on' ) ) {
                    $products.find( '.gg_woo_bt-product-together' ).each( function () {
                        var _qty = parseFloat( $( this ).attr( 'data-qty-ori' ) ) * qty;

                        $( this ).attr( 'data-qty', _qty );
                        $( this ).find( '.gg_woo_bt-qty-num .gg_woo_bt-qty' ).html( _qty );
                    } );
                }

                GG_Woo_BT.prepareData( $wrap );
            } );
        },
        calcPrice: function ( $wrap ) {
            var $products = $wrap.find( '.gg_woo_bt-products' );
            var $product_this = $products.find( '.gg_woo_bt-product-main' );
            var $total = $wrap.find( '.gg_woo_bt-total' );
            var $btn = $wrap.find( '.single_add_to_cart_button' );
            var count = 0, total = 0;
            var total_html = '';
            var discount = parseFloat( $products.attr( 'data-discount' ) );
            var ori_price = parseFloat( $products.attr( 'data-product-price' ) );
            var ori_price_suffix = $products.attr( 'data-product-price-suffix' );
            var ori_qty = parseFloat( $btn.closest( 'form.cart' ).find( 'input.qty' ).val() );
            var total_ori = ori_price * ori_qty;
            var main_price_selector = gg_woo_bt_params.main_price_selector;
            var show_price = $products.attr( 'data-show-price' );
            var fix = Math.pow( 10, Number( gg_woo_bt_params.price_decimals ) + 1 );

            $products.find( '.gg_woo_bt-product-together' ).each( function () {
                var $el = $( this );
                var _checked = $el.find( '.gg_woo_bt-checkbox' ).prop( 'checked' );
                var _id = parseInt( $el.attr( 'data-id' ) );
                var _qty = parseFloat( $el.attr( 'data-qty' ) );
                var _price = $el.attr( 'data-new-price' );
                var _price_suffix = $el.attr( 'data-price-suffix' );
                var origin_price = $el.attr( 'data-price' );
                var regular_price = $el.attr( 'data-regular-price' );
                var origin_total = 0, _total = 0;

                if ( ( _qty > 0 ) && ( _id > 0 ) ) {
                    origin_total = _qty * origin_price;

                    if ( isNaN( _price ) ) {
                        if ( _price == '100%' ) {
                            origin_total = _qty * regular_price;
                            _total = _qty * origin_price;
                        } else {
                            _total = origin_total * parseFloat( _price ) / 100;
                        }
                    } else {
                        _total = _qty * _price;
                    }

                    if ( show_price === 'total' ) {
                        $el.find( '.gg_woo_bt-price-ori' ).hide();
                        $el.find( '.gg_woo_bt-price-new' )
                           .html( GG_Woo_BT.priceHtml( origin_total, _total ) + _price_suffix )
                           .show();
                    }

                    if ( _checked ) {
                        count++;
                        total += _total;
                    }
                }
            } );

            total = Math.round( total * fix ) / fix;

            if ( $product_this.length ) {
                var _qty = parseFloat( $product_this.attr( 'data-qty' ) );
                var _price_suffix = $product_this.attr( 'data-price-suffix' );

                if ( total > 0 ) {
                    var _price = $product_this.attr( 'data-new-price' );
                    var origin_price = $product_this.attr( 'data-price' );
                    var origin_total = _qty * origin_price,
                        _total = _qty * _price;

                    $product_this.find( '.gg_woo_bt-price-ori' ).hide();
                    $product_this.find( '.gg_woo_bt-price-new' )
                                 .html( GG_Woo_BT.priceHtml( origin_total, _total ) + _price_suffix )
                                 .show();
                } else {
                    var _price = $product_this.attr( 'data-price' );
                    var regular_price = $product_this.attr( 'data-regular-price' );
                    var origin_total = _qty * regular_price,
                        _total = _qty * _price;

                    $product_this.find( '.gg_woo_bt-price-ori' ).hide();
                    $product_this.find( '.gg_woo_bt-price-new' )
                                 .html( GG_Woo_BT.priceHtml( origin_total, _total ) + _price_suffix )
                                 .show();
                }
            }

            if ( count > 0 ) {
                total_html = GG_Woo_BT.formatPrice( total );
                $total.html(
                    gg_woo_bt_params.text.additional_price + ' ' + total_html + ori_price_suffix ).slideDown();

                if ( isNaN( discount ) ) {
                    discount = 0;
                }

                total_ori = total_ori * ( 100 - discount ) / 100 + total;
            } else {
                $total.html( '' ).slideUp();
            }

            if ( gg_woo_bt_params.recal_price !== 'off' ) {
                if ( parseInt( $products.attr( 'data-product-id' ) ) > 0 ) {
                    $( main_price_selector ).html( GG_Woo_BT.formatPrice( total_ori ) + ori_price_suffix );
                } else {
                    $( main_price_selector ).html( $products.attr( 'data-product-price-html' ) );
                }
            }

            $( document ).trigger( 'calcPrice', [ total, total_html ] );

            $wrap.find( '.gg_woo_bt-wrap' ).attr( 'data-total', total );
        },
        saveIds: function ( $wrap ) {
            var $products = $wrap.find( '.gg_woo_bt-products' );
            var $ids = $wrap.find( '.gg_woo_bt-ids' );
            var items = [];

            $products.find( '.gg_woo_bt-product-together' ).each( function () {
                var $el = $( this );
                var _checked = $el.find( '.gg_woo_bt-checkbox' ).prop( 'checked' );
                var _id = parseInt( $el.attr( 'data-id' ) );
                var _qty = parseFloat( $el.attr( 'data-qty' ) );
                var _price = $el.attr( 'data-new-price' );

                if ( _checked && ( _qty > 0 ) && ( _id > 0 ) ) {
                    items.push( _id + '/' + _price + '/' + _qty );
                }
            } );

            if ( items.length > 0 ) {
                $ids.val( items.join( ',' ) );
            } else {
                $ids.val( '' );
            }
        },
        updateCount: function ( $wrap ) {
            var $products = $wrap.find( '.gg_woo_bt-products' );
            var $btn = $wrap.find( '.single_add_to_cart_button' );
            var qty = 0;
            var num = 1;

            $products.find( '.gg_woo_bt-product-together' ).each( function () {
                var $el = $( this );
                var _checked = $el.find( '.gg_woo_bt-checkbox' ).prop( 'checked' );
                var _id = parseInt( $el.attr( 'data-id' ) );
                var _qty = parseFloat( $el.attr( 'data-qty' ) );

                if ( _checked && ( _qty > 0 ) && ( _id > 0 ) ) {
                    qty += _qty;
                    num++;
                }
            } );

            if ( $btn.closest( 'form.cart' ).find( 'input.qty' ).length ) {
                qty += parseFloat(
                    $btn.closest( 'form.cart' ).find( 'input.qty' ).val() );
            }

            if ( num > 1 ) {
                if ( gg_woo_bt_params.counter === 'individual' ) {
                    $btn.text( gg_woo_bt_params.text.add_to_cart + ' (' + num + ')' );
                } else {
                    $btn.text( gg_woo_bt_params.text.add_to_cart + ' (' + qty + ')' );
                }
            } else {
                $btn.text( gg_woo_bt_params.text.add_to_cart );
            }

            $( document.body ).trigger( 'updateCount', [ num, qty ] );
        },
        priceHtml: function ( regular_price, sale_price ) {
            var price_html = '';

            if ( sale_price < regular_price ) {
                price_html = '<del>' + GG_Woo_BT.formatPrice( regular_price ) +
                    '</del> <ins>' +
                    GG_Woo_BT.formatPrice( sale_price ) + '</ins>';
            } else {
                price_html = GG_Woo_BT.formatPrice( regular_price );
            }

            return price_html;
        },
        formatPrice: function ( total ) {
            var total_html = '<span class="woocommerce-Price-amount amount">';
            var total_formatted = GG_Woo_BT.formatMoney( total, gg_woo_bt_params.price_decimals,
                '', gg_woo_bt_params.price_thousand_separator,
                gg_woo_bt_params.price_decimal_separator );

            switch ( gg_woo_bt_params.price_format ) {
                case '%1$s%2$s':
                    total_html += '<span class="woocommerce-Price-currencySymbol">' +
                        gg_woo_bt_params.currency_symbol + '</span>' + total_formatted;
                    break;
                case '%1$s %2$s':
                    total_html += '<span class="woocommerce-Price-currencySymbol">' +
                        gg_woo_bt_params.currency_symbol + '</span> ' + total_formatted;
                    break;
                case '%2$s%1$s':
                    total_html += total_formatted +
                        '<span class="woocommerce-Price-currencySymbol">' +
                        gg_woo_bt_params.currency_symbol + '</span>';
                    break;
                case '%2$s %1$s':
                    total_html += total_formatted +
                        ' <span class="woocommerce-Price-currencySymbol">' +
                        gg_woo_bt_params.currency_symbol + '</span>';
                    break;
                default:
                    total_html += '<span class="woocommerce-Price-currencySymbol">' +
                        gg_woo_bt_params.currency_symbol + '</span> ' + total_formatted;
            }

            total_html += '</span>';

            return total_html;
        },
        formatMoney: function ( number, places, symbol, thousand, decimal ) {
            number = number || 0;
            places = !isNaN( places = Math.abs( places ) ) ? places : 2;
            symbol = symbol !== undefined ? symbol : '$';
            thousand = thousand !== undefined ? thousand : ',';
            decimal = decimal !== undefined ? decimal : '.';

            var negative = number < 0 ? '-' : '',
                i = parseInt( number = Math.abs( +number || 0 ).toFixed( places ), 10 ) + '',
                j = 0;

            if ( i.length > 3 ) {
                j = i.length % 3;
            }

            return symbol + negative + ( j ? i.substr( 0, j ) + thousand : '' ) +
                i.substr( j ).replace( /(\d{3})(?=\d)/g, '$1' + thousand ) + (
                    places ? decimal + Math.abs( number - i ).toFixed( places ).slice( 2 ) : ''
                );
        }
    };

    $( GG_Woo_BT.init );
} )( jQuery );
