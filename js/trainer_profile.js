const stars = document.querySelectorAll('.star');
const ratingInput = document.getElementById('rating-input');
const starPicker = document.getElementById('star-picker');

function highlightStars(value, className) {
    stars.forEach(star => {
        star.classList.toggle(className, Number(star.dataset.val) <= Number(value));
    });
}

if (stars.length && ratingInput && starPicker) {
    stars.forEach(star => {
        star.addEventListener('mouseover', () => highlightStars(star.dataset.val, 'active'));
        star.addEventListener('click', () => {
            ratingInput.value = star.dataset.val;
            highlightStars(star.dataset.val, 'selected');
        });
    });

    starPicker.addEventListener('mouseleave', () => highlightStars(ratingInput.value, 'active'));
}
