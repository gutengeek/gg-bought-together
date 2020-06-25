( function ( $ ) {
    var gg_woo_bt_timeout = null;

    var GG_Woo_BT_Admin = {
        init: function () {
            GG_Woo_BT_Admin.sortableInit();
            GG_Woo_BT_Admin.searchProducts();
            GG_Woo_BT_Admin.addResults();
            GG_Woo_BT_Admin.dependencies();
            GG_Woo_BT_Admin.removeProduct();
            GG_Woo_BT_Admin.refreshIds();
        },
        sortableInit: function () {
            var $ul = $( '#gg_woo_bt_selected ul' );
            if ( $ul.length ) {
                $ul.sortable( {
                    cursor: 'move',
                    stop: function ( event, ui ) {
                        GG_Woo_BT_Admin.getIds();
                    }
                } ).disableSelection();
            }
        },
        searchProducts: function () {
            $( '#gg_woo_bt_search' ).keyup( function () {
                var keyword = $( '#gg_woo_bt_search' ).val();
                var $loading = $( '#gg_woo_bt_loading' ),
                    $results = $( '#gg_woo_bt_results' );
                if ( keyword !== '' ) {
                    $loading.show();

                    if ( gg_woo_bt_timeout != null ) {
                        clearTimeout( gg_woo_bt_timeout );
                    }

                    gg_woo_bt_timeout = setTimeout( function () {
                        gg_woo_bt_timeout = null;

                        var data = {
                            action: 'gg_woo_bt_get_search_results',
                            keyword: keyword,
                            id: $( '#gg_woo_bt_id' ).val(),
                            ids: $( '#gg_woo_bt_ids' ).val(),
                        };

                        $.ajax( {
                            url: ajaxurl,
                            method: 'POST',
                            data: data
                        } ).done( function ( res ) {
                            $results.show();
                            $results.html( res );
                            $loading.hide();
                            GG_Woo_BT_Admin.addResults();
                        } );
                    }, 300 );

                    return false;
                } else {
                    $results.hide();
                }
            } );
        },
        addResults: function () {
            $( '#gg_woo_bt_results li' ).on( 'click', function () {
                var $el = $( this );
                var id = $( this ).data( 'id' );
                $.ajax( {
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'gg_woo_bt_add_result_product_meta',
                        id: id,
                    }
                } ).done( function (res) {
                    if ( res ) {
                        $el.remove();
                        $( '#gg_woo_bt_selected ul' ).append( res );
                        $( '#gg_woo_bt_results' ).hide();
                        $( '#gg_woo_bt_results li' ).remove();
                        $( '#gg_woo_bt_search' ).val( '' );

                        GG_Woo_BT_Admin.reInitWCToolTip();
                        GG_Woo_BT_Admin.sortableInit();
                        GG_Woo_BT_Admin.removeProduct();
                        GG_Woo_BT_Admin.getIds();
                    }
                } );
            } );
        },
        reInitWCToolTip: function () {
            $( '.tips, .help_tip, .woocommerce-help-tip' ).tipTip( {
                'attribute': 'data-tip',
                'fadeIn': 50,
                'fadeOut': 50,
                'delay': 200
            } );
        },
        getIds: function () {
            var ids = new Array();
            var $ids = $( '#gg_woo_bt_ids' );

            $( '#gg_woo_bt_selected li' ).each( function () {
                if ( !$( this ).hasClass( 'gg_woo_bt_default' ) ) {
                    ids.push( $( this ).attr( 'data-id' ) + '/' +
                        $( this ).find( 'input.gg_woo_bt_price' ).val() + '/' +
                        $( this ).find( 'input.gg_woo_bt_qty' ).val() );
                }
            } );

            if ( ids.length ) {
                $ids.val( ids.join( ',' ) );
            } else {
                $ids.val( '' );
            }
        },
        refreshIds: function () {
            $( '#gg_woo_bt_selected' ).on( 'keyup change click', 'input', function () {
                GG_Woo_BT_Admin.getIds();

                return false;
            } );
        },
        dependencies: function () {
            $( '#gg_woo_bt_custom_qty' ).on( 'click', function () {
                if ( $( this ).is( ':checked' ) ) {
                    $( '.gg_woo_bt_tr_show_if_custom_qty' ).show();
                    $( '.gg_woo_bt_tr_hide_if_custom_qty' ).hide();
                } else {
                    $( '.gg_woo_bt_tr_show_if_custom_qty' ).hide();
                    $( '.gg_woo_bt_tr_hide_if_custom_qty' ).show();
                }
            } );
        },
        removeProduct: function () {
            $( '#gg_woo_bt_selected span.remove' ).on( 'click', function () {
                $( this ).closest( 'li' ).remove();
                GG_Woo_BT_Admin.getIds();

                return false;
            } );
        },

    };

    $( GG_Woo_BT_Admin.init );

} )( jQuery );
