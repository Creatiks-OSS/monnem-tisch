@props([
    'name' => 'content',
    'value' => '',
    'label' => 'İçerik',
    'id' => null,
])

@php
    $fieldId = $id ?: $name;
    $html = old($name, $value ?? '');
@endphp

<div
    class="rich-editor"
    x-data="{
        sync() {
            this.$refs.input.value = this.$refs.editor.innerHTML;
        },
        cmd(command, value = null) {
            this.$refs.editor.focus();
            document.execCommand(command, false, value);
            this.sync();
        },
        block(tag) {
            this.cmd('formatBlock', '<' + tag + '>');
        },
        link() {
            const url = window.prompt('Bağlantı URL', 'https://');
            if (url) this.cmd('createLink', url);
        },
        init() {
            const form = this.$el.closest('form');
            if (form) form.addEventListener('submit', () => this.sync());
        }
    }"
>
    @if ($label)
        <label for="{{ $fieldId }}" class="mb-1 block text-sm">{{ $label }}</label>
    @endif

    <div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
        <div class="flex flex-wrap items-center gap-1 border-b border-stone-200 bg-stone-50 p-2">
            <button type="button" class="rich-editor-btn" title="Başlık 1" @click="block('h1')">H1</button>
            <button type="button" class="rich-editor-btn" title="Başlık 2" @click="block('h2')">H2</button>
            <button type="button" class="rich-editor-btn" title="Başlık 3" @click="block('h3')">H3</button>
            <button type="button" class="rich-editor-btn" title="Paragraf" @click="block('p')">P</button>
            <span class="mx-1 h-5 w-px bg-stone-200"></span>
            <button type="button" class="rich-editor-btn font-bold" title="Kalın" @click="cmd('bold')">B</button>
            <button type="button" class="rich-editor-btn italic" title="İtalik" @click="cmd('italic')">I</button>
            <button type="button" class="rich-editor-btn underline" title="Altı çizili" @click="cmd('underline')">U</button>
            <span class="mx-1 h-5 w-px bg-stone-200"></span>
            <button type="button" class="rich-editor-btn" title="Madde listesi" @click="cmd('insertUnorderedList')">• Liste</button>
            <button type="button" class="rich-editor-btn" title="Numaralı liste" @click="cmd('insertOrderedList')">1. Liste</button>
            <button type="button" class="rich-editor-btn" title="Alıntı" @click="block('blockquote')">Alıntı</button>
            <button type="button" class="rich-editor-btn" title="Bağlantı" @click="link()">Link</button>
            <button type="button" class="rich-editor-btn" title="Biçimi temizle" @click="cmd('removeFormat')">Temizle</button>
        </div>

        <div
            id="{{ $fieldId }}_editor"
            x-ref="editor"
            class="rich-editor-area min-h-[280px] px-4 py-3 text-sm outline-none"
            contenteditable="true"
            @input="sync()"
            @blur="sync()"
        >{!! $html ?: '<p></p>' !!}</div>
    </div>

    <textarea name="{{ $name }}" id="{{ $fieldId }}" x-ref="input" class="hidden">{{ $html }}</textarea>
</div>
