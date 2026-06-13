document.addEventListener('click', (event) => {
  const button = event.target.closest('[data-fill-location]');
  if (!button) return;
  const data = JSON.parse(button.dataset.fillLocation);
  const form = document.querySelector('#location-form');
  if (!form) return;
  ['id', 'code', 'name', 'area', 'parent_id', 'notes'].forEach((name) => {
    const field = form.querySelector(`[name="${name}"]`);
    if (field) field.value = data[name] ?? '';
  });
  form.scrollIntoView({ behavior: 'smooth', block: 'center' });
});

document.addEventListener('change', (event) => {
  if (event.target.name === 'location_id') {
    const hint = document.querySelector('[name="container_id"]');
    if (hint) hint.title = 'Behälterliste zeigt alle Behälter mit Ortscode.';
  }
});
