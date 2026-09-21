<script setup>
import { defineProps, defineEmits, watch, onMounted, onBeforeUnmount } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import { BubbleMenu } from '@tiptap/vue-3/menus';
import StarterKit from '@tiptap/starter-kit';
import { Icon } from '@iconify/vue';

// Text Styling
import { TextStyle } from '@tiptap/extension-text-style';
import { Color } from '@tiptap/extension-color';
import { Underline } from '@tiptap/extension-underline';
import { Highlight } from '@tiptap/extension-highlight';

// Content & Media
import { Link as EditorLink } from '@tiptap/extension-link';
import ImageResize from 'tiptap-extension-resize-image';
import { Youtube } from '@tiptap/extension-youtube';

// Table
import { Table } from '@tiptap/extension-table';
import { TextAlign } from '@tiptap/extension-text-align';
import { TableCell } from '@tiptap/extension-table-cell';
import { TableHeader } from '@tiptap/extension-table-header';
import { TableRow } from '@tiptap/extension-table-row';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue']);

const CustomYoutube = Youtube.extend({
    renderHTML({ HTMLAttributes }) {
        return [
            'div',
            {
                'data-youtube-video': '',
                style: `width: ${HTMLAttributes.width || 640}px; height: ${HTMLAttributes.height || 480}px;`
            },
            [
                'iframe',
                {
                    src: HTMLAttributes.src,
                    width: HTMLAttributes.width || 640,
                    height: HTMLAttributes.height || 480,
                    allowfullscreen: "true"
                }
            ]
        ]
    }
});

const editor = useEditor({
    content: props.modelValue || "",
    editorProps: {
        attributes: {
            class: 'min-h-[280px] p-4 focus:outline-none',
        },
    },
    extensions: [
        StarterKit,
        TextStyle,
        Color,
        Underline,
        Highlight,
        EditorLink.configure({ openOnClick: false }),
        ImageResize,
        CustomYoutube,
        Table.configure({ resizable: true }),
        TableRow,
        TableHeader,
        TableCell,
        TextAlign.configure({ types: ['heading', 'paragraph'] })
    ],
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

watch(() => props.modelValue, (value) => {
    const isSame = editor.value.getHTML() === value;
    if (isSame) {
        return;
    }
    editor.value.commands.setContent(value, false);
});

const handleMouseUp = () => {
    const youtubeDiv = document.querySelector('.ProseMirror div[data-youtube-video].ProseMirror-selectednode');
    if (youtubeDiv && editor.value) {
        const currentWidth = youtubeDiv.offsetWidth;
        const currentHeight = youtubeDiv.offsetHeight;
        const attrs = editor.value.getAttributes('youtube');

        if (attrs && (Math.abs(attrs.width - currentWidth) > 5 || Math.abs(attrs.height - currentHeight) > 5)) {
            editor.value.commands.updateAttributes('youtube', {
                width: currentWidth,
                height: currentHeight
            });
        }
    }
};

onMounted(() => {
    document.addEventListener('mouseup', handleMouseUp);
});

onBeforeUnmount(() => {
    document.removeEventListener('mouseup', handleMouseUp);
    editor.value?.destroy();
});

const setImageWidth = (width) => {
    editor.value?.chain().focus().updateAttributes('image', { width }).run()
}

const setImageAlign = (align) => {
    editor.value?.chain().focus().updateAttributes('image', { align }).run()
}

const setLink = () => {
    const previousUrl = editor.value?.getAttributes('link').href
    const url = window.prompt('URL', previousUrl)

    if (url === null) {
        return
    }

    if (url === '') {
        editor.value?.chain().focus().extendMarkRange('link').unsetLink().run()
        return
    }

    editor.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}

const addImage = () => {
    const url = window.prompt('Image URL')

    if (url) {
        editor.value?.chain().focus().setImage({ src: url }).run()
    }
}

const addYoutubeVideo = () => {
    const url = window.prompt('Enter YouTube URL')

    if (url) {
        editor.value?.commands.setYoutubeVideo({
            src: url,
            width: 640,
            height: 480,
        })
    }
}
</script>

<template>
    <div v-if="editor" class="border rounded-md">
        <!-- Main Toolbar -->
        <div class="bg-gray-100 p-2 border-b flex flex-wrap gap-2 items-center rounded-t-md">
            <!-- Text Styles -->
            <button type="button" @click="editor.chain().focus().toggleBold().run()"
                class="px-2 py-1 text-xs rounded hover:bg-gray-100 font-bold cursor-pointer"
                :class="{ 'bg-gray-400 text-white': editor.isActive('bold'), 'bg-white': !editor.isActive('bold') }">B</button>
            <button type="button" @click="editor.chain().focus().toggleItalic().run()"
                class="px-2 py-1 text-xs rounded hover:bg-gray-100 italic cursor-pointer"
                :class="{ 'bg-gray-400 text-white': editor.isActive('italic'), 'bg-white': !editor.isActive('italic') }">I</button>
            <button type="button" @click="editor.chain().focus().toggleUnderline().run()"
                class="px-2 py-1 text-xs rounded hover:bg-gray-100 underline cursor-pointer"
                :class="{ 'bg-gray-400 text-white': editor.isActive('underline'), 'bg-white': !editor.isActive('underline') }">U</button>
            <button type="button" @click="editor.chain().focus().toggleStrike().run()"
                class="px-2 py-1 text-xs rounded hover:bg-gray-100 line-through cursor-pointer"
                :class="{ 'bg-gray-400 text-white': editor.isActive('strike'), 'bg-white': !editor.isActive('strike') }">S</button>
            <div class="border-l mx-1 h-4"></div>

            <button type="button" @click="editor.chain().focus().toggleHighlight().run()"
                :class="{ 'bg-gray-400 text-white': editor.isActive('highlight'), 'bg-white': !editor.isActive('highlight') }"
                class="px-2 py-1 rounded border shadow-sm text-sm cursor-pointer">
                <Icon icon="lucide:highlighter" width="1.2em" height="1.2em" />
            </button>

            <div class="border-l pl-2 flex gap-2 items-center">
                <input type="color" @input="editor.chain().focus().setColor($event.target.value).run()"
                    :value="editor.getAttributes('textStyle').color || '#000000'"
                    class="w-8 h-8 p-0 border-0 rounded cursor-pointer" title="Text Color" />
            </div>

            <!-- Headings -->
            <div class="border-l pl-2 flex gap-1 items-center">
                <button type="button" @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                    :class="{ 'bg-blue-500 text-white': editor.isActive('heading', { level: 1 }), 'bg-white': !editor.isActive('heading', { level: 1 }) }"
                    class="px-2 py-1 rounded border shadow-sm text-sm font-bold">H1</button>
                <button type="button" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                    :class="{ 'bg-blue-500 text-white': editor.isActive('heading', { level: 2 }), 'bg-white': !editor.isActive('heading', { level: 2 }) }"
                    class="px-2 py-1 rounded border shadow-sm text-sm font-semibold">H2</button>
                <button type="button" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                    :class="{ 'bg-blue-500 text-white': editor.isActive('heading', { level: 3 }), 'bg-white': !editor.isActive('heading', { level: 3 }) }"
                    class="px-2 py-1 rounded border shadow-sm text-sm font-medium">H3</button>
            </div>

            <!-- Lists -->
            <div class="border-l pl-2 flex gap-1 items-center">
                <button type="button" @click="editor.chain().focus().toggleBulletList().run()"
                    :class="{ 'bg-gray-400 text-white': editor.isActive('bulletList'), 'bg-white': !editor.isActive('bulletList') }"
                    class="px-2 py-1 rounded border shadow-sm text-sm cursor-pointer">
                    <Icon icon="lucide:list" width="1.2em" height="1.2em" />
                </button>
                <button type="button" @click="editor.chain().focus().toggleOrderedList().run()"
                    :class="{ 'bg-gray-400 text-white': editor.isActive('orderedList'), 'bg-white': !editor.isActive('orderedList') }"
                    class="px-2 py-1 rounded border shadow-sm text-sm cursor-pointer">
                    <Icon icon="lucide:list-ordered" width="1.2em" height="1.2em" />
                </button>
            </div>

            <!-- Text Align -->
            <div class="border-l pl-2 flex gap-2">
                <button type="button" @click="editor.chain().focus().setTextAlign('left').run()"
                    :class="{ 'bg-gray-400 text-white': editor.isActive({ textAlign: 'left' }), 'bg-white': !editor.isActive({ textAlign: 'left' }) }"
                    class="px-2 py-1 rounded border shadow-sm text-sm cursor-pointer">
                    <Icon icon="lucide:align-left" width="1.2em" height="1.2em" />
                </button>
                <button type="button" @click="editor.chain().focus().setTextAlign('center').run()"
                    :class="{ 'bg-gray-400 text-white': editor.isActive({ textAlign: 'center' }), 'bg-white': !editor.isActive({ textAlign: 'center' }) }"
                    class="px-2 py-1 rounded border shadow-sm text-sm cursor-pointer">
                    <Icon icon="lucide:align-center" width="1.2em" height="1.2em" />
                </button>
                <button type="button" @click="editor.chain().focus().setTextAlign('right').run()"
                    :class="{ 'bg-gray-400 text-white': editor.isActive({ textAlign: 'right' }), 'bg-white': !editor.isActive({ textAlign: 'right' }) }"
                    class="px-2 py-1 rounded border shadow-sm text-sm cursor-pointer">
                    <Icon icon="lucide:align-right" width="1.2em" height="1.2em" />
                </button>
            </div>

            <!-- Media & Links -->
            <div class="border-l pl-2 flex gap-2">
                <button type="button" @click="setLink"
                    :class="{ 'bg-blue-500 text-white': editor.isActive('link'), 'bg-white': !editor.isActive('link') }"
                    class="px-2 py-1 rounded border shadow-sm text-sm flex items-center gap-1">
                    <Icon icon="lucide:link" width="1.2em" height="1.2em" /> Link
                </button>
                <button type="button" @click="addImage"
                    class="px-2 py-1 rounded border shadow-sm text-sm bg-white hover:bg-gray-50 flex items-center gap-1">
                    <Icon icon="lucide:image" width="1.2em" height="1.2em" /> Image
                </button>
                <button type="button" @click="addYoutubeVideo"
                    class="px-2 py-1 rounded border shadow-sm text-sm bg-white hover:bg-gray-50 flex items-center gap-1">
                    <Icon icon="lucide:youtube" width="1.2em" height="1.2em" /> YouTube
                </button>
            </div>

            <!-- Table -->
            <div class="border-l pl-2 flex gap-2">
                <button type="button"
                    @click="editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()"
                    class="px-2 py-1 rounded border shadow-sm text-sm bg-white hover:bg-gray-50 flex items-center gap-1">
                    <Icon icon="lucide:table" width="1.2em" height="1.2em" /> Add Table
                </button>
                <button type="button" v-if="editor.isActive('table')"
                    @click="editor.chain().focus().deleteTable().run()"
                    class="px-2 py-1 rounded border shadow-sm text-sm bg-red-100 text-red-700 hover:bg-red-200 flex items-center gap-1">
                    <Icon icon="lucide:trash-2" width="1.2em" height="1.2em" /> Delete Table
                </button>
            </div>

            <!-- Table Controls (Show only when inside a table) -->
            <div class="border-l pl-2 flex flex-wrap gap-1" v-if="editor.isActive('table')">
                <button type="button" @click="editor.chain().focus().addColumnBefore().run()"
                    class="px-2 py-1 rounded border shadow-sm text-xs bg-white hover:bg-gray-50 flex items-center gap-1"
                    title="Add Column Before">
                    <Icon icon="lucide:arrow-left" width="1.2em" height="1.2em" /> Col
                </button>
                <button type="button" @click="editor.chain().focus().addColumnAfter().run()"
                    class="px-2 py-1 rounded border shadow-sm text-xs bg-white hover:bg-gray-50 flex items-center gap-1"
                    title="Add Column After">
                    <Icon icon="lucide:arrow-right" width="1.2em" height="1.2em" /> Col
                </button>
                <button type="button" @click="editor.chain().focus().deleteColumn().run()"
                    class="px-2 py-1 rounded border shadow-sm text-xs bg-white hover:bg-gray-50 flex items-center gap-1 text-red-500"
                    title="Delete Column">
                    <Icon icon="lucide:trash-2" width="1.2em" height="1.2em" /> Col
                </button>
                <button type="button" @click="editor.chain().focus().addRowBefore().run()"
                    class="px-2 py-1 rounded border shadow-sm text-xs bg-white hover:bg-gray-50 flex items-center gap-1"
                    title="Add Row Before">
                    <Icon icon="lucide:arrow-up" width="1.2em" height="1.2em" /> Row
                </button>
                <button type="button" @click="editor.chain().focus().addRowAfter().run()"
                    class="px-2 py-1 rounded border shadow-sm text-xs bg-white hover:bg-gray-50 flex items-center gap-1"
                    title="Add Row After">
                    <Icon icon="lucide:arrow-down" width="1.2em" height="1.2em" /> Row
                </button>
                <button type="button" @click="editor.chain().focus().deleteRow().run()"
                    class="px-2 py-1 rounded border shadow-sm text-xs bg-white hover:bg-gray-50 flex items-center gap-1 text-red-500"
                    title="Delete Row">
                    <Icon icon="lucide:trash-2" width="1.2em" height="1.2em" /> Row
                </button>
                <button type="button" @click="editor.chain().focus().mergeCells().run()"
                    class="px-2 py-1 rounded border shadow-sm text-xs bg-white hover:bg-gray-50 flex items-center gap-1">
                    <Icon icon="lucide:combine" width="1.2em" height="1.2em" /> Merge
                </button>
                <button type="button" @click="editor.chain().focus().splitCell().run()"
                    class="px-2 py-1 rounded border shadow-sm text-xs bg-white hover:bg-gray-50 flex items-center gap-1">
                    <Icon icon="lucide:split-square-horizontal" width="1.2em" height="1.2em" /> Split
                </button>
            </div>
        </div>

        <!-- Contextual Bubble Menu -->
        <bubble-menu :editor="editor" :tippy-options="{ duration: 100 }">

            <!-- Text Formatting Menu -->
            <div class="bg-white border rounded shadow-lg p-1 flex gap-1 items-center flex-wrap max-w-[400px]"
                v-if="!editor.isActive('image') && !editor.isActive('table')">
                <button type="button" @click="editor.chain().focus().toggleBold().run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100 font-bold"
                    :class="{ 'bg-gray-200': editor.isActive('bold') }">B</button>
                <button type="button" @click="editor.chain().focus().toggleItalic().run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100 italic"
                    :class="{ 'bg-gray-200': editor.isActive('italic') }">I</button>
                <button type="button" @click="editor.chain().focus().toggleUnderline().run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100 underline"
                    :class="{ 'bg-gray-200': editor.isActive('underline') }">U</button>
                <button type="button" @click="editor.chain().focus().toggleStrike().run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100 line-through"
                    :class="{ 'bg-gray-200': editor.isActive('strike') }">S</button>
                <div class="border-l mx-1 h-4"></div>
                <button type="button" @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100 font-bold"
                    :class="{ 'bg-gray-200': editor.isActive('heading', { level: 1 }) }">H1</button>
                <button type="button" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100 font-semibold"
                    :class="{ 'bg-gray-200': editor.isActive('heading', { level: 2 }) }">H2</button>
                <button type="button" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100 font-medium"
                    :class="{ 'bg-gray-200': editor.isActive('heading', { level: 3 }) }">H3</button>
                <div class="border-l mx-1 h-4"></div>
                <button type="button" @click="editor.chain().focus().toggleBulletList().run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100"
                    :class="{ 'bg-gray-200': editor.isActive('bulletList') }">
                    <Icon icon="lucide:list" width="1.2em" height="1.2em" />
                </button>
                <button type="button" @click="editor.chain().focus().toggleOrderedList().run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100"
                    :class="{ 'bg-gray-200': editor.isActive('orderedList') }">
                    <Icon icon="lucide:list-ordered" width="1.2em" height="1.2em" />
                </button>
                <div class="border-l mx-1 h-4"></div>
                <button type="button" @click="editor.chain().focus().setTextAlign('left').run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100"
                    :class="{ 'bg-gray-200': editor.isActive({ textAlign: 'left' }) }">
                    <Icon icon="lucide:align-left" width="1.2em" height="1.2em" />
                </button>
                <button type="button" @click="editor.chain().focus().setTextAlign('center').run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100"
                    :class="{ 'bg-gray-200': editor.isActive({ textAlign: 'center' }) }">
                    <Icon icon="lucide:align-center" width="1.2em" height="1.2em" />
                </button>

                <button type="button" @click="editor.chain().focus().setTextAlign('right').run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100"
                    :class="{ 'bg-gray-200': editor.isActive({ textAlign: 'right' }) }">
                    <Icon icon="lucide:align-right" width="1.2em" height="1.2em" />
                </button>
                <div class="border-l mx-1 h-4"></div>
                <button type="button" @click="setLink" class="px-2 py-1 text-xs rounded hover:bg-gray-100"
                    :class="{ 'bg-gray-200': editor.isActive('link') }">
                    <Icon icon="lucide:link" width="1.2em" height="1.2em" />
                </button>
            </div>

            <!-- Image Menu -->

            <!-- Table Menu -->
            <div class="bg-white border rounded shadow-lg p-1 flex gap-1" v-if="editor.isActive('table')">
                <button type="button" @click="editor.chain().focus().addColumnBefore().run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100 flex items-center gap-1"
                    title="Add Column Before">
                    <Icon icon="lucide:arrow-left" width="1.2em" height="1.2em" /> Col
                </button>
                <button type="button" @click="editor.chain().focus().addColumnAfter().run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100 flex items-center gap-1"
                    title="Add Column After">
                    <Icon icon="lucide:arrow-right" width="1.2em" height="1.2em" /> Col
                </button>
                <button type="button" @click="editor.chain().focus().deleteColumn().run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100 text-red-500 flex items-center gap-1"
                    title="Delete Column">
                    <Icon icon="lucide:trash-2" width="1.2em" height="1.2em" /> Col
                </button>
                <div class="border-l mx-1 h-4"></div>
                <button type="button" @click="editor.chain().focus().addRowBefore().run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100 flex items-center gap-1" title="Add Row Before">
                    <Icon icon="lucide:arrow-up" width="1.2em" height="1.2em" /> Row
                </button>
                <button type="button" @click="editor.chain().focus().addRowAfter().run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100 flex items-center gap-1" title="Add Row After">
                    <Icon icon="lucide:arrow-down" width="1.2em" height="1.2em" /> Row
                </button>
                <button type="button" @click="editor.chain().focus().deleteRow().run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100 text-red-500 flex items-center gap-1"
                    title="Delete Row">
                    <Icon icon="lucide:trash-2" width="1.2em" height="1.2em" /> Row
                </button>
                <div class="border-l mx-1 h-4"></div>
                <button type="button" @click="editor.chain().focus().deleteTable().run()"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-100 text-red-500 flex items-center gap-1">
                    <Icon icon="lucide:trash-2" width="1.2em" height="1.2em" /> Table
                </button>
            </div>
        </bubble-menu>

        <!-- Editor Content -->
        <editor-content :editor="editor" class="focus-within:ring-1 focus-within:ring-green-200" />
    </div>
</template>

<style>
/* Tiptap Editor Styles for Table and Media */
.ProseMirror,
.tiptap-content {
    outline: none !important;
}

.ProseMirror table,
.tiptap-content table {
    border-collapse: collapse;
    table-layout: fixed;
    width: 100%;
    margin: 0;
    overflow-wrap: break-word;
}

.ProseMirror table {
    overflow: hidden;
}

.ProseMirror td,
.tiptap-content td,
.ProseMirror th,
.tiptap-content th {
    min-width: 1em;
    border: 1px solid #ced4da;
    padding: 3px 5px;
    vertical-align: top;
    box-sizing: border-box;
    position: relative;
}

.ProseMirror th,
.tiptap-content th {
    font-weight: bold;
    text-align: left;
    background-color: #f1f3f5;
}

.ProseMirror .selectedCell:after,
.tiptap-content .selectedCell:after {
    z-index: 2;
    position: absolute;
    content: "";
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    background: rgba(200, 200, 255, 0.4);
    pointer-events: none;
}

.ProseMirror .column-resize-handle,
.tiptap-content .column-resize-handle {
    position: absolute;
    right: -2px;
    top: 0;
    bottom: -2px;
    width: 4px;
    background-color: #adf;
    pointer-events: none;
}

.ProseMirror p,
.tiptap-content p {
    margin-top: 0;
    margin-bottom: 1rem;
}

.ProseMirror p:empty::before,
.tiptap-content p:empty::before {
    content: "";
    display: inline-block;
}

.ProseMirror p:last-child,
.tiptap-content p:last-child {
    margin-bottom: 0;
}

/* Image styles */
.ProseMirror img,
.tiptap-content img {
    max-width: 100% !important;
    height: auto !important;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.ProseMirror img[data-align="left"],
.tiptap-content img[data-align="left"] {
    float: left;
    margin-right: 1rem;
    margin-bottom: 0.5rem;
}

.ProseMirror img[data-align="right"],
.tiptap-content img[data-align="right"] {
    float: right;
    margin-left: 1rem;
    margin-bottom: 0.5rem;
}

.ProseMirror img[data-align="center"],
.tiptap-content img[data-align="center"] {
    display: block;
    margin: 0 auto;
}

.ProseMirror img.ProseMirror-selectednode {
    outline: 3px solid #68CEF8;
}

/* YouTube Video styles */
.ProseMirror div[data-youtube-video],
.tiptap-content div[data-youtube-video] {
    display: inline-block;
    resize: both;
    overflow: hidden;
    position: relative;
    border: 2px solid transparent;
    max-width: 100% !important;
}

.ProseMirror div[data-youtube-video].ProseMirror-selectednode,
.tiptap-content div[data-youtube-video].ProseMirror-selectednode {
    border-color: #68CEF8;
}

.ProseMirror div[data-youtube-video].ProseMirror-selectednode iframe,
.tiptap-content div[data-youtube-video].ProseMirror-selectednode iframe {
    pointer-events: none;
    /* Disable interaction when selected so resize dragging works smoothly */
}

.ProseMirror iframe {
    width: 100% !important;
    height: 100% !important;
    border-radius: 4px;
    pointer-events: auto;
}

.tiptap-content iframe {
    max-width: 100% !important;
    border-radius: 4px;
    pointer-events: auto;
}

/* List styles */
.ProseMirror ul,
.tiptap-content ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}

.ProseMirror ol,
.tiptap-content ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}

.ProseMirror li>p,
.tiptap-content li>p {
    margin: 0;
}
</style>
