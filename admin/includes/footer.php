<?php
/**
 * Admin Footer & Script Initialization
 * 
 * Includes WYSIWYG editor (TinyMCE) and other admin scripts
 */
?>

    </main>

    <!-- TinyMCE Rich Text Editor -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: 'textarea.wysiwyg-editor',
            height: 400,
            theme: 'silver',
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'preview', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | code',
            automatic_uploads: true,
            file_picker_types: 'image',
            images_upload_url: '/admin/ajax/upload_image.php',
            images_upload_base_path: '/',
            images_reuse_filename: false,
            content_css: [
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            ],
            skin: 'oxide',
            content_style: `
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif; line-height: 1.6; }
                img { max-width: 100%; height: auto; }
            `,
            setup: function(editor) {
                // Custom styling for the editor
                editor.ui.registry.setIcon('code', '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M9.4 16.6L4.8 12l4.6-4.6-1.4-1.4L2 12l6 6 1.4-1.4zm5.2 0l4.6-4.6-4.6-4.6 1.4-1.4L22 12l-6 6-1.4-1.4z"></path></svg>');
            }
        });

        // Lucide Icons Initialization
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>

</body>
</html>
