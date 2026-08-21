@once
    <script>
        function openMobileNav() {
            const nav = document.getElementById('mobileNav');
            const overlay = document.getElementById('navOverlay');
            const button = document.getElementById('hamburgerBtn');

            nav?.classList.add('open');
            nav?.setAttribute('aria-hidden', 'false');
            if (overlay) {
                overlay.style.display = 'block';
                requestAnimationFrame(() => overlay.classList.add('visible'));
            }
            button?.classList.add('open');
            button?.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            nav?.querySelector('.mobile-nav-close')?.focus({ preventScroll: true });
        }

        function closeMobileNav() {
            const nav = document.getElementById('mobileNav');
            const overlay = document.getElementById('navOverlay');
            const button = document.getElementById('hamburgerBtn');

            nav?.classList.remove('open');
            nav?.setAttribute('aria-hidden', 'true');
            overlay?.classList.remove('visible');
            button?.classList.remove('open');
            button?.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
            window.setTimeout(() => { if (overlay) overlay.style.display = 'none'; }, 280);
        }

        if (!window.__pmisMarketingShellInitialized) {
            window.__pmisMarketingShellInitialized = true;

            document.querySelectorAll('.pmis-nav__group > button').forEach((button) => {
                button.addEventListener('click', () => {
                    const expanded = button.getAttribute('aria-expanded') === 'true';
                    button.setAttribute('aria-expanded', String(!expanded));
                    button.parentElement?.classList.toggle('open', !expanded);
                });
            });

            document.addEventListener('click', (event) => {
                document.querySelectorAll('.pmis-nav__group.open').forEach((group) => {
                    if (!group.contains(event.target)) {
                        group.classList.remove('open');
                        group.querySelector('button')?.setAttribute('aria-expanded', 'false');
                    }
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeMobileNav();
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth > 940 && document.getElementById('mobileNav')?.classList.contains('open')) {
                    closeMobileNav();
                }
            });
        }
    </script>
@endonce
