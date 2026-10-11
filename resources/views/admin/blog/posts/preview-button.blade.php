<button type="button" data-blog-preview="{{ route('admin.blog-posts.preview') }}" class="px-4 py-2 rounded-lg border border-stone-300 text-sm">Ön izleme</button>
@once
<script>
document.querySelectorAll('[data-blog-preview]').forEach((button) => {
    button.addEventListener('click', async () => {
        const form = button.closest('form');
        if (! form || button.disabled) return;

        const previewWindow = window.open('', '_blank');

        form.querySelectorAll('.rich-editor [contenteditable]').forEach((editor) => {
            const field = editor.closest('.rich-editor')?.querySelector('textarea');
            if (field) field.value = editor.innerHTML;
        });

        const method = form.querySelector('input[name="_method"]');
        if (method) method.disabled = true;
        const data = new FormData(form);
        data.delete('featured_image');
        data.delete('_method');
        if (method) method.disabled = false;

        button.disabled = true;
        try {
            const response = await fetch(button.dataset.blogPreview, {
                method: 'POST',
                body: data,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            const payload = await response.json().catch(() => ({}));
            if (! response.ok || ! payload.url) {
                previewWindow?.close();
                window.alert('Ön izleme açılamadı. Başlığı kontrol edip tekrar deneyin.');
                return;
            }
            if (previewWindow) {
                previewWindow.location = payload.url;
            } else {
                window.location = payload.url;
            }
        } catch (error) {
            previewWindow?.close();
            window.alert('Ön izleme açılamadı. Başlığı kontrol edip tekrar deneyin.');
        } finally {
            button.disabled = false;
        }
    });
});
</script>
@endonce
