<?php
/**
 * Admin Footer & Script Initialization
 *
 * Closes the <main> and page shell opened by header.php,
 * loads Quill JS dependencies, and includes the consolidated admin.js.
 */
?>

</main>
</div>

<!-- Quill Rich Text Editor JS & Modules -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>window.Quill = Quill;</script>
<script src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>

<!-- Consolidated Admin JS (sidebar toggle, password eye, Quill init, Lucide) -->
<script src="/mbh-golden-global/assets/js/admin.js"></script>

</body>

</html>