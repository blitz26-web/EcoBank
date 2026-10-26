<style>
    @keyframes kh-fade-up {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes kh-pop-in {
        from { opacity: 0; transform: scale(.96); }
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes kh-pulse-dot {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, .5); }
        50% { transform: scale(1.15); box-shadow: 0 0 0 .3rem rgba(220, 53, 69, 0); }
    }
    @keyframes kh-shimmer {
        from { background-position: 200% 0; }
        to { background-position: -200% 0; }
    }

    html { scroll-behavior: smooth; }

    .kh-reveal {
        opacity: 0;
        transform: translateY(16px);
    }
    .kh-reveal.kh-in {
        opacity: 1;
        transform: translateY(0);
        transition: opacity .55s ease, transform .55s cubic-bezier(.22, 1, .36, 1);
        transition-delay: var(--kh-delay, 0ms);
    }

    .navbar .nav-link {
        border-radius: .5rem;
        transition: color .2s ease, background-color .2s ease;
    }
    .navbar .nav-link:hover {
        background-color: rgba(25, 135, 84, .1);
        color: #198754;
    }
    [data-bs-theme="dark"] .navbar .nav-link:hover {
        color: #75dfb4;
    }

    .btn {
        transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease,
            border-color .18s ease, color .18s ease;
    }
    .btn:hover {
        transform: translateY(-2px);
    }
    .btn:active {
        transform: translateY(0) scale(.97);
        transition-duration: .08s;
    }
    .btn-success {
        box-shadow: 0 .25rem .75rem rgba(25, 135, 84, .25);
    }
    .btn-success:hover {
        box-shadow: 0 .5rem 1.1rem rgba(25, 135, 84, .38);
    }
    .btn-outline-success:hover,
    .btn-outline-secondary:hover,
    .btn-outline-danger:hover {
        box-shadow: 0 .35rem .85rem rgba(0, 0, 0, .12);
    }
    .btn i {
        transition: transform .18s ease;
    }
    .btn:hover i {
        transform: translateY(-1px) scale(1.08);
    }
    .btn-close:hover {
        transform: rotate(90deg);
        transition: transform .2s ease;
    }

    .app-card, .guest-card, .hero-card {
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease,
            background-color .2s ease-in-out, color .2s ease-in-out;
    }
    .app-card:hover, .guest-card:hover, .hero-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 .75rem 1.6rem rgba(25, 135, 84, .16);
    }
    [data-bs-theme="dark"] .app-card:hover, [data-bs-theme="dark"] .guest-card:hover,
    [data-bs-theme="dark"] .hero-card:hover {
        box-shadow: 0 .75rem 1.6rem rgba(0, 0, 0, .55);
    }
    .app-card:hover .stat-icon {
        animation: kh-pop-in .35s ease;
    }

    .dropdown-menu.show {
        animation: kh-pop-in .18s ease;
    }
    .dropdown-item {
        transition: padding-left .18s ease, background-color .15s ease, color .15s ease;
    }
    .dropdown-item:hover {
        padding-left: 1.5rem;
    }
    .dropdown-item.d-flex:hover {
        padding-left: .5rem;
    }
    .notif-item:hover {
        background-color: rgba(25, 135, 84, .07);
    }

    .nav-link, .dropdown-item, .page-link, .text-decoration-none {
        transition: color .15s ease, background-color .15s ease, opacity .15s ease;
    }
    a.text-decoration-none:hover i.bi-arrow-right {
        transform: translateX(3px);
        transition: transform .2s ease;
    }
    .hover-lift-link:hover {
        transform: translateX(3px);
        transition: transform .2s ease;
    }

    .notification-dot {
        position: absolute;
        top: .3rem;
        right: .3rem;
        width: .55rem;
        height: .55rem;
        border-radius: 50%;
        background: #dc3545;
        border: 2px solid var(--bs-body-bg);
        animation: kh-pulse-dot 1.8s ease-in-out infinite;
    }
    .notif-icon {
        font-size: 1.05rem;
        line-height: 1;
        padding-top: .1rem;
    }
    .notif-msg {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .notif-scroll {
        max-height: 19rem;
        overflow-y: auto;
    }

    .badge-orb {
        width: 3.4rem;
        height: 3.4rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        transition: transform .2s ease;
    }
    .badge-orb-wrap:hover .badge-orb {
        transform: scale(1.12) rotate(-4deg);
    }
    .badge-earned {
        background: rgba(25, 135, 84, .12);
        color: #198754;
        border: 2px solid #198754;
    }
    .badge-level {
        background: linear-gradient(135deg, #198754, #20c997);
        color: #fff;
        border: 2px solid transparent;
    }
    .badge-locked {
        background: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
        border: 2px dashed var(--bs-border-color);
        opacity: .65;
    }

    .table-hover > tbody > tr {
        transition: background-color .15s ease;
    }
    .progress-bar {
        transition: width 1s cubic-bezier(.22, 1, .36, 1);
    }

    .alert {
        animation: kh-fade-up .4s ease;
    }

    @media (prefers-reduced-motion: reduce) {
        html { scroll-behavior: auto; }
        *, *::before, *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
        }
        .kh-reveal {
            opacity: 1;
            transform: none;
        }
    }
</style>

<script>
    (function () {
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        var formatter = new Intl.NumberFormat('id-ID');

        document.querySelectorAll('[data-count-up]').forEach(function (el) {
            var target = parseInt(el.getAttribute('data-count-up'), 10) || 0;
            var duration = 900;
            var start = null;

            function frame(ts) {
                if (start === null) {
                    start = ts;
                }
                var t = Math.min(1, (ts - start) / duration);
                var eased = 1 - Math.pow(1 - t, 3);
                el.textContent = formatter.format(Math.round(target * eased));
                if (t < 1) {
                    requestAnimationFrame(frame);
                }
            }

            el.textContent = formatter.format(0);
            requestAnimationFrame(frame);
        });

        document.querySelectorAll('[data-motion-width]').forEach(function (el) {
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    el.style.width = el.getAttribute('data-motion-width') + '%';
                });
            });
        });

        var revealTargets = Array.prototype.filter.call(
            document.querySelectorAll('.app-card, .guest-card, .hero-card, main > .alert'),
            function (el) {
                return el.closest('.modal, .dropdown-menu, template') === null;
            },
        );

        if ('IntersectionObserver' in window) {
            var stagger = new Map();

            revealTargets.forEach(function (el) {
                var parent = el.parentElement;
                var index = stagger.get(parent) || 0;
                stagger.set(parent, index + 1);
                el.style.setProperty('--kh-delay', Math.min(index * 70, 420) + 'ms');
                el.classList.add('kh-reveal');
            });

            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('kh-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });

            revealTargets.forEach(function (el) {
                observer.observe(el);
            });
        }
    })();
</script>
