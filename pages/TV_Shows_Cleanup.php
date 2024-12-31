<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">TV Shows Cleanup Status</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" onclick="refreshTVShows()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-tv"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Shows</span>
                                    <span class="info-box-number" id="totalShows">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Recently Watched</span>
                                    <span class="info-box-number" id="recentlyWatched">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cleanup Pending</span>
                                    <span class="info-box-number" id="cleanupPending">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-trash"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Space to Free</span>
                                    <span class="info-box-number" id="spaceToFree">0 GB</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TV Shows List -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">TV Shows</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Show Name</th>
                                            <th>Episodes</th>
                                            <th>Size</th>
                                            <th>Last Watched</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="showsList">
                                        <!-- Shows will be inserted here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cleanup Confirmation Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Cleanup</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="cleanupDetails">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmCleanup()">Confirm Cleanup</button>
            </div>
        </div>
    </div>
</div>

<!-- Activity Log -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Activity Log</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="activityTable">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Show Name</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="activityLog">
                    <!-- Activity logs will be inserted here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let currentCleanupPath = null;

function refreshTVShows() {
    queryAPI("GET", "/plugin/plextvcleaner/shows")
        .done(function(data) {
            if (data["result"] == "Success") {
                const shows = data.data || [];
                localStorage.setItem('tvShows', JSON.stringify(shows));
                updateShowsList(shows);
                updateStats(shows);
            } else {
                toast("Error", "", data["message"] || "Failed to get TV shows", "danger", "30000");
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            toast("Error", "", "Failed to get TV shows: " + textStatus, "danger", "30000");
        });
}

function updateStats(shows) {
    const totalShows = shows.length;
    const recentlyWatched = shows.filter(show => 
        show.lastWatched && 
        show.lastWatched > Math.floor(Date.now() / 1000) - (plextvcleaner.tautulliMonths * 30 * 24 * 60 * 60)
    ).length;
    const cleanupPending = totalShows - recentlyWatched;
    const spaceToFree = shows.reduce((total, show) => total + (show.size || 0), 0);

    document.getElementById('totalShows').textContent = totalShows;
    document.getElementById('recentlyWatched').textContent = recentlyWatched;
    document.getElementById('cleanupPending').textContent = cleanupPending;
    document.getElementById('spaceToFree').textContent = formatSize(spaceToFree);
}

function updateShowsList(shows) {
    const tbody = document.getElementById('showsList');
    if (!tbody) {
        console.error('Shows list table body not found');
        return;
    }

    tbody.innerHTML = shows.map(show => {
        const lastWatched = formatDate(show.lastWatched);
        const isRecent = show.lastWatched && 
            show.lastWatched > Math.floor(Date.now() / 1000) - (plextvcleaner.tautulliMonths * 30 * 24 * 60 * 60);

        return `
            <tr>
                <td>${escapeHtml(show.name)}</td>
                <td>${show.episodeCount || 0}</td>
                <td>${formatSize(show.size || 0)}</td>
                <td>${lastWatched || 'Never'}</td>
                <td>
                    <span class="badge badge-${isRecent ? 'success' : 'warning'}">
                        ${isRecent ? 'Recent' : 'Cleanup Pending'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="analyzeShow('${escapeHtml(show.path)}')">
                        <i class="fas fa-search"></i> Analyze
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="showCleanupModal('${escapeHtml(show.path)}')">
                        <i class="fas fa-trash"></i> Cleanup
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function formatDate(timestamp) {
    if (!timestamp) return 'Never';
    const date = new Date(timestamp * 1000);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

function formatSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    refreshTVShows();
});
</script>