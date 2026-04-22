<?php
$pageTitle = 'Add Website';
include __DIR__ . '/components/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h3 mb-4">Add Website</h1>

                    <div id="messageBox"></div>

                    <form id="addWebsiteForm">
                        <div class="mb-3">
                            <label for="title" class="form-label">Website Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="mb-3">
                            <label for="url" class="form-label">Website URL</label>
                            <input type="url" class="form-control" id="url" name="url" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Add Website</button>
                            <a href="index.php" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('addWebsiteForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    const messageBox = document.getElementById('messageBox');
    const formData = new FormData(form);

    messageBox.innerHTML = '<div class="alert alert-info">Submitting...</div>';

    try {
        const response = await fetch('api/add_website.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            messageBox.innerHTML = `<div class="alert alert-success">${escapeHtml(result.message)}</div>`;
            form.reset();

            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1000);
        } else {
            messageBox.innerHTML = `<div class="alert alert-danger">${escapeHtml(result.message)}</div>`;
        }
    } catch (error) {
        console.error(error);
        messageBox.innerHTML = '<div class="alert alert-danger">Something went wrong.</div>';
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}
</script>

<?php include __DIR__ . '/components/footer.php'; ?>