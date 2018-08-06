<?php
include('/usr/local/cpanel/php/cpanel.php');

require_once(__DIR__ . '/lib/MicroweberPluginController.php');


$cpanel = new CPANEL();
$controller = new MicroweberPluginController($cpanel);
echo $cpanel->header();

if ($_POST) {
    $action = $_POST['_action'];
    switch ($action) {
        case 'install':
            $admin = $controller->install();
            break;
        case 'uninstall':
            $controller->uninstall();
            break;
    }
}

if (isset($_GET['search']) && !$_GET['search']) {
    unset($_GET['search']);
}

$domaindata = $cpanel->uapi('DomainInfo', 'domains_data', array('format' => 'hash'));
$domaindata = $domaindata['cpanelresult']['result']['data'];
$allDomains = array_merge(array($domaindata['main_domain']), $domaindata['addon_domains'], $domaindata['sub_domains']);
?>

    <link rel="stylesheet" type="text/css" href="./microweber/index.css">

    <script>
        function advancedRadioChanged() {
            var express = document.forms[document.forms.length - 1].express.value;
            document.getElementById('advanced').style.display = express == '1' ? 'none' : 'block';
        }
        function askDelete() {
            return !confirm('Are you sure you want to delete this website?');
        }
    </script>

    <div class="microweber-plugin">
        <h1 class="page-header">
            <span class="page-icon"><img src="./microweber/logo.svg"></span>
            <span id="pageHeading">Manager</span>
        </h1>
        <div class="body-content">
            <div id="viewContent">
                <div class="row ng-scope" ng-show="viewDoneLoading">
                    <div class="col-xs-12 col-sm-8 col-md-6">
                        <p><strong>List of installed Microweber websites</strong></p>
                    </div>

                    <div class="col-xs-12 col-md-6 text-md-right text-lg-right pagination-controls">
                        <a id="btnInstall" class="btn btn-primary" title="Create a new Microweber installation." href="#install">Create new installation</a>
                        <button id="btnRefresh" class="btn btn-primary outline" title="Refresh the Microweber installations list." onclick="location.reload();">Refresh</button>
                    </div>

                    <div class="col-xs-12">
                        <form>
                            <div id="search-area" class="form-group">
                                <div class="row input-row">
                                    <div class="col-xs-12">
                                        <div class="input-group filter-controls">
                                            <input name="search" class="form-control ng-pristine ng-valid ng-touched" placeholder="Search" title="Type in your search filter." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                                            <span class="input-group-btn">
                                                <button id="search-button" type="submit" class="btn btn-default" ng-click="clearSearch()">
                                                    <span class="glyphicon glyphicon-search" ng-class="{ 'glyphicon-search': !list.meta.filterValue, 'glyphicon-remove': list.meta.filterValue }"></span>
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="instance-list">
                    <table class="table table-striped responsive-table">
                        <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Version</th>
                            <th>File Path</th>
                            <th class="text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $siteCount = 0; ?>
                        <?php foreach ($allDomains as $key => $domain): ?>
                            <?php
                            $mainDir = $domain['documentroot'];
                            $config = @include("$mainDir/config/microweber.php");
                            if (!$config) continue;
                            if (isset($_GET['search'])) {
                                if (strpos($domain['domain'], $_GET['search']) === false) continue;
                            }
                            $siteCount++;
                            ?>
                            <tr>
                                <td>
                                    <a href="//<?php echo $domain['domain']; ?>" target="_blank">
                                        <img src="./microweber/mw-icon.png" class="mw-icon"> <?php echo $domain['domain']; ?>
                                    </a>
                                </td>
                                <td><?php echo $config['version']; ?></td>
                                <td><?php echo $domain['documentroot']; ?></td>
                                <td class="action">
                                    <a href="#" class="update">Update</a>
                                    <a href="#" class="login">Login</a>

                                    <button type="button" class="remove" data-toggle="modal" data-target="#removeSite-<?php print $key; ?>"><i class="fa fa-trash"></i></button>

                                    <!-- Modal Delete Accept -->
                                    <div class="modal fade" id="removeSite-<?php print $key; ?>" tabindex="-1" role="dialog" aria-labelledby="removeSiteLabel">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-body" style="padding: 70px 0;">
                                                        <h4 class="modal-title text-center">Are you sure you want to delete this website?</h4>

                                                        <input type="hidden" name="_action" value="uninstall">
                                                        <input type="hidden" name="domain" value="<?php echo htmlspecialchars(json_encode($domain)); ?>">
                                                    </div>

                                                    <div class="modal-footer" style="margin: 0;">
                                                        <button type="button" class="btn btn-primary" data-dismiss="modal">No</button>
                                                        <button type="submit" class="btn btn-default">Yes, delete my website</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if (!isset($_GET['search']) && $siteCount == 0): ?>
                        <div id="row-no-instances" class="instance-list-callout callout callout-info">
                            <i class="fa fa-exclamation-circle"></i>
                            <span id="no-installation-msg" class="callout-message">
                            There is no Microweber installations yet.
                            <a id="addinstall" href="#install" title="Create a new Microweber installation.">Create an installation.</a>
                        </span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['search']) && $siteCount == 0): ?>
                        <div id="row-no-matches" class="instance-list-callout callout callout-info">
                            <i class="fa fa-exclamation-circle"></i>
                            No Microweber installations match your search criteria.
                        </div>
                    <?php endif; ?>

                    <?php if ($siteCount > 10): ?>
                        <div id="loading-callout-large-set" class="instance-list-callout callout callout-warning">
                            <i class="fa fa-exclamation-circle"></i>
                            This account contains many Microweber installations. Some operations may require more time.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row ng-scope" style="margin-top:20px;">
                    <div class="col-xs-12 text-md-right text-lg-right pagination-controls">

                        <div class="hidden-xs page-size" id="bottom-page-size" name="">
                            <div ng-hide="autoHide &amp;&amp; options[0].value >= totalItems" class="form-group">
                                <label for="bottom-page-size_select" class="title ng-binding">Page Size</label>
                                <select id="bottom-page-size_select" class="form-control ng-pristine ng-untouched ng-valid" ng-options="size.value as size.label for size in options" ng-model="pageSize" name="">
                                    <option value="number:10" label="10" selected="selected">10</option>
                                    <option value="number:20" label="20" selected="selected">20</option>
                                    <option value="number:50" label="50">50</option>
                                    <option value="number:100" label="100">100</option>
                                </select>
                            </div>
                        </div>

                        <ul class="pagination ng-isolate-scope" aria-label="Pagination" id="bottom-page-selector">
                            <li ng-if="boundaryLinks" ng-class="{disabled: noPrevious()}" class="ng-scope disabled">
                                <a id="bottom-page-selector_first" href="" ng-click="selectPage(1)" aria-label="Go to first page." class="ng-binding">
                                    &lt;&lt;
                                </a>
                            </li>
                            <li ng-if="directionLinks" ng-class="{disabled: noPrevious()}" class="ng-scope disabled">
                                <a id="bottom-page-selector_previous" href="" ng-click="selectPage(page - 1)" aria-label="Go to previous page." class="ng-binding">
                                    &lt;
                                </a>
                            </li>
                            <li ng-repeat="page in pages track by $index" ng-class="{active: page.active}" ng-switch="page.active" class="ng-scope active">
                                <a id="bottom-page-selector_1" href="" ng-click="selectPage(page.number)" aria-label="Go to page “1”." aria-current="page" ng-switch-when="true" class="ng-binding ng-scope">
                                    1
                                </a>
                            </li>
                            <li ng-if="directionLinks" ng-class="{disabled: noNext()}" class="ng-scope disabled">
                                <a id="bottom-page-selector_next" href="" ng-click="selectPage(page + 1)" aria-label="Go to next page." class="ng-binding">
                                    &gt;
                                </a>
                            </li>
                            <li ng-if="boundaryLinks" ng-class="{disabled: noNext()}" class="ng-scope disabled">
                                <a id="bottom-page-selector_last" href="" ng-click="selectPage(totalPages)" aria-label="Go to last page." class="ng-binding">
                                    &gt;&gt;
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST">
            <a name="install"></a>
            <input type="hidden" name="_action" value="install">

            <div id="row-no-matches" class="callout callout-silver">
                <strong>Select domain:</strong>

                <select name="domain" class="form-control inline-element" style="margin: 0 10px;">
                    <?php foreach ($allDomains as $domain): ?>
                        <option value="<?php echo htmlspecialchars(json_encode($domain)); ?>"><?php echo $domain['domain']; ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="pull-right" style="padding: 7px 0;">
                    <a href="./addon/index.html">Create new domain</a>
                    &nbsp; or &nbsp;
                    <a href="./subdomain/index.html">Create Sub-domain</a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-5">
                    <h4>Admin account details</h4>
                    <br>

                    <form class="form-horizontal">
                        <div class="form-group">
                            <label class="col-lg-3 control-label">Email</label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control" name="admin_email">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-lg-3 control-label">Username</label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control" name="admin_username">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-lg-3 control-label">Password</label>
                            <div class="col-lg-9">
                                <input type="password" class="form-control" name="admin_password">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-lg-3 control-label">Confirm Password</label>
                            <div class="col-lg-9">
                                <input type="password" class="form-control" name="admin_password_confirm">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-lg-3 control-label"></label>
                            <div class="col-lg-9">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="express" value="1" onclick="advancedRadioChanged()" checked>
                                        Use automatic database settings
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="express" onclick="advancedRadioChanged()" value="0">
                                        Manual database settings
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="advanced" style="display: none;">
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Database Driver:</label>
                                <div class="col-lg-9">
                                    <select name="db_driver" class="form-control">
                                        <option value="mysql">MySQL</option>
                                        <option value="sqlite">SQLite</option>
                                        <option value="pgsql">PostgreSQL</option>
                                        <option value="mssql">Microsoft SQL Server</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-3 control-label">Database Host</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control" name="db_host" value="localhost">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-3 control-label">Database Name</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control" name="db_name" value="<?php echo $controller->getDBPrefix(); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-3 control-label">Database Username</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control" name="db_username" value="<?php echo $controller->getDBPrefix(); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-3 control-label">Database Password</label>
                                <div class="col-lg-9">
                                    <input type="password" class="form-control" name="db_password">
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-right">
                            <div class="col-lg-12">
                                <button class="btn btn-lg btn-primary" type="submit">Finish the installation</button>
                            </div>
                        </div>

                        <div id="plugin-version-info">
                            <strong>Plugin:</strong> Microweber Manager - <span id="plugin-version">1.1.7-1</span>
                        </div>
                    </form>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>

<?php
echo $cpanel->footer();
$cpanel->end();
?>