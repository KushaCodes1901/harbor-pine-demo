// Use WordPress's built-in Media Library; IDs are validated again on save.
document.querySelectorAll('.hp-media-control').forEach((control) => {
    const input = control.querySelector('input');
    const preview = control.querySelector('.hp-media-preview');
    let frame;
    control.querySelector('.hp-media-select').addEventListener('click', () => {
        if (!frame) {
            frame = wp.media({ title: 'Choose an image', library: { type: 'image' }, multiple: false });
            frame.on('select', () => {
                const image = frame.state().get('selection').first().toJSON();
                input.value = image.id;
                const img = document.createElement('img');
                img.src = image.sizes?.thumbnail?.url || image.url;
                img.alt = image.alt || '';
                preview.replaceChildren(img);
            });
            frame.on('open', () => {
                const selection = frame.state().get('selection');
                selection.reset();
                if (Number(input.value)) selection.add(wp.media.attachment(Number(input.value)));
            });
        }
        frame.open();
    });
    control.querySelector('.hp-media-remove').addEventListener('click', () => {
        input.value = '0'; preview.replaceChildren();
    });
});
