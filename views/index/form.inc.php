<form id="request-form" class="form-horizontal">
    <div class="form-group form-group-sm">
        <label for="uri" class="col-sm-3 control-label">URI</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="uri" placeholder="http://"/>
        </div>
    </div>
    <div class="form-group form-group-sm">
        <label for="type" class="col-sm-3 control-label">Method</label>
        <div class="col-sm-9">
            <label class="radio-inline">
                <input type="radio" name="type" value="get" checked> GET
            </label>
            <label class="radio-inline">
                <input type="radio" name="type" id="type-post" value="post"> POST
            </label>
        </div>
    </div>
    <div class="form-group form-group-sm">
        <label for="placeholder" class="col-sm-3 control-label">Parameters</label>
        <div class="col-sm-9">
            <div id="placeholder-row" class="row param-row">
                <div class="col-sm-3">
                    <input class="form-control paramName" placeholder="paramName"/>
                </div>
                <div class="col-sm-9 input-group input-group-sm">
                    <input class="form-control paramValue" placeholder="paramValue"/>
                    <span class="input-group-btn"><button id="param-add-button" type="button"
                        class="btn btn-success" style="font-family: monospace">Add</button></span>
                </div>
                <br/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            <button id="submit-button" class="btn btn-primary btn-sm pull-left" type="submit"
                    name="send" value="true" data-loading-text="Loading..." style="margin-right: 10px;">Send</button>
            <div id="error-container" class="alert alert-sm alert-danger" style="overflow: hidden;display:none"></div>
            <div id="status-container" style="overflow: hidden;display:none"></div>
        </div>
    </div>
</form>
<div id="response-data" style="display:none">
    <h3>Response headers</h3>
    <table id="response-headers" class="table table-hover table-condensed">
        <thead>
            <tr>
                <th>Name</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    <h3>Response body</h3>
    <div>
        <pre id="response-container" style="max-height: 640px;overflow-y: scroll;"></pre>
    </div>
</div>