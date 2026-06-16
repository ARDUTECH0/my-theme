/**
 * ECM Theme JavaScript
 * E.Camera.Man — Navigation, Scroll Effects & Micro-interactions
 * @version 2.0.0
 */
( function () {
    'use strict';

    /* ── DOM REFS ── */
    const header   = document.getElementById( 'ecm-header' );
    const hamburger = document.getElementById( 'ecm-hamburger' );
    const drawer    = document.getElementById( 'ecm-mobile-drawer' );
    const overlay   = document.getElementById( 'ecm-nav-overlay' );

    /* ════════════════════════════════
       MOBILE MENU
       ════════════════════════════════ */
    function openMenu() {
        hamburger.setAttribute( 'aria-expanded', 'true' );
        drawer.setAttribute( 'aria-hidden', 'false' );
        overlay.setAttribute( 'aria-hidden', 'false' );
        hamburger.classList.add( 'active' );
        drawer.classList.add( 'active' );
        overlay.classList.add( 'active' );
        document.body.classList.add( 'no-scroll' );
    }

    function closeMenu() {
        hamburger.setAttribute( 'aria-expanded', 'false' );
        drawer.setAttribute( 'aria-hidden', 'true' );
        overlay.setAttribute( 'aria-hidden', 'true' );
        hamburger.classList.remove( 'active' );
        drawer.classList.remove( 'active' );
        overlay.classList.remove( 'active' );
        document.body.classList.remove( 'no-scroll' );
    }

    if ( hamburger && drawer ) {
        hamburger.addEventListener( 'click', function () {
            const isOpen = hamburger.getAttribute( 'aria-expanded' ) === 'true';
            isOpen ? closeMenu() : openMenu();
        } );

        // Close when a link inside drawer is clicked
        drawer.querySelectorAll( 'a' ).forEach( function ( link ) {
            link.addEventListener( 'click', closeMenu );
        } );
    }

    // Close on overlay click
    if ( overlay ) {
        overlay.addEventListener( 'click', closeMenu );
    }

    // Close on Escape key
    document.addEventListener( 'keydown', function ( e ) {
        if ( e.key === 'Escape' ) closeMenu();
    } );


    /* ════════════════════════════════
       NAVBAR SCROLL SHRINK
       ════════════════════════════════ */
    if ( header ) {
        let lastScroll = 0;
        let ticking    = false;

        function handleScroll() {
            const currentScroll = window.scrollY;
            if ( currentScroll > 60 ) {
                header.classList.add( 'ecm-nav--scrolled' );
            } else {
                header.classList.remove( 'ecm-nav--scrolled' );
            }
            lastScroll = currentScroll;
            ticking    = false;
        }

        window.addEventListener( 'scroll', function () {
            if ( ! ticking ) {
                window.requestAnimationFrame( handleScroll );
                ticking = true;
            }
        }, { passive: true } );
    }


    /* ════════════════════════════════
       FADE-IN ON SCROLL (Intersection Observer)
       ════════════════════════════════ */
    const animateEls = document.querySelectorAll(
        '.ecm-ctrl-card, .ecm-feat-card, .ecm-stat-box, .ecm-spec-row'
    );

    if ( animateEls.length && 'IntersectionObserver' in window ) {
        // Set initial hidden state
        animateEls.forEach( function ( el ) {
            el.style.opacity    = '0';
            el.style.transform  = 'translateY(20px)';
            el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        } );

        const observer = new IntersectionObserver(
            function ( entries ) {
                entries.forEach( function ( entry, i ) {
                    if ( entry.isIntersecting ) {
                        // Stagger delay per element
                        const delay = ( i % 4 ) * 80;
                        setTimeout( function () {
                            entry.target.style.opacity   = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, delay );
                        observer.unobserve( entry.target );
                    }
                } );
            },
            { threshold: 0.12 }
        );

        animateEls.forEach( function ( el ) {
            observer.observe( el );
        } );
    }


    /* ════════════════════════════════
       SMOOTH ANCHOR SCROLL
       ════════════════════════════════ */
    document.querySelectorAll( 'a[href^="#"]' ).forEach( function ( anchor ) {
        anchor.addEventListener( 'click', function ( e ) {
            const targetId = this.getAttribute( 'href' ).slice( 1 );
            if ( ! targetId ) return;

            const target = document.getElementById( targetId );
            if ( target ) {
                e.preventDefault();
                const navH   = header ? header.offsetHeight : 68;
                const topPos = target.getBoundingClientRect().top + window.scrollY - navH - 16;
                window.scrollTo( { top: topPos, behavior: 'smooth' } );
            }
        } );
    } );


    /* ════════════════════════════════
       HERO SLIDER
       ════════════════════════════════ */
    const slides = document.querySelectorAll( '.ecm-slide' );
    const dots   = document.querySelectorAll( '.ecm-dot' );
    const prev   = document.getElementById( 'ecmPrev' );
    const next   = document.getElementById( 'ecmNext' );

    if ( slides.length > 1 ) {
        let current   = 1;
        let autoTimer = null;
        const total   = slides.length;

        function goTo( n ) {
            // Deactivate all
            slides.forEach( s => s.classList.remove( 'ecm-slide-active' ) );
            dots.forEach( d => d.classList.remove( 'ecm-dot-active' ) );

            // Activate target
            current = n;
            if ( current > total ) current = 1;
            if ( current < 1 )     current = total;

            const activeSlide = document.querySelector( '[data-slide="' + current + '"]' );
            const activeDot   = document.querySelector( '[data-goto="' + current + '"]' );
            if ( activeSlide ) activeSlide.classList.add( 'ecm-slide-active' );
            if ( activeDot )   activeDot.classList.add( 'ecm-dot-active' );

            // Reset progress bar animation
            const slider = document.querySelector( '.ecm-slider' );
            if ( slider ) {
                slider.style.animation = 'none';
                slider.offsetHeight; // force reflow
                slider.style.animation = '';
            }
        }

        function nextSlide() { goTo( current + 1 ); }
        function prevSlide() { goTo( current - 1 ); }

        function startAuto() {
            stopAuto();
            autoTimer = setInterval( nextSlide, 6000 );
        }
        function stopAuto() {
            if ( autoTimer ) clearInterval( autoTimer );
        }

        // Dots click
        dots.forEach( dot => {
            dot.addEventListener( 'click', () => {
                goTo( parseInt( dot.dataset.goto ) );
                startAuto(); // restart timer
            } );
        } );

        // Arrows
        if ( prev ) prev.addEventListener( 'click', () => { prevSlide(); startAuto(); } );
        if ( next ) next.addEventListener( 'click', () => { nextSlide(); startAuto(); } );

        // Keyboard
        document.addEventListener( 'keydown', ( e ) => {
            if ( e.key === 'ArrowLeft' )  { nextSlide(); startAuto(); }
            if ( e.key === 'ArrowRight' ) { prevSlide(); startAuto(); }
        } );

        // Pause on hover
        const sliderEl = document.querySelector( '.ecm-slider' );
        if ( sliderEl ) {
            sliderEl.addEventListener( 'mouseenter', stopAuto );
            sliderEl.addEventListener( 'mouseleave', startAuto );
        }

        // Touch swipe
        let touchStartX = 0;
        if ( sliderEl ) {
            sliderEl.addEventListener( 'touchstart', ( e ) => {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true } );

            sliderEl.addEventListener( 'touchend', ( e ) => {
                const diff = touchStartX - e.changedTouches[0].screenX;
                if ( Math.abs( diff ) > 50 ) {
                    if ( diff > 0 ) nextSlide(); // swipe left = next (RTL)
                    else prevSlide();
                    startAuto();
                }
            }, { passive: true } );
        }

        // Start auto-play
        startAuto();
    }


    /* ════════════════════════════════
       VIDEO CLICK-TO-PLAY
       ════════════════════════════════ */
    document.querySelectorAll( '.ecm-video-thumb' ).forEach( thumb => {
        thumb.addEventListener( 'click', function () {
            const vid = this.dataset.vid;
            if ( ! vid ) return;
            const iframe = document.createElement( 'iframe' );
            iframe.src = 'https://www.youtube.com/embed/' + vid + '?autoplay=1&rel=0&modestbranding=1';
            iframe.setAttribute( 'allow', 'autoplay; encrypted-media' );
            iframe.setAttribute( 'allowfullscreen', '' );
            this.innerHTML = '';
            this.appendChild( iframe );
        } );
    } );


    /* ════════════════════════════════
       DARK / LIGHT TOGGLE
       ════════════════════════════════ */
    // الوضع الغامق ثابت — زرار التبديل اتشال
    document.body.classList.remove( 'ecm-light-theme' );
    document.body.classList.add( 'ecm-dark-theme' );
    try { localStorage.removeItem( 'ecm-theme' ); } catch ( e ) {}


    /* ════════════════════════════════
       FAQ ACCORDION
       ════════════════════════════════ */
    document.querySelectorAll( '.ecm-sw-faq-q' ).forEach( btn => {
        btn.addEventListener( 'click', function () {
            const item = this.closest( '.ecm-sw-faq-item' );
            const isOpen = item.classList.contains( 'ecm-faq-open' );

            // Close all
            document.querySelectorAll( '.ecm-sw-faq-item' ).forEach( i => i.classList.remove( 'ecm-faq-open' ) );
            document.querySelectorAll( '.ecm-sw-faq-q' ).forEach( b => b.setAttribute( 'aria-expanded', 'false' ) );

            // Open clicked (if was closed)
            if ( ! isOpen ) {
                item.classList.add( 'ecm-faq-open' );
                this.setAttribute( 'aria-expanded', 'true' );
            }
        } );
    } );

} )();
