document.querySelectorAll('.class-type-reviews').forEach(details => {
    const summary = details.querySelector('summary');

    if (!summary) return;

    details.addEventListener('toggle', () => {
        summary.textContent = details.open ? summary.dataset.openLabel : summary.dataset.closedLabel;
    });
});
