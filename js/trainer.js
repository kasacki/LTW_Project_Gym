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

// Schedule management: Edit and Cancel modals
const editModal = document.getElementById('modal-edit-class');
const cancelModal = document.getElementById('modal-cancel-class');

function openModal(modal) {
    if (!modal) return;
    modal.hidden = false;
    modal.querySelector('input, button')?.focus();
    document.body.style.overflow = 'hidden';
}

function closeModal(modal) {
    if (!modal) return;
    modal.hidden = true;
    document.body.style.overflow = '';
}

// Edit class button handler
document.querySelectorAll('[data-edit-class]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('edit-class-id').value = btn.dataset.editClass;
        document.getElementById('edit-class-name').value = btn.dataset.className;
        document.getElementById('edit-class-room').value = btn.dataset.classRoom;
        document.getElementById('edit-class-scheduled').value = btn.dataset.classScheduled;
        document.getElementById('edit-class-duration').value = btn.dataset.classDuration;
        openModal(editModal);
    });
});

// Cancel class button handler
document.querySelectorAll('[data-cancel-class]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('cancel-class-id').value = btn.dataset.cancelClass;
        const descEl = document.getElementById('modal-cancel-class-name');
        if (descEl) descEl.textContent = `Class: ${btn.dataset.className}`;
        openModal(cancelModal);
    });
});

// Close modal buttons
document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
        const modal = document.getElementById(btn.dataset.closeModal);
        closeModal(modal);
    });
});

// Close modal on backdrop click
[editModal, cancelModal].forEach(modal => {
    modal?.addEventListener('click', e => {
        if (e.target === modal) closeModal(modal);
    });
});

// Close on Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        [editModal, cancelModal].forEach(modal => {
            if (!modal?.hidden) closeModal(modal);
        });
    }
});
