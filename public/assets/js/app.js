document.addEventListener('change', function (event) {
    if (event.target.matches('input[type="file"]')) {
        const label = event.target.closest('.drop');
        const file = event.target.files && event.target.files[0];

        if (label && file) {
            const title = label.querySelector('h3');
            const description = label.querySelector('p');

            if (title) title.textContent = file.name;
            if (description) description.textContent = 'Arquivo selecionado';
        }
    }
});
