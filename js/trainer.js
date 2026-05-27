function activateTrainerTab(targetId) {
    document.querySelectorAll('.trainer-tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.trainer-tab').forEach(tabButton => tabButton.classList.remove('active'));

    document.getElementById(targetId)?.classList.add('active');
    document.querySelector(`[data-trainer-tab-target="${targetId}"]`)?.classList.add('active');
}

document.querySelectorAll('[data-trainer-tab-target]').forEach(button => {
    button.addEventListener('click', () => {
        activateTrainerTab(button.dataset.trainerTabTarget);
    });
});

const scheduleFilterButtons = document.querySelectorAll('[data-schedule-filter]');
const scheduleRows = document.querySelectorAll('[data-schedule-row]');
const scheduleFilterEmpty = document.getElementById('schedule-filter-empty');

function filterSchedule(filter) {
    let visibleRows = 0;

    scheduleRows.forEach(row => {
        const isVisible = filter === 'all'
            || row.dataset.scheduleStatus === filter
            || (filter === 'full' && row.dataset.scheduleFull === 'true');

        row.hidden = !isVisible;

        if (isVisible) {
            visibleRows += 1;
        }
    });

    if (scheduleFilterEmpty) {
        scheduleFilterEmpty.hidden = visibleRows > 0;
    }
}

scheduleFilterButtons.forEach(button => {
    button.addEventListener('click', () => {
        scheduleFilterButtons.forEach(filterButton => filterButton.classList.remove('active'));
        button.classList.add('active');
        filterSchedule(button.dataset.scheduleFilter);
    });
});

const rosterSearchInput = document.getElementById('roster-search');
const rosterCards = document.querySelectorAll('[data-roster-card]');
const rosterGlobalEmpty = document.getElementById('roster-global-empty');

function filterRosters() {
    const query = rosterSearchInput?.value.trim().toLowerCase() || '';
    let visibleCards = 0;

    rosterCards.forEach(card => {
        const rows = card.querySelectorAll('[data-roster-row]');
        let visibleRows = 0;

        rows.forEach(row => {
            const rowMatches = !query || row.dataset.rosterText.includes(query);
            row.hidden = !rowMatches;

            if (rowMatches) {
                visibleRows += 1;
            }
        });

        const cardMatches = !query || card.dataset.rosterText.includes(query);
        const shouldShowCard = rows.length ? visibleRows > 0 : cardMatches;
        card.hidden = !shouldShowCard;

        const noMatchMessage = card.querySelector('.trainer-roster-no-match');
        if (noMatchMessage) {
            noMatchMessage.hidden = visibleRows > 0 || !cardMatches;
        }

        if (shouldShowCard) {
            visibleCards += 1;
        }
    });

    if (rosterGlobalEmpty) {
        rosterGlobalEmpty.hidden = visibleCards > 0;
    }
}

rosterSearchInput?.addEventListener('input', filterRosters);

document.querySelectorAll('[data-open-roster]').forEach(button => {
    button.addEventListener('click', () => {
        const rosterCard = document.getElementById(`roster-class-${button.dataset.openRoster}`);

        activateTrainerTab('tab-rosters');

        if (rosterSearchInput) {
            rosterSearchInput.value = '';
            filterRosters();
        }

        rosterCard?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        rosterCard?.classList.add('is-highlighted');

        window.setTimeout(() => {
            rosterCard?.classList.remove('is-highlighted');
        }, 1400);
    });
});

document.querySelectorAll('[data-review-toggle]').forEach(button => {
    button.addEventListener('click', () => {
        const panel = document.getElementById(button.dataset.reviewToggle);

        if (!panel) {
            return;
        }

        const shouldOpen = panel.hidden;
        panel.hidden = !shouldOpen;
        button.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        button.textContent = shouldOpen ? 'Hide reviews' : 'View reviews';
    });
});
