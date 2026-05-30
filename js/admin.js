function showSection(name) {
    document.querySelectorAll('.admin-section').forEach(section => section.classList.remove('active'));
    document.querySelectorAll('.sidebar-nav-btn').forEach(button => button.classList.remove('active'));

    document.getElementById(`section-${name}`)?.classList.add('active');
    document.getElementById(`nav-${name}`)?.classList.add('active');
}

function openModal(id) {
    document.getElementById(id)?.classList.add('open');
    document.body.classList.add('modal-open');
}

function closeModal(id) {
    document.getElementById(id)?.classList.remove('open');
    if (!document.querySelector('.modal-backdrop.open')) {
        document.body.classList.remove('modal-open');
    }
}

function parseRecord(button, attribute) {
    try {
        return JSON.parse(button.getAttribute(attribute));
    } catch (_error) {
        return null;
    }
}

function setValue(id, value) {
    const field = document.getElementById(id);
    if (field) field.value = value ?? '';
}

function openEditMember(member) {
    setValue('edit-member-uid', member.user_id);
    setValue('edit-member-fullname', member.full_name);
    setValue('edit-member-username', member.username);
    setValue('edit-member-email', member.email);
    setValue('edit-member-tier', member.membership_tier);
    setValue('edit-member-status', member.membership_status || 'active');
    openModal('modal-edit-member');
}

function openEditTrainer(trainer) {
    setValue('edit-trainer-uid', trainer.user_id);
    setValue('edit-trainer-fullname', trainer.full_name);
    setValue('edit-trainer-username', trainer.username);
    setValue('edit-trainer-email', trainer.email);
    setValue('edit-trainer-specs', trainer.specializations || '');
    setValue('edit-trainer-certs', trainer.certifications || '');
    setValue('edit-trainer-bio', (trainer.bio || '').replace(' [DEACTIVATED]', ''));
    openModal('modal-edit-trainer');
}

function openEditClass(classItem) {
    const trainerSelect = document.getElementById('edit-class-trainer');

    trainerSelect?.querySelectorAll('option').forEach(option => {
        option.hidden = option.dataset.deactivated === 'true' && option.value !== String(classItem.trainer_id);
    });

    setValue('edit-class-id', classItem.id);
    setValue('edit-class-name', classItem.name);
    setValue('edit-class-type', classItem.type);
    setValue('edit-class-trainer', classItem.trainer_id);
    setValue('edit-class-room', classItem.room || '');
    setValue('edit-class-capacity', classItem.capacity);
    setValue('edit-class-scheduled', (classItem.scheduled_at || '').replace(' ', 'T').slice(0, 16));
    setValue('edit-class-duration', classItem.duration_min || 60);

    const featuredCheckbox = document.getElementById('edit-class-featured');
    if (featuredCheckbox) featuredCheckbox.checked = Number(classItem.is_featured) === 1;

    openModal('modal-edit-class');
}

function openEditEquipment(equipment) {
    setValue('edit-eq-id', equipment.id);
    setValue('edit-eq-name', equipment.name);
    setValue('edit-eq-category', equipment.category || 'Other');
    setValue('edit-eq-status', equipment.status || 'operational');
    setValue('edit-eq-total', equipment.total_count);
    setValue('edit-eq-available', equipment.available_count);
    openModal('modal-edit-equipment');
}

document.querySelectorAll('[data-admin-section]').forEach(button => {
    button.addEventListener('click', () => showSection(button.dataset.adminSection));
});

const adminShell = document.querySelector('[data-active-admin-section]');
const serverActiveSection = adminShell?.dataset.activeAdminSection || '';
const hash = location.hash.replace('#', '');
const initialSection = serverActiveSection || hash;

if (['overview', 'members', 'trainers', 'classes', 'equipment'].includes(initialSection)) {
    showSection(initialSection);
}

document.querySelectorAll('[data-auto-dismiss]').forEach(alert => {
    const delay = Number(alert.dataset.autoDismiss);
    if (!Number.isFinite(delay) || delay <= 0) return;

    setTimeout(() => {
        alert.classList.add('is-dismissing');
        alert.addEventListener('transitionend', () => alert.remove(), { once: true });
    }, delay);
});

document.querySelectorAll('[data-table-filter]').forEach(input => {
    const table = document.getElementById(input.dataset.tableFilter);
    if (!table) return;

    const rows = Array.from(table.querySelectorAll('tbody tr[data-search]'));
    const emptyRow = table.querySelector('.admin-filter-empty');
    const staticEmptyRows = Array.from(table.querySelectorAll('.admin-static-empty'));

    input.addEventListener('input', () => {
        const query = input.value.trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach(row => {
            const matches = row.dataset.search.includes(query);
            row.hidden = !matches;
            if (matches) visibleCount += 1;
        });

        staticEmptyRows.forEach(row => {
            row.hidden = query !== '';
        });

        if (emptyRow) {
            emptyRow.hidden = query === '' || visibleCount > 0;
        }
    });

    if (emptyRow) {
        emptyRow.hidden = true;
    }
});

document.querySelectorAll('form[method="POST"]').forEach(form => {
    form.addEventListener('submit', () => {
        const activeSection = document.querySelector('.admin-section.active')?.id.replace('section-', '') || 'overview';
        let sectionInput = form.querySelector('input[name="admin_section"]');

        if (!sectionInput) {
            sectionInput = document.createElement('input');
            sectionInput.type = 'hidden';
            sectionInput.name = 'admin_section';
            form.appendChild(sectionInput);
        }

        sectionInput.value = activeSection;
    });
});

document.querySelectorAll('[data-open-modal]').forEach(button => {
    button.addEventListener('click', () => openModal(button.dataset.openModal));
});

document.querySelectorAll('[data-close-modal]').forEach(button => {
    button.addEventListener('click', () => closeModal(button.dataset.closeModal));
});

document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', event => {
        if (event.target === backdrop) closeModal(backdrop.id);
    });
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal-backdrop.open').forEach(modal => closeModal(modal.id));
    }
});

document.querySelectorAll('[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
        if (!confirm(form.dataset.confirm)) event.preventDefault();
    });
});

document.querySelectorAll('[data-edit-member]').forEach(button => {
    button.addEventListener('click', () => {
        const record = parseRecord(button, 'data-edit-member');
        if (record) openEditMember(record);
    });
});

document.querySelectorAll('[data-edit-trainer]').forEach(button => {
    button.addEventListener('click', () => {
        const record = parseRecord(button, 'data-edit-trainer');
        if (record) openEditTrainer(record);
    });
});

document.querySelectorAll('[data-edit-class]').forEach(button => {
    button.addEventListener('click', () => {
        const record = parseRecord(button, 'data-edit-class');
        if (record) openEditClass(record);
    });
});

document.querySelectorAll('[data-edit-equipment]').forEach(button => {
    button.addEventListener('click', () => {
        const record = parseRecord(button, 'data-edit-equipment');
        if (record) openEditEquipment(record);
    });
});
