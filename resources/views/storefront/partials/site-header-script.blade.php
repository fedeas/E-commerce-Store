<script>
    (() => {
        const body = document.body;
        const overlay = document.querySelector('.catalog-overlay');
        const openers = document.querySelectorAll('.js-open-catalog');
        const closers = document.querySelectorAll('.js-close-catalog');
        const tabs = document.querySelectorAll('.drawer-cat-button');
        const panels = document.querySelectorAll('.drawer-panel');

        const openCatalog = () => {
            body.classList.add('catalog-open');
            overlay?.setAttribute('aria-hidden', 'false');
        };

        const closeCatalog = () => {
            body.classList.remove('catalog-open');
            overlay?.setAttribute('aria-hidden', 'true');
        };

        openers.forEach((button) => button.addEventListener('click', openCatalog));
        closers.forEach((button) => button.addEventListener('click', closeCatalog));

        overlay?.addEventListener('click', (event) => {
            if (event.target === overlay) {
                closeCatalog();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeCatalog();
            }
        });

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.panel;

                tabs.forEach((item) => {
                    const active = item === tab;
                    item.classList.toggle('is-active', active);
                    item.setAttribute('aria-selected', active ? 'true' : 'false');
                });

                panels.forEach((panel) => {
                    panel.classList.toggle('is-active', panel.dataset.panel === target);
                });
            });
        });
    })();
</script>
