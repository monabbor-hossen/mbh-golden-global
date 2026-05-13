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
            selector: '.wysiwyg-editor',
            menubar: false,
            height: 400,
            plugins: 'image link lists advlist',
            toolbar: 'bold italic underline | bullist numlist | alignleft aligncenter alignright | image',
            automatic_uploads: true,
            images_upload_url: 'ajax/upload_image.php',
            file_picker_types: 'image',
            images_reuse_filename: false,
            skin: 'oxide',
            content_style: `
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif; line-height: 1.6; }
                img { max-width: 100%; height: auto; }
            `,
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
                editor.on('init', function() {
                    var form = document.querySelector('form');
                    if (form) {
                        form.addEventListener('submit', function() {
                            editor.save();
                        });
                    }
                });
            }
        });

        // Lucide Icons Initialization
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>

</body>
</html>
