<?php
/**
 * Admin Footer & Script Initialization
 * 
 * Includes WYSIWYG editor (Quill) and other admin scripts
 */
?>

</main>
</div>

<!-- Quill Rich Text Editor CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-toolbar.ql-snow {
        background-color: rgba(255, 255, 255, 0.9);
        border-color: rgba(255, 255, 255, 0.4);
        border-radius: 0.75rem 0.75rem 0 0;
    }

    .ql-container.ql-snow {
        border-color: rgba(255, 255, 255, 0.4);
        font-family: 'Inter', sans-serif;
        font-size: 1rem;
    }

    /* Force Quill's editor to respect image floats upon reload */
    .ql-editor img {
        display: inline-block;
    }

    /* Handle Left Alignment inside the Editor */
    .ql-editor img[style*="float: left"],
    .ql-editor .ql-align-left {
        float: left !important;
        margin: 0.5rem 1.5rem 1rem 0 !important;
    }

    /* Handle Right Alignment inside the Editor */
    .ql-editor img[style*="float: right"],
    .ql-editor .ql-align-right {
        float: right !important;
        margin: 0.5rem 0 1rem 1.5rem !important;
    }

    /* Clearfix so the editor height stretches correctly */
    .ql-editor::after {
        content: "";
        display: table;
        clear: both;
    }
</style>

<!-- Quill Rich Text Editor JS & Modules -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>window.Quill = Quill;</script>
<script src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editorEl = document.getElementById('quill-editor');
        if (editorEl) {
            // 5. Custom Image Handler
            function imageHandler() {
                const input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.click();

                input.onchange = async () => {
                    const file = input.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('file', file);

                    try {
                        const response = await fetch('/mbh-golden-global/admin/ajax/upload_image.php', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await response.json();

                        if (data.location) {
                            const range = quill.getSelection(true); // true focuses the editor and gets selection
                            quill.insertEmbed(range.index, 'image', data.location);
                        } else {
                            alert(data.error || 'Upload failed');
                        }
                    } catch (err) {
                        console.error('Error:', err);
                        alert('Image upload failed');
                    }
                };
            }

            // 4. Initialize Quill with ALL Tools
            const toolbarOptions = [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
                ['blockquote', 'code-block'],

                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'script': 'sub' }, { 'script': 'super' }],      // superscript/subscript
                [{ 'indent': '-1' }, { 'indent': '+1' }],          // outdent/indent
                [{ 'direction': 'rtl' }],                         // text direction

                [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
                [{ 'color': [false, '#000000', '#003355', '#0082CA', '#e60000', '#ff9900', '#ffff00', '#008a00', '#0066cc'] }, { 'background': [] }],
                [{ 'font': [] }],
                [{ 'align': [] }],

                ['clean'],                                        // remove formatting button
                ['link', 'image', 'video']                        // media
            ];

            // Force Quill to retain inline styles, widths, and heights on images
            var BaseImageFormat = Quill.import('formats/image');
            class CustomImage extends BaseImageFormat {
                static formats(domNode) {
                    return {
                        alt: domNode.getAttribute('alt'),
                        src: domNode.getAttribute('src'),
                        style: domNode.getAttribute('style'),
                        width: domNode.getAttribute('width'),
                        height: domNode.getAttribute('height')
                    };
                }
                format(name, value) {
                    if (['alt', 'style', 'width', 'height'].indexOf(name) > -1) {
                        if (value) {
                            this.domNode.setAttribute(name, value);
                        } else {
                            this.domNode.removeAttribute(name);
                        }
                    } else {
                        super.format(name, value);
                    }
                }
            }
            Quill.register(CustomImage, true);

            const quill = new Quill('#quill-editor', {
                theme: 'snow',
                modules: {
                    imageResize: {
                        displayStyles: { backgroundColor: 'black', border: 'none', color: 'white' },
                        modules: ['Resize', 'DisplaySize', 'Toolbar']
                    },
                    toolbar: {
                        container: toolbarOptions,
                        handlers: {
                            image: imageHandler
                        }
                    }
                }
            });

            // Safe loading of HTML into Quill
            const hiddenInput = document.getElementById('content-input');
            if (hiddenInput && hiddenInput.value) {
                quill.clipboard.dangerouslyPasteHTML(hiddenInput.value);
            }

            // 6. Form Synchronization
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function () {
                    const contentInput = document.getElementById('content-input');
                    if (contentInput) {
                        let html = quill.root.innerHTML;
                        if (html === '<p><br></p>') html = '';
                        contentInput.value = html;
                    }
                });
            }
        }

        // Lucide Icons Initialization
        lucide.createIcons();
    });
</script>

</body>

</html>