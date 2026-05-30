const typeFilter = document.getElementById('filter-type');
const trainerFilter = document.getElementById('filter-trainer');
const dayFilter = document.getElementById('filter-day');
const timeStartFilter = document.getElementById('filter-time-start');
const timeEndFilter = document.getElementById('filter-time-end');
const classCards = document.querySelectorAll('.class-card');
const classesPage = document.querySelector('[data-csrf-token]');
const dateStartFilter = document.getElementById('filter-date-start');
const dateEndFilter = document.getElementById('filter-date-end');

const initialType = new URLSearchParams(window.location.search).get('type');
const initialTrainer = new URLSearchParams(window.location.search).get('trainer');

if (initialType && typeFilter && Array.from(typeFilter.options).some(option => option.value === initialType)) {
    typeFilter.value = initialType;
}

if (initialTrainer && trainerFilter && Array.from(trainerFilter.options).some(option => option.value === initialTrainer)) {
    trainerFilter.value = initialTrainer;
}

function normalizeTime(value) {
    const match = value.trim().match(/^([01]?\d|2[0-3]):([0-5]\d)$/);

    if (!match) {
        return '';
    }

    return `${match[1].padStart(2, '0')}:${match[2]}`;
}

function formatTimeInput(value, isDeleting = false) {
    const digits = value.replace(/\D/g, '').slice(0, 4);

    if (digits.length === 0) {
        return '';
    }

    if (isDeleting && digits.length <= 2) {
        return digits;
    }

    if (digits.length === 1 && Number(digits) > 2) {
        return `0${digits}:`;
    }

    if (digits.length <= 2) {
        return digits.length === 2 ? `${digits}:` : digits;
    }

    return `${digits.slice(0, 2)}:${digits.slice(2)}`;
}

function completeTimeInput(value) {
    const trimmedValue = value.trim();
    const hourOnlyMatch = trimmedValue.match(/^([01]?\d|2[0-3]):?$/);

    if (hourOnlyMatch) {
        return `${hourOnlyMatch[1].padStart(2, '0')}:00`;
    }

    return normalizeTime(trimmedValue);
}

function filterClasses() {
    const type = typeFilter?.value || 'all';
    const trainer = trainerFilter?.value || 'all';
    const day = dayFilter?.value || 'all';
    const timeStart = normalizeTime(timeStartFilter?.value || '');
    const timeEnd = normalizeTime(timeEndFilter?.value || '');
    const dateStart = dateStartFilter?.value || '';
    const dateEnd = dateEndFilter?.value || '';

    classCards.forEach(card => {
        const matchesType = type === 'all' || card.dataset.type === type;
        const matchesTrainer = trainer === 'all' || card.dataset.trainer === trainer;
        const matchesDay = day === 'all' || card.dataset.day === day;
        const matchesTimeStart = !timeStart || card.dataset.time >= timeStart;
        const matchesTimeEnd = !timeEnd || card.dataset.time <= timeEnd;
        const matchesDateStart = !dateStart || card.dataset.date >= dateStart;
        const matchesDateEnd = !dateEnd || card.dataset.date <= dateEnd;

        const shouldHide = !(matchesType && matchesTrainer && matchesDay && matchesTimeStart && matchesTimeEnd && matchesDateStart && matchesDateEnd);
        card.hidden = shouldHide;
        card.classList.toggle('is-hidden', shouldHide);
    });
}

typeFilter?.addEventListener('change', filterClasses);
dateStartFilter?.addEventListener('change', filterClasses);
dateEndFilter?.addEventListener('change', filterClasses);
trainerFilter?.addEventListener('change', filterClasses);
dayFilter?.addEventListener('change', filterClasses);

[timeStartFilter, timeEndFilter].forEach(input => {
    input?.addEventListener('input', event => {
        input.value = formatTimeInput(input.value, event.inputType?.startsWith('delete'));
        filterClasses();
    });
    input?.addEventListener('blur', () => {
        input.value = completeTimeInput(input.value);
        filterClasses();
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
        button.textContent = shouldOpen ? 'Hide reviews' : 'Show reviews';
    });
});

document.querySelectorAll('.class-enroll-action').forEach(button => {
    button.addEventListener('click', () => {
        const formData = new FormData();
        formData.append('csrf_token', classesPage?.dataset.csrfToken || '');
        formData.append('class_id', button.dataset.classId);
        formData.append('action', button.dataset.action);

        fetch('enroll_action.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                    return;
                }
                alert(data.message || 'Error');
            });
    });
});

filterClasses();
