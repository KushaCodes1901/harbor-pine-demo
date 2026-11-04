// Public static demo: no form requests, storage, or email delivery.
const form = document.querySelector('#hp-demo-form');
if (form) {
    const response = document.querySelector('#hp-demo-response');
    document.querySelector('#hp-demo-submit').type = 'submit';
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        response.textContent = 'Test inquiry complete. No email was sent and no inquiry data was stored.';
        response.hidden = false;
        response.focus();
        form.reset();
    });
    form.addEventListener('input', () => { response.hidden = true; });
    const selected = document.querySelector('#hp-selected-property');
    const id = new URLSearchParams(location.search).get('property_id');
    if (id && /^[1-9]\d{0,9}$/.test(id)) {
        fetch('/assets/properties.json')
            .then((result) => { if (!result.ok) throw new Error('Property data unavailable'); return result.json(); })
            .then((properties) => {
                if (!Object.hasOwn(properties, id) || typeof properties[id] !== 'string') return;
                const title = document.createElement('strong');
                title.textContent = properties[id];
                const clear = document.createElement('a');
                clear.href = location.pathname + '#contact';
                clear.textContent = 'Make a general inquiry instead';
                selected.replaceChildren('Inquiring about: ', title, document.createElement('br'), clear);
                selected.hidden = false;
            })
            .catch(() => { selected.hidden = true; });
    }
}
