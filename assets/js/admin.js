/**
 * MBH Golden Global — Admin Panel JavaScript
 *
 * All admin-specific interactive logic, extracted from inline <script> blocks.
 * Loaded with `defer` so the DOM is ready; we still wrap in DOMContentLoaded
 * as a safety-net for any edge-cases.
 */

document.addEventListener('DOMContentLoaded', function () {


    /* ──────────────────────────────────────────────────────────
       2. Mobile Sidebar / Hamburger Toggle
       ────────────────────────────────────────────────────────── */
    const sidebar      = document.getElementById('mobile-sidebar');
    const overlay      = document.getElementById('sidebar-overlay');
    const hamburgerBtn = document.getElementById('hamburger-btn');

    function toggleSidebar() {
        if (sidebar)  sidebar.classList.toggle('-translate-x-full');
        if (overlay)  overlay.classList.toggle('hidden');
    }

    if (hamburgerBtn) hamburgerBtn.addEventListener('click', toggleSidebar);
    if (overlay)      overlay.addEventListener('click', toggleSidebar);


    /* ──────────────────────────────────────────────────────────
       3. Password Visibility Toggle (login.php & admins.php)
       ────────────────────────────────────────────────────────── */
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput     = document.getElementById('adminPassword');
    const iconShow          = document.getElementById('iconShow');
    const iconHide          = document.getElementById('iconHide');

    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            if (iconShow) iconShow.classList.toggle('hidden');
            if (iconHide) iconHide.classList.toggle('hidden');
        });
    }


    /* ──────────────────────────────────────────────────────────
       4. Quill WYSIWYG Editor Init (packages.php & stories.php)
       ────────────────────────────────────────────────────────── */
    const editorEl = document.getElementById('quill-editor');

    if (editorEl && typeof Quill !== 'undefined') {

        // ── Custom Image Handler (upload to server) ──────────
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
                        const range = quill.getSelection(true);
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

        // ── CustomImage format (preserves style/width/height on edit) ──
        var BaseImageFormat = Quill.import('formats/image');
        class CustomImage extends BaseImageFormat {
            static formats(domNode) {
                return {
                    alt:    domNode.getAttribute('alt'),
                    src:    domNode.getAttribute('src'),
                    style:  domNode.getAttribute('style'),
                    width:  domNode.getAttribute('width'),
                    height: domNode.getAttribute('height')
                };
            }
            format(name, value) {
                if (['alt', 'style', 'width', 'height'].indexOf(name)> -1) {
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

        // ── Toolbar config ───────────────────────────────────
        const toolbarOptions = [
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],

            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            [{ 'script': 'sub' }, { 'script': 'super' }],
            [{ 'indent': '-1' }, { 'indent': '+1' }],
            [{ 'direction': 'rtl' }],

            [{ 'size': ['small', false, 'large', 'huge'] }],
            [{ 'color': [false, '#000000', '#003355', '#0082CA', '#e60000', '#ff9900', '#ffff00', '#008a00', '#0066cc'] }, { 'background': [] }],
            [{ 'font': [] }],
            [{ 'align': [] }],

            ['clean'],
            ['link', 'image', 'video']
        ];

        // ── Initialize Quill ─────────────────────────────────
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

        // ── Load existing HTML into the editor ───────────────
        const hiddenInput = document.getElementById('content-input');
        if (hiddenInput && hiddenInput.value) {
            quill.clipboard.dangerouslyPasteHTML(hiddenInput.value);
        }

        // ── Sync editor content to hidden input on submit ────
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

});
