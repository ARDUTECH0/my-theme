/**
 * Theme Customizer Live Preview
 * E.Camera.Man — Real-time customization updates
 * @version 2.0.0
 */
( function ( $ ) {
    'use strict';

    /* ── Helper: hex → rgb object ── */
    function hexToRgb( hex ) {
        hex = hex.replace( /^#?([a-f\d])([a-f\d])([a-f\d])$/i, function ( m, r, g, b ) {
            return r + r + g + g + b + b;
        } );
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec( hex );
        return result
            ? { r: parseInt( result[1], 16 ), g: parseInt( result[2], 16 ), b: parseInt( result[3], 16 ) }
            : null;
    }

    /* ── Helper: rgb → hex ── */
    function rgbToHex( r, g, b ) {
        return '#' + ( ( 1 << 24 ) + ( r << 16 ) + ( g << 8 ) + b ).toString( 16 ).slice( 1 );
    }

    /* ── Helper: set CSS custom property ── */
    function setCssVar( name, value ) {
        document.documentElement.style.setProperty( name, value );
    }

    /* ── PRIMARY ACCENT COLOR ── */
    wp.customize( 'ecm_green_color', function ( value ) {
        value.bind( function ( newval ) {
            setCssVar( '--ecm-green', newval );

            const rgb = hexToRgb( newval );
            if ( rgb ) {
                const pct   = 0.28;
                const dR    = Math.max( 0, rgb.r - Math.round( 255 * pct ) );
                const dG    = Math.max( 0, rgb.g - Math.round( 255 * pct ) );
                const dB    = Math.max( 0, rgb.b - Math.round( 255 * pct ) );
                setCssVar( '--ecm-green-dim',    rgbToHex( dR, dG, dB ) );
                setCssVar( '--ecm-green-glow',   `rgba(${ rgb.r },${ rgb.g },${ rgb.b },0.20)` );
                setCssVar( '--ecm-green-subtle', `rgba(${ rgb.r },${ rgb.g },${ rgb.b },0.07)` );
            }
        } );
    } );

    /* ── BACKGROUND COLORS ── */
    wp.customize( 'ecm_bg_deep', function ( value ) {
        value.bind( function ( newval ) { setCssVar( '--ecm-bg-deep', newval ); } );
    } );

    wp.customize( 'ecm_bg_card', function ( value ) {
        value.bind( function ( newval ) { setCssVar( '--ecm-bg-card', newval ); } );
    } );

    /* ── TEXT BINDINGS ── */
    wp.customize( 'ecm_nav_cta_text', function ( value ) {
        value.bind( function ( newval ) {
            document.querySelectorAll( '.ecm-nav-cta, .ecm-mobile-cta .ecm-btn-primary' )
                .forEach( function ( el ) { el.textContent = newval; } );
        } );
    } );

    wp.customize( 'ecm_footer_copy', function ( value ) {
        value.bind( function ( newval ) {
            const el = document.querySelector( '.ecm-footer-copy' );
            if ( el ) el.innerHTML = newval;
        } );
    } );

    wp.customize( 'ecm_footer_tagline', function ( value ) {
        value.bind( function ( newval ) {
            const el = document.querySelector( '.ecm-footer-logo small' );
            if ( el ) el.textContent = newval;
        } );
    } );

} )( jQuery );
