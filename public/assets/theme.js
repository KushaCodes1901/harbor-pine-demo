// Without JavaScript the navigation stays visible.
const menuButton = document.querySelector('.hp-menu-toggle');
const navigation = document.querySelector('.hp-nav');
if (menuButton && navigation) {
    menuButton.hidden = false;
    navigation.classList.add('hp-nav--enhanced');
    function closeMenu() {
        menuButton.setAttribute('aria-expanded', 'false');
        navigation.classList.remove('is-open');
    }
    menuButton.addEventListener('click', () => {
        const open = menuButton.getAttribute('aria-expanded') === 'true';
        menuButton.setAttribute('aria-expanded', String(!open));
        navigation.classList.toggle('is-open', !open);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && menuButton.getAttribute('aria-expanded') === 'true') {
            closeMenu(); menuButton.focus();
        }
    });
    navigation.addEventListener('click', (event) => { if (event.target.closest('a')) closeMenu(); });
    document.addEventListener('click', (event) => {
        if (!navigation.contains(event.target) && !menuButton.contains(event.target)) closeMenu();
    });
    // Close before tabbing out so hidden links do not retain focus.
    document.querySelector('.hp-header').addEventListener('focusout', (event) => {
        if (event.relatedTarget && !event.currentTarget.contains(event.relatedTarget)) closeMenu();
    });
}
