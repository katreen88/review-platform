<?php
$pageTitle = 'Home';
include __DIR__ . '/components/header.php';

$flash = get_flash();
?>

<div class="container">
    <div class="hero-card p-4 p-md-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge text-bg-primary mb-3">Phase 2 Ready</span>
                <h1 class="display-6 fw-bold mb-3">Discover and review websites in one clean platform.</h1>
                <p class="lead text-secondary mb-0">Admins add websites, users leave ratings, and everyone can browse community feedback.</p>
            </div>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form id="searchForm" class="row g-3">
                <div class="col-md-10">
                    <input
                        type="text"
                        name="q"
                        id="searchInput"
                        class="form-control"
                        placeholder="Search websites by title, URL, or description..."
                    >
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-dark">Search</button>
                </div>
            </form>
        </div>
    </div>

    <div id="websitesContainer">
        <p>Loading websites...</p>
    </div>
</div>

<script>
async function loadWebsites(query = '') {
    const container = document.getElementById('websitesContainer');
    container.innerHTML = '<p>Loading websites...</p>';

    try {
        const response = await fetch('api/get_websites.php?q=' + encodeURIComponent(query));
        const result = await response.json();

        if (!result.success) {
            container.innerHTML = '<p class="text-danger">Failed to load data.</p>';
            return;
        }

        const websites = result.data;

        if (websites.length === 0) {
            container.innerHTML = '<p>No websites found.</p>';
            return;
        }

        container.innerHTML = websites.map(site => `
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h3>${escapeHtml(site.title)}</h3>

                    <p>
                        <a href="${site.url}" target="_blank" rel="noopener noreferrer">
                            ${escapeHtml(site.url)}
                        </a>
                    </p>

                    <p>${escapeHtml(site.description ?? '')}</p>

                    <p><strong>⭐ Rating:</strong> ${Number(site.average_rating).toFixed(1)} / 5</p>
                    <p><strong>Reviews:</strong> ${site.review_count}</p>

                    <a href="website.php?id=${site.id}" class="btn btn-outline-dark btn-sm">
                        View Details
                    </a>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error(error);
        container.innerHTML = '<p class="text-danger">⚠️ Server error. Please try again.</p>';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

document.getElementById('searchForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const query = document.getElementById('searchInput').value.trim();
    loadWebsites(query);
});

let searchTimeout;

document.getElementById('searchInput').addEventListener('input', function () {
    clearTimeout(searchTimeout);

    const query = this.value.trim();

    searchTimeout = setTimeout(() => {
        loadWebsites(query);
    }, 400);
});

loadWebsites();
</script>

<?php include __DIR__ . '/components/footer.php'; ?>