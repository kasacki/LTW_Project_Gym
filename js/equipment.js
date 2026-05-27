const equipmentGrid = document.getElementById('equipment-grid');
const refreshButton = document.getElementById('equipment-refresh-button');
const refreshStatus = document.getElementById('equipment-refresh-status');

function createTextElement(tagName, className, text) {
    const element = document.createElement(tagName);

    if (className) {
        element.className = className;
    }

    element.textContent = text;
    return element;
}

function getStatusTagClass(status) {
    if (status === 'operational') {
        return 'tag tag-yoga';
    }

    if (status === 'maintenance') {
        return 'tag tag-spinning';
    }

    return 'tag tag-hiit';
}

function renderEquipmentCard(item) {
    const card = document.createElement('article');
    card.className = 'card equipment-card';
    card.dataset.equipmentId = item.id;

    card.appendChild(createTextElement('h3', '', item.name));
    card.appendChild(createTextElement('p', '', `Category: ${item.category || 'Other'}`));
    card.appendChild(createTextElement('div', 'equipment-availability', `Available: ${item.available_count} / ${item.total_count}`));

    const statusContainer = document.createElement('div');
    statusContainer.className = 'equipment-status';

    const statusTag = createTextElement('span', getStatusTagClass(item.status), String(item.status).toUpperCase());
    statusContainer.appendChild(statusTag);
    card.appendChild(statusContainer);

    return card;
}

function renderEquipment(items) {
    equipmentGrid.replaceChildren();

    if (!items.length) {
        equipmentGrid.appendChild(createTextElement('p', 'equipment-empty', 'No equipment available.'));
        return;
    }

    items.forEach(item => {
        equipmentGrid.appendChild(renderEquipmentCard(item));
    });
}

function setRefreshState(message, isLoading = false) {
    refreshStatus.textContent = message;
    refreshButton.disabled = isLoading;
}

async function refreshEquipment() {
    setRefreshState('Refreshing availability...', true);

    try {
        const response = await fetch('api/equipment.php', {
            headers: { Accept: 'application/json' },
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Could not refresh equipment availability.');
        }

        renderEquipment(data.equipment);
        setRefreshState(`Last updated at ${data.updated_at}.`);
    } catch (error) {
        setRefreshState(error.message || 'Could not refresh equipment availability.');
    }
}

refreshButton?.addEventListener('click', refreshEquipment);

if (equipmentGrid && refreshButton && refreshStatus) {
    setInterval(refreshEquipment, 30000);
}
