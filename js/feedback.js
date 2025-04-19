
const sliders = document.querySelectorAll('input[type="range"]');

sliders.forEach(slider => {
function updateBackground(el) {
    const val = (el.value - el.min) / (el.max - el.min) * 100;
    el.style.background = `linear-gradient(to right, #3182ce 0%, #3182ce ${val}%, #e2e8f0 ${val}%, #e2e8f0 100%)`;
}

updateBackground(slider);

slider.addEventListener('input', () => {
    updateBackground(slider);
});
});