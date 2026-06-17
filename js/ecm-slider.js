/**
 * ECM — 3D Slider
 * سلايدر fade بسيط (RTL-safe) — أسهم · نقط · تشغيل تلقائي · إيقاف عند المرور.
 */
(function () {
    'use strict';

    // إعادة تأطير موديل 3D واحد (يوسّطه في النص بالظبط — حتى لو نفس الملف متكرّر)
    function reframeModel( mv ) {
        if ( ! mv ) { return; }
        // وجّه الكاميرا لمركز صندوق الموديل الحقيقي (أدقّ من "auto")
        try {
            if ( typeof mv.getBoundingBoxCenter === 'function' ) {
                var c = mv.getBoundingBoxCenter();
                if ( c && isFinite( c.x ) ) {
                    mv.cameraTarget = c.x + 'm ' + c.y + 'm ' + c.z + 'm';
                } else {
                    mv.cameraTarget = 'auto auto auto';
                }
            } else {
                mv.cameraTarget = 'auto auto auto';
            }
        } catch ( e ) { try { mv.cameraTarget = 'auto auto auto'; } catch ( e2 ) {} }
        try { if ( typeof mv.updateFraming === 'function' ) { mv.updateFraming(); } } catch ( e ) {}
        try { if ( typeof mv.jumpCameraToGoal === 'function' ) { mv.jumpCameraToGoal(); } } catch ( e ) {}
    }

    // إعادة تأطير كل الموديلات في الصفحة + ربط حدث التحميل لكل واحد
    // تشغيل كل الأنيميشنز في الملف بالتتابع (model-viewer بيشغّل واحد في المرة)
    function setupAnims( mv ) {
        if ( ! mv.hasAttribute( 'autoplay' ) || mv.dataset.ecmAnimBound ) { return; }
        mv.dataset.ecmAnimBound = '1';

        function setup() {
            var anims = mv.availableAnimations || [];
            if ( anims.length < 2 ) { return; } // أنيميشن واحد → autoplay بيكرّره عادي
            mv.removeAttribute( 'autoplay' );
            var i = 0;
            function playClip() {
                mv.animationName = anims[ i ];
                try { mv.play( { repetitions: 1 } ); } catch ( e ) { try { mv.play(); } catch ( e2 ) {} }
            }
            mv.addEventListener( 'finished', function () {
                i = ( i + 1 ) % anims.length;
                playClip();
            } );
            playClip();
        }

        if ( mv.loaded ) { setup(); }
        else { mv.addEventListener( 'load', setup ); }
    }

    function reframeAll() {
        var mvs = document.querySelectorAll( 'model-viewer' );
        Array.prototype.forEach.call( mvs, function ( mv ) {
            reframeModel( mv );
            setupAnims( mv );
            if ( ! mv.dataset.ecmFrameBound ) {
                mv.dataset.ecmFrameBound = '1';
                mv.addEventListener( 'load', function () { reframeModel( mv ); } );
            }
        } );
    }

    // الموديل في الشريحة النشطة
    function activeModel( root ) {
        var slide = root.querySelector( '.ecm-slider-3d__slide.is-active' );
        return slide ? slide.querySelector( 'model-viewer' ) : null;
    }

    // زوم للموديل النشط — dir سالب = تقريب، موجب = تبعيد
    function zoomModel( mv, dir ) {
        if ( ! mv ) { return; }
        var fov = 30;
        try { fov = parseFloat( mv.getFieldOfView() ); } catch ( e ) {}
        if ( ! fov || isNaN( fov ) ) { fov = 30; }
        fov = Math.max( 8, Math.min( 55, fov + dir * 5 ) );
        try { mv.fieldOfView = fov + 'deg'; } catch ( e ) {}
    }

    function initSlider( root ) {
        if ( ! root || root.dataset.ecmSliderInit ) { return; }
        root.dataset.ecmSliderInit = '1';

        var slides = root.querySelectorAll( '.ecm-slider-3d__slide' );
        var dots   = root.querySelectorAll( '.ecm-slider-3d__dot' );
        var prev   = root.querySelector( '.ecm-slider-3d__prev' );
        var next   = root.querySelector( '.ecm-slider-3d__next' );
        var zin    = root.querySelector( '.ecm-slider-3d__zoom-in' );
        var zout   = root.querySelector( '.ecm-slider-3d__zoom-out' );
        var zreset = root.querySelector( '.ecm-slider-3d__zoom-reset' );
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
        if ( zin ) { zin.addEventListener( 'click', function () { zoomModel( activeModel( root ), -1 ); } ); }
        if ( zout ) { zout.addEventListener( 'click', function () { zoomModel( activeModel( root ), 1 ); } ); }
        if ( zreset ) { zreset.addEventListener( 'click', function () { reframeModel( activeModel( root ) ); } ); }
        Array.prototype.forEach.call( dots, function ( d ) {
            d.addEventListener( 'click', function () { go( parseInt( d.getAttribute( 'data-i' ), 10 ) || 0 ); restart(); } );
        } );

        // إيقاف الأوتوبلاي عند المرور أو التفاعل مع الموديل 3D
        var isOver      = false; // الماوس فوق السلايدر
        var interacting = false; // بيسحب/بيلفّ الموديل دلوقتي

        root.addEventListener( 'mouseenter', function () { isOver = true; stop(); } );
        root.addEventListener( 'mouseleave', function () { isOver = false; if ( ! interacting ) { restart(); } } );

        // أثناء السحب (ماوس أو لمس) — model-viewer بيمسك المؤشر فبيطلّع mouseleave بالغلط؛
        // نقفل التقدّم طول ما الإيد ماسكة، ومايرجعش غير بعد رفع الإيد + خروج الماوس.
        root.addEventListener( 'pointerdown', function () { interacting = true; stop(); } );
        document.addEventListener( 'pointerup', function () {
            if ( ! interacting ) { return; }
            interacting = false;
            if ( ! isOver ) { restart(); }
        } );

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

    // إعادة تأطير عند تغيير حجم الشاشة/دوران الجهاز (عشان يفضل متأقلم ومتسنتر)
    var resizeT = null;
    window.addEventListener( 'resize', function () {
        if ( resizeT ) { clearTimeout( resizeT ); }
        resizeT = setTimeout( reframeAll, 250 );
    } );
    window.addEventListener( 'orientationchange', function () { setTimeout( reframeAll, 350 ); } );

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

    /* ── لايت‌بوكس بسيط لشبكة التحكّم 3D (تكبير الصور) ── */
    var lbEl = null;
    function ensureLightbox() {
        if ( lbEl ) { return lbEl; }
        lbEl = document.createElement( 'div' );
        lbEl.className = 'ecm-lb';
        lbEl.innerHTML = '<button type="button" class="ecm-lb__close" aria-label="إغلاق">&times;</button><img class="ecm-lb__img" alt="">';
        document.body.appendChild( lbEl );
        lbEl.addEventListener( 'click', function ( e ) {
            if ( e.target === lbEl || e.target.classList.contains( 'ecm-lb__close' ) ) { closeLightbox(); }
        } );
        return lbEl;
    }
    function openLightbox( src ) {
        if ( ! src ) { return; }
        var el = ensureLightbox();
        el.querySelector( '.ecm-lb__img' ).src = src;
        el.classList.add( 'is-open' );
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        if ( lbEl ) { lbEl.classList.remove( 'is-open' ); }
        document.body.style.overflow = '';
    }
    document.addEventListener( 'click', function ( e ) {
        var t = e.target.closest ? e.target.closest( '[data-ecm-lb]' ) : null;
        if ( t ) { e.preventDefault(); openLightbox( t.getAttribute( 'data-ecm-lb' ) ); }
    } );
    document.addEventListener( 'keydown', function ( e ) {
        if ( 'Escape' === e.key ) { closeLightbox(); return; }
        if ( ( 'Enter' === e.key || ' ' === e.key ) && document.activeElement ) {
            var t = document.activeElement.closest ? document.activeElement.closest( '[data-ecm-lb]' ) : null;
            if ( t ) { e.preventDefault(); openLightbox( t.getAttribute( 'data-ecm-lb' ) ); }
        }
    } );
})();
