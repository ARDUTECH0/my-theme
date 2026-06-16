/**
 * ECM — 3D Slider
 * سلايدر fade بسيط (RTL-safe) — أسهم · نقط · تشغيل تلقائي · إيقاف عند المرور.
 */
(function () {
    'use strict';

    // إعادة تأطير موديل 3D واحد (يوسّطه ويفتّحه صح)
    function reframeModel( mv ) {
        if ( ! mv ) { return; }
        try { if ( typeof mv.updateFraming === 'function' ) { mv.updateFraming(); } } catch ( e ) {}
    }

    // إعادة تأطير كل الموديلات في الصفحة + ربط حدث التحميل لكل واحد
    function reframeAll() {
        var mvs = document.querySelectorAll( 'model-viewer' );
        Array.prototype.forEach.call( mvs, function ( mv ) {
            reframeModel( mv );
            if ( ! mv.dataset.ecmFrameBound ) {
                mv.dataset.ecmFrameBound = '1';
                mv.addEventListener( 'load', function () { reframeModel( mv ); } );
            }
        } );
    }

    function initSlider( root ) {
        if ( ! root || root.dataset.ecmSliderInit ) { return; }
        root.dataset.ecmSliderInit = '1';

        var slides = root.querySelectorAll( '.ecm-slider-3d__slide' );
        var dots   = root.querySelectorAll( '.ecm-slider-3d__dot' );
        var prev   = root.querySelector( '.ecm-slider-3d__prev' );
        var next   = root.querySelector( '.ecm-slider-3d__next' );
        if ( slides.length < 1 ) { return; }

        var current = 0;
        var timer   = null;
        var delay   = parseInt( root.getAttribute( 'data-autoplay' ), 10 ) || 0;

        function go( i ) {
            i = ( i + slides.length ) % slides.length;
            if ( slides[ current ] ) { slides[ current ].classList.remove( 'is-active' ); }
            if ( dots[ current ] ) { dots[ current ].classList.remove( 'is-active' ); }
            current = i;
            if ( slides[ current ] ) { slides[ current ].classList.add( 'is-active' ); }
            if ( dots[ current ] ) { dots[ current ].classList.add( 'is-active' ); }
            // إعادة تأطير موديل الشريحة النشطة عشان يبقى متسنطر صح
            if ( window.requestAnimationFrame && slides[ current ] ) {
                requestAnimationFrame( function () {
                    reframeModel( slides[ current ].querySelector( 'model-viewer' ) );
                } );
            }
        }

        function start() {
            if ( delay > 0 && slides.length > 1 ) {
                timer = setInterval( function () { go( current + 1 ); }, delay );
            }
        }
        function stop() { if ( timer ) { clearInterval( timer ); timer = null; } }
        function restart() { stop(); start(); }

        if ( next ) { next.addEventListener( 'click', function () { go( current + 1 ); restart(); } ); }
        if ( prev ) { prev.addEventListener( 'click', function () { go( current - 1 ); restart(); } ); }
        Array.prototype.forEach.call( dots, function ( d ) {
            d.addEventListener( 'click', function () { go( parseInt( d.getAttribute( 'data-i' ), 10 ) || 0 ); restart(); } );
        } );

        root.addEventListener( 'mouseenter', stop );
        root.addEventListener( 'mouseleave', restart );

        start();
    }

    function initAll( ctx ) {
        ( ctx || document ).querySelectorAll( '.ecm-slider-3d' ).forEach( initSlider );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', function () { initAll(); } );
    } else {
        initAll();
    }

    // إعادة تأطير كل موديلات 3D (توسيط صحيح حتى مع تعدد الموديلات في الصفحة)
    function reframeBurst() {
        reframeAll();
        setTimeout( reframeAll, 600 );
        setTimeout( reframeAll, 1600 );
    }
    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', reframeBurst );
    } else {
        reframeBurst();
    }
    window.addEventListener( 'load', reframeBurst );

    // التشغيل داخل محرّر Elementor (لما الودجِت يتضاف/يتعدّل)
    if ( window.jQuery ) {
        jQuery( window ).on( 'elementor/frontend/init', function () {
            if ( window.elementorFrontend && elementorFrontend.hooks ) {
                elementorFrontend.hooks.addAction( 'frontend/element_ready/ecm_slider_3d.default', function ( $scope ) {
                    var root = $scope[ 0 ].querySelector( '.ecm-slider-3d' );
                    if ( root ) { root.dataset.ecmSliderInit = ''; initSlider( root ); }
                } );
            }
        } );
    }
})();
