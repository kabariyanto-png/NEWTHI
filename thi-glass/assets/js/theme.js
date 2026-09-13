/*!
 * THI Glass — theme interactions
 * Vanilla JS, tanpa dependensi. Semua efek gerak menghormati prefers-reduced-motion.
 */
(function () {
  'use strict';

  var root = document.documentElement;
  root.classList.add('js-on'); // pengaman bila skrip head tidak sempat jalan
  root.classList.remove('js-off'); // kompatibilitas mundur

  var reduceQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
  var pointerFine = window.matchMedia('(hover: hover) and (pointer: fine)');
  var reduce = reduceQuery.matches;
  reduceQuery.addEventListener('change', function (e) {
    reduce = e.matches;
    if (reduce) { stopAllMotion(); }
  });

  var motionCleanups = [];
  function stopAllMotion() {
    motionCleanups.forEach(function (fn) { try { fn(); } catch (e) {} });
    motionCleanups = [];
  }
  function $(sel, ctx) { return (ctx || document).querySelector(sel); }
  function $$(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

  /* ------------------------------------------------------------------ *
   * 1. Dark mode
   * ------------------------------------------------------------------ */
  function initTheme() {
    var STORE = 'thiglass-theme';
    var btns = $$('.theme-toggle');
    var sysDark = window.matchMedia('(prefers-color-scheme: dark)');

    function apply(mode, persist) {
      var dark = mode === 'dark';
      root.setAttribute('data-theme', dark ? 'dark' : 'light');
      btns.forEach(function (b) {
        b.setAttribute('aria-pressed', String(dark));
        b.setAttribute('aria-label', dark
          ? (b.dataset.labelLight || 'Aktifkan mode terang')
          : (b.dataset.labelDark || 'Aktifkan mode gelap'));
      });
      if (persist) { try { localStorage.setItem(STORE, mode); } catch (e) {} }
    }

    var stored = null;
    try { stored = localStorage.getItem(STORE); } catch (e) {}
    apply(stored || (sysDark.matches ? 'dark' : 'light'), false);

    sysDark.addEventListener('change', function (e) {
      var s = null;
      try { s = localStorage.getItem(STORE); } catch (err) {}
      if (!s) { apply(e.matches ? 'dark' : 'light', false); }
    });

    btns.forEach(function (b) {
      b.addEventListener('click', function () {
        apply(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark', true);
      });
    });
  }

  /* ------------------------------------------------------------------ *
   * 2. Header: sticky glass + auto-hide + scroll progress
   * ------------------------------------------------------------------ */
  function initHeader() {
    var header = $('.site-header');
    var bar = $('.scroll-progress');
    if (!header && !bar) { return; }

    var lastY = window.scrollY;
    var ticking = false;

    function update() {
      var y = window.scrollY;

      if (header) {
        header.classList.toggle('is-stuck', y > 24);
        // Auto-hide hanya setelah lewat 2x tinggi header, dan tidak saat drawer terbuka
        var drawerOpen = document.body.classList.contains('nav-open');
        if (!drawerOpen && y > 220) {
          header.classList.toggle('is-hidden', y > lastY + 6);
        } else {
          header.classList.remove('is-hidden');
        }
      }

      if (bar) {
        var h = document.documentElement.scrollHeight - window.innerHeight;
        bar.style.transform = 'scaleX(' + (h > 0 ? Math.min(y / h, 1) : 0) + ')';
      }

      lastY = y;
      ticking = false;
    }

    window.addEventListener('scroll', function () {
      if (!ticking) { ticking = true; requestAnimationFrame(update); }
    }, { passive: true });
    update();
  }

  /* ------------------------------------------------------------------ *
   * 3. Drawer (mobile nav) — focus trap, ESC, scrim, submenu
   * ------------------------------------------------------------------ */
  function initDrawer() {
    var toggle = $('.nav-toggle');
    var drawer = $('.drawer');
    var scrim = $('.nav-scrim');
    if (!toggle || !drawer) { return; }

    var closeBtn = $('.drawer-close', drawer);
    var lastFocus = null;
    var FOCUSABLE = 'a[href],button:not([disabled]),input:not([disabled]),select,textarea,[tabindex]:not([tabindex="-1"])';

    function open() {
      lastFocus = document.activeElement;
      drawer.classList.add('is-open');
      if (scrim) { scrim.classList.add('is-open'); }
      document.body.classList.add('no-scroll', 'nav-open');
      toggle.setAttribute('aria-expanded', 'true');
      drawer.removeAttribute('aria-hidden');
      var first = $(FOCUSABLE, drawer);
      if (first) { first.focus(); }
      document.addEventListener('keydown', onKey);
    }

    function close() {
      drawer.classList.remove('is-open');
      if (scrim) { scrim.classList.remove('is-open'); }
      document.body.classList.remove('no-scroll', 'nav-open');
      toggle.setAttribute('aria-expanded', 'false');
      drawer.setAttribute('aria-hidden', 'true');
      document.removeEventListener('keydown', onKey);
      if (lastFocus && lastFocus.focus) { lastFocus.focus(); }
    }

    function onKey(e) {
      if (e.key === 'Escape') { e.preventDefault(); close(); return; }
      if (e.key !== 'Tab') { return; }
      var items = $$(FOCUSABLE, drawer).filter(function (el) { return el.offsetParent !== null; });
      if (!items.length) { return; }
      var first = items[0], last = items[items.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }

    toggle.addEventListener('click', function () {
      drawer.classList.contains('is-open') ? close() : open();
    });
    if (closeBtn) { closeBtn.addEventListener('click', close); }
    if (scrim) { scrim.addEventListener('click', close); }
    $$('a', drawer).forEach(function (a) {
      a.addEventListener('click', function () {
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('#') !== 0) { close(); }
      });
    });
    window.addEventListener('resize', function () {
      if (window.innerWidth >= 1024 && drawer.classList.contains('is-open')) { close(); }
    });
    drawer.setAttribute('aria-hidden', 'true');
  }

  /* ------------------------------------------------------------------ *
   * 4. Submenu: buka via keyboard/touch pada desktop nav
   * ------------------------------------------------------------------ */
  function initSubmenus() {
    $$('.primary-nav .menu-item-has-children > a').forEach(function (link) {
      var li = link.parentNode;
      link.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          li.classList.add('is-open');
          var first = $('ul a', li);
          if (first) { first.focus(); }
        }
      });
      li.addEventListener('focusout', function () {
        setTimeout(function () {
          if (!li.contains(document.activeElement)) { li.classList.remove('is-open'); }
        }, 0);
      });
    });

    // Drawer: sub-menu toggle button
    $$('.drawer .menu-item-has-children').forEach(function (li) {
      var link = li.querySelector(':scope > a');
      var sub = li.querySelector(':scope > ul');
      if (!link || !sub) { return; }
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'icon-btn submenu-toggle';
      btn.setAttribute('aria-expanded', 'false');
      btn.setAttribute('aria-label', (link.textContent || '').trim() + ' — buka sub-menu');
      btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
      link.insertAdjacentElement('afterend', btn);
      sub.hidden = true;
      btn.addEventListener('click', function () {
        var open = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', String(!open));
        sub.hidden = open;
        btn.style.transform = open ? '' : 'rotate(180deg)';
      });
    });
  }

  /* ------------------------------------------------------------------ *
   * 5. Parallax — rAF, transform saja, layer dekoratif saja
   * ------------------------------------------------------------------ */
  function initParallax() {
    if (reduce) { return; }
    var layers = $$('[data-parallax]');
    if (!layers.length) { return; }

    var items = layers.map(function (el) {
      return {
        el: el,
        speed: parseFloat(el.dataset.parallax) || 0.12,
        rect: null,
        visible: false
      };
    });

    function measure() {
      items.forEach(function (it) {
        var r = it.el.getBoundingClientRect();
        it.rect = { top: r.top + window.scrollY, height: r.height };
      });
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        var it = items.filter(function (i) { return i.el === entry.target; })[0];
        if (!it) { return; }
        it.visible = entry.isIntersecting;
        it.el.style.willChange = entry.isIntersecting ? 'transform' : '';
      });
    }, { rootMargin: '20% 0px 20% 0px' });
    items.forEach(function (it) { io.observe(it.el); });

    var ticking = false;
    function render() {
      var vh = window.innerHeight;
      var y = window.scrollY;
      items.forEach(function (it) {
        if (!it.visible || !it.rect) { return; }
        // -1..1 relatif terhadap pusat viewport
        var progress = ((y + vh / 2) - (it.rect.top + it.rect.height / 2)) / (vh + it.rect.height);
        var offset = progress * it.speed * 100;
        // clamp agar layer tidak pernah keluar terlalu jauh
        offset = Math.max(-24, Math.min(24, offset));
        it.el.style.transform = 'translate3d(0,' + offset.toFixed(2) + '%,0)';
      });
      ticking = false;
    }

    function onScroll() {
      if (!ticking) { ticking = true; requestAnimationFrame(render); }
    }

    measure();
    render();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', function () { measure(); onScroll(); }, { passive: true });
    window.addEventListener('load', function () { measure(); onScroll(); });

    motionCleanups.push(function () {
      window.removeEventListener('scroll', onScroll);
      io.disconnect();
      items.forEach(function (it) { it.el.style.transform = ''; it.el.style.willChange = ''; });
    });
  }

  /* ------------------------------------------------------------------ *
   * 6. Scroll reveal (stagger)
   * ------------------------------------------------------------------ */
  function initReveal() {
    var els = $$('[data-reveal]');
    if (!els.length) { return; }
    if (reduce || !('IntersectionObserver' in window)) {
      els.forEach(function (el) { el.classList.add('is-revealed'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) { return; }
        var el = entry.target;
        var delay = parseInt(el.dataset.revealDelay || '0', 10);
        el.style.setProperty('--reveal-delay', delay + 'ms');
        el.classList.add('is-revealed');
        io.unobserve(el);
        setTimeout(function () { el.style.willChange = ''; }, 800 + delay);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

    els.forEach(function (el, i) {
      if (!el.dataset.revealDelay && el.parentElement && el.parentElement.dataset.revealGroup !== undefined) {
        var sibs = $$('[data-reveal]', el.parentElement);
        el.dataset.revealDelay = String(Math.min(sibs.indexOf(el), 6) * 90);
      }
      io.observe(el);
    });
  }

  /* ------------------------------------------------------------------ *
   * 7. Counter angka statistik
   * ------------------------------------------------------------------ */
  function initCounters() {
    var els = $$('[data-count]');
    if (!els.length) { return; }

    function format(n, decimals) {
      return n.toLocaleString('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
    }

    function run(el) {
      var target = parseFloat(el.dataset.count);
      if (isNaN(target)) { return; }
      var decimals = (el.dataset.count.split('.')[1] || '').length;
      var prefix = el.dataset.prefix || '';
      var suffix = el.dataset.suffix || '';

      if (reduce) { el.textContent = prefix + format(target, decimals) + suffix; return; }

      var dur = 1500, start = null;
      function step(ts) {
        if (start === null) { start = ts; }
        var p = Math.min((ts - start) / dur, 1);
        var eased = 1 - Math.pow(1 - p, 3); // easeOutCubic
        el.textContent = prefix + format(target * eased, decimals) + suffix;
        if (p < 1) { requestAnimationFrame(step); }
      }
      requestAnimationFrame(step);
    }

    if (!('IntersectionObserver' in window)) { els.forEach(run); return; }

    // HTML sudah memuat angka finalnya (agar terbaca tanpa JS). Karena JS ada,
    // nolkan dulu supaya animasi hitung-naik tidak terlihat melompat.
    if (!reduce) {
      els.forEach(function (el) {
        var d = (el.dataset.count.split('.')[1] || '').length;
        el.textContent = (el.dataset.prefix || '') +
          (0).toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d }) +
          (el.dataset.suffix || '');
      });
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) { run(entry.target); io.unobserve(entry.target); }
      });
    }, { threshold: 0.4 });
    els.forEach(function (el) { io.observe(el); });
  }

  /* ------------------------------------------------------------------ *
   * 8. Tilt 3D (hanya pointer presisi, non-reduced-motion)
   * ------------------------------------------------------------------ */
  function initTilt() {
    if (reduce || !pointerFine.matches) { return; }
    var cards = $$('.tilt');
    if (!cards.length) { return; }
    var MAX = 6;

    cards.forEach(function (card) {
      var raf = null;
      function move(e) {
        if (raf) { return; }
        raf = requestAnimationFrame(function () {
          var r = card.getBoundingClientRect();
          var px = (e.clientX - r.left) / r.width - 0.5;
          var py = (e.clientY - r.top) / r.height - 0.5;
          card.style.transform =
            'perspective(900px) rotateX(' + (-py * MAX).toFixed(2) + 'deg) rotateY(' +
            (px * MAX).toFixed(2) + 'deg) translateY(-6px)';
          raf = null;
        });
      }
      function reset() {
        if (raf) { cancelAnimationFrame(raf); raf = null; }
        card.style.transform = '';
      }
      card.addEventListener('pointermove', move);
      card.addEventListener('pointerleave', reset);
      card.addEventListener('blur', reset, true);
      motionCleanups.push(function () {
        card.removeEventListener('pointermove', move);
        card.removeEventListener('pointerleave', reset);
        reset();
      });
    });
  }

  /* ------------------------------------------------------------------ *
   * 9. Testimonial slider — aksesibel (tombol, keyboard, pause, aria-live)
   * ------------------------------------------------------------------ */
  function initSliders() {
    $$('.tslider').forEach(function (slider) {
      var list = $('.tslider__list', slider);
      var slides = $$('.tslide', slider);
      var prev = $('.tslider__prev', slider);
      var next = $('.tslider__next', slider);
      var playBtn = $('.tslider__play', slider);
      var dotsWrap = $('.tslider__dots', slider);
      var live = $('.tslider__live', slider);
      if (!list || slides.length < 2) { return; }

      var index = 0;
      var timer = null;
      var interval = parseInt(slider.dataset.interval || '7000', 10);
      var autoplay = slider.dataset.autoplay === 'true' && !reduce;

      var dots = slides.map(function (_, i) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'tslider__dot';
        b.setAttribute('aria-label', 'Testimoni ' + (i + 1) + ' dari ' + slides.length);
        b.addEventListener('click', function () { stop(); go(i); });
        if (dotsWrap) { dotsWrap.appendChild(b); }
        return b;
      });

      function go(i) {
        index = (i + slides.length) % slides.length;
        list.style.transform = 'translate3d(' + (-index * 100) + '%,0,0)';
        slides.forEach(function (s, n) {
          s.setAttribute('aria-hidden', String(n !== index));
          $$('a,button', s).forEach(function (el) {
            if (n === index) { el.removeAttribute('tabindex'); } else { el.setAttribute('tabindex', '-1'); }
          });
        });
        dots.forEach(function (d, n) { d.setAttribute('aria-current', String(n === index)); });
        if (live) { live.textContent = 'Testimoni ' + (index + 1) + ' dari ' + slides.length; }
      }

      function start() {
        if (!autoplay || timer) { return; }
        timer = setInterval(function () { go(index + 1); }, interval);
        if (playBtn) { playBtn.setAttribute('aria-pressed', 'true'); playBtn.setAttribute('aria-label', 'Jeda pergantian testimoni'); }
      }
      function stop() {
        if (timer) { clearInterval(timer); timer = null; }
        if (playBtn) { playBtn.setAttribute('aria-pressed', 'false'); playBtn.setAttribute('aria-label', 'Putar pergantian testimoni'); }
      }

      if (prev) { prev.addEventListener('click', function () { stop(); go(index - 1); }); }
      if (next) { next.addEventListener('click', function () { stop(); go(index + 1); }); }
      if (playBtn) {
        playBtn.addEventListener('click', function () { timer ? stop() : (autoplay = true, start()); });
      }

      slider.addEventListener('mouseenter', stop);
      slider.addEventListener('focusin', stop);
      slider.addEventListener('mouseleave', start);
      slider.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') { e.preventDefault(); stop(); go(index - 1); }
        if (e.key === 'ArrowRight') { e.preventDefault(); stop(); go(index + 1); }
      });

      // Swipe
      var x0 = null;
      slider.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
      slider.addEventListener('touchend', function (e) {
        if (x0 === null) { return; }
        var dx = e.changedTouches[0].clientX - x0;
        if (Math.abs(dx) > 45) { stop(); go(index + (dx < 0 ? 1 : -1)); }
        x0 = null;
      }, { passive: true });

      document.addEventListener('visibilitychange', function () {
        document.hidden ? stop() : start();
      });

      go(0);
      start();
      motionCleanups.push(stop);
    });
  }

  /* ------------------------------------------------------------------ *
   * 10. Marquee logo — gandakan isi untuk loop mulus
   * ------------------------------------------------------------------ */
  function initMarquee() {
    $$('.marquee').forEach(function (m) {
      var track = $('.marquee__track', m);
      var group = $('.marquee__group', m);
      if (!track || !group || track.children.length > 1) { return; }
      var clone = group.cloneNode(true);
      clone.setAttribute('aria-hidden', 'true');
      $$('a,button', clone).forEach(function (el) { el.setAttribute('tabindex', '-1'); });
      track.appendChild(clone);
      if (reduce) { track.style.animation = 'none'; }
    });
  }

  /* ------------------------------------------------------------------ *
   * 11. Tombol ke atas
   * ------------------------------------------------------------------ */
  function initToTop() {
    var btn = $('.to-top');
    if (!btn) { return; }
    var ticking = false;
    function check() {
      btn.classList.toggle('is-visible', window.scrollY > window.innerHeight * 0.8);
      ticking = false;
    }
    window.addEventListener('scroll', function () {
      if (!ticking) { ticking = true; requestAnimationFrame(check); }
    }, { passive: true });
    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
      var skip = $('.skip-link');
      if (skip) { skip.focus({ preventScroll: true }); }
    });
    check();
  }

  /* ------------------------------------------------------------------ *
   * Init
   * ------------------------------------------------------------------ */
  function boot() {
    initTheme();
    initHeader();
    initDrawer();
    initSubmenus();
    initParallax();
    initReveal();
    initCounters();
    initTilt();
    initSliders();
    initMarquee();
    initToTop();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
