document.querySelectorAll('[data-home-tab-target]').forEach(button => {
    button.addEventListener('click', () => {
        document.querySelectorAll('.home-tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.home-tab').forEach(tabButton => tabButton.classList.remove('active'));

        document.getElementById(button.dataset.homeTabTarget)?.classList.add('active');
        button.classList.add('active');
    });
});
