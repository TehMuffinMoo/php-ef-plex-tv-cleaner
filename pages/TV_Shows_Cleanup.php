<?php
$plextvcleaner = new plextvcleaner();
$pluginConfig = $plextvcleaner->config->get('Plugins','Plex TV Cleaner');
if ($plextvcleaner->auth->checkAccess($pluginConfig['ACL-PLEXTVCLEANER'] ?? null) == false) {
    die();
}
return '
<div class="container-fluid">
    <div class="row m-1">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">TV Shows Cleanup Status</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="row m-1">
        <!-- TV Shows Card -->
        <div class="col-lg-3 col-md-4 col-sm-6 col-12">
            <div class="card info-card tv-shows-card">
                <div class="card-body info-box bg-info">
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fas fa-tv mb-2"></i>&nbsp;
                            <h5 class="card-title">TV Shows: </h5>
                        </div>
                        <div class="pt-1 ps-3">
                            <h6 id="totalShows" class="metric-circle border-5">N/A</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Recently Watched Card -->
        <div class="col-lg-3 col-md-4 col-sm-6 col-12">
            <div class="card info-card tv-shows-card">
                <div class="card-body info-box bg-success">
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fas fa-check mb-2"></i>&nbsp;
                            <h5 class="card-title">Recently Watched: </h5>
                        </div>
                        <div class="pt-1 ps-3">
                            <h6 id="recentlyWatched" class="metric-circle border-5">N/A</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Cleanup Pending Card -->
        <div class="col-lg-3 col-md-4 col-sm-6 col-12">
            <div class="card info-card tv-shows-card">
                <div class="card-body info-box bg-warning">
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fas fa-clock mb-2"></i>&nbsp;
                            <h5 class="card-title">Cleanup Pending: </h5>
                        </div>
                        <div class="pt-1 ps-3">
                            <h6 id="cleanupPending" class="metric-circle border-5">N/A</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Space To Free Card -->
        <div class="col-lg-3 col-md-4 col-sm-6 col-12">
            <div class="card info-card tv-shows-card">
                <div class="card-body info-box bg-danger">
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fas fa-trash mb-2"></i>&nbsp;
                            <h5 class="card-title">Space to Free: </h5>
                        </div>
                        <div class="pt-1 ps-3">
                            <h6 id="spaceToFree" class="metric-circle border-5">N/A</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <!-- TV Shows List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">TV Shows</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table data-url="/api/plugin/plextvcleaner/tautulli/tvshows"
                    data-toggle="table"
                    data-search="true"
                    data-filter-control="true"
                    data-filter-control-visible="false"
                    data-show-filter-control-switch="true"
                    data-show-refresh="true"
                    data-show-columns="true"
                    data-show-
                    data-pagination="true"
                    data-toolbar="#toolbar"
                    data-page-size="25"
                    data-response-handler="tautulliResponseHandler"
                    class="table table-striped" id="tvShowsTable">

                    <thead>
                    <tr>
                        <th data-field="state" data-checkbox="true"></th>
                        <th data-field="title" data-sortable="true" data-filter-control="input">Show Name</th>
                        <th data-field="last_played" data-sortable="true" data-formatter="tautulliLastWatchedFormatter" data-filter-control="input">Last Watched</th>
                        <th data-field="play_count" data-sortable="true" data-filter-control="input">Play Count</th>
                        <th data-field="library_name" data-sortable="true" data-filter-control="select">Library</th>
                    </tr>
                    </thead>
                </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <!-- Activity Log Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Activity Log</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
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
';