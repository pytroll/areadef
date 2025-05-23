<?php
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
    $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $location);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PyTroll Area Definition</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@9.1.0/ol.css" type="text/css">

    <style>
        body { font-family: Arial, sans-serif; margin: 10px; }
        .form_container {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }
        .form_container h2, .form_container h3 {
            margin-top: 0;
            color: #333;
        }
        .form_description p { font-size: 0.9em; color: #555; }
        ul { list-style-type: none; padding: 0; }
        li { margin-bottom: 10px; }
        label.description, .coord-inputs label {
            display: block;
            font-weight: bold;
            margin-bottom: 3px;
            color: #444;
        }
        input[type=text], input[type=number], input.element.text {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
            box-sizing: border-box;
        }
        input.medium { width: 250px; }
        input.large { width: 350px; }
        input.small { width: 100px; }
        .coord-inputs input { width: 120px; margin-right:10px; margin-bottom: 5px;}

        input[type=radio] { margin-right: 5px; }
        label.choice { font-weight: normal; margin-right: 15px; }

        button {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 5px;
        }
        button:hover { background-color: #0056b3; }

        /* MODIFIED: Added .hidden-stere-params */
        .hidden-tmerc-params, .hidden-stere-params, .modal-hidden { display: none; }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 25px;
            border: 1px solid #bbb;
            border-radius: 5px;
            width: 90%;
            max-width: 550px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .modal-content p { margin-top: 0; }
        .modal-content label { display: block; margin-top:10px; font-weight:normal; }
        .modal-content input[type=text], .modal-content input[type=number] {
            width: calc(100% - 22px); /* padding + border */
        }
        .modal-content div { margin-top: 15px; text-align: right; } /* For buttons */

        #result {
            font-family: monospace;
            white-space: pre;
            border: 1px solid #eee;
            background-color: #fff;
            padding: 10px;
            min-height: 50px;
            margin-top: 10px;
        }
        #statusMessage { font-size: 0.9em; color: navy; margin-top: 5px; min-height: 1.2em;}
        .info-note { font-size: 0.85em; color: #666; margin-top:10px;}
        .map { border: 1px solid black; }
    </style>
</head>
<body>

    <div class="form_container">
        <form id="mainForm">
            <div class="form_description">
                <h2>PyTroll Area Definition Tool</h2>
                <p>Define projection, then draw extent (Shift+Drag) or input Lat/Lon coordinates.</p>
            </div>
            <ul>
                <li>
                    <label class="description" for="element_2a">Region name (used for YAML key and filename)</label>
                    <div>
                        <input id="element_2a" name="element_2a" class="element text medium" type="text" maxlength="255" value="my_area"/>
                    </div>
                </li>
                <li id="li_1">
                    <label id="prjSelector" class="description" for="element_1">Projection type</label>
                    <span>
                        <input id="element_1_1" name="element_1" class="element radio" type="radio" value="1" checked /><label class="choice" for="element_1_1">proj=stere (Stereographic - customizable)</label><br>
                        <input id="element_1_2" name="element_1" class="element radio" type="radio" value="2"/><label class="choice" for="element_1_2">proj=eqc, ellps=WGS84, units=m</label><br>
                        <input id="element_1_3" name="element_1" class="element radio" type="radio" value="3"/><label class="choice" for="element_1_3">proj=tmerc (Transverse Mercator - customizable)</label>
                    </span>
                </li>
                <li id="li_stere_lat0" class="hidden-stere-params">
                    <label class="description" for="stere_lat_0">Stere: Latitude of Origin (lat_0)</label>
                    <div><input id="stere_lat_0" name="stere_lat_0" class="element text small" type="number" step="any" value="45.0"/></div>
                </li>
                <li id="li_stere_lon0" class="hidden-stere-params">
                    <label class="description" for="stere_lon_0">Stere: Center Longitude (lon_0)</label>
                    <div><input id="stere_lon_0" name="stere_lon_0" class="element text small" type="number" step="any" value="10.0"/></div>
                </li>
                <li id="li_stere_lat_ts" class="hidden-stere-params">
                    <label class="description" for="stere_lat_ts">Stere: Latitude of True Scale (lat_ts)</label>
                    <div><input id="stere_lat_ts" name="stere_lat_ts" class="element text small" type="number" step="any" value="45.0"/></div>
                </li>

                <li id="li_tmerc_lat0" class="hidden-tmerc-params">
                    <label class="description" for="tmerc_lat_0">TMerc: Latitude of Origin (lat_0)</label>
                    <div><input id="tmerc_lat_0" name="tmerc_lat_0" class="element text small" type="number" step="any" value="0"/></div>
                </li>
                <li id="li_tmerc_lon0" class="hidden-tmerc-params">
                    <label class="description" for="tmerc_lon_0">TMerc: Central Meridian (lon_0)</label>
                    <div><input id="tmerc_lon_0" name="tmerc_lon_0" class="element text small" type="number" step="any" value="0"/></div>
                </li>
                <li id="li_tmerc_k0" class="hidden-tmerc-params">
                    <label class="description" for="tmerc_k_0">TMerc: Scale Factor (k_0)</label>
                    <div><input id="tmerc_k_0" name="tmerc_k_0" class="element text small" type="number" step="any" value="0.9996"/></div>
                </li>
                <li id="li_tmerc_lat_ts" class="hidden-tmerc-params">
                    <label class="description" for="tmerc_lat_ts">TMerc: Latitude of True Scale (lat_ts) (default: lat_0)</label>
                    <div><input id="tmerc_lat_ts" name="tmerc_lat_ts" class="element text small" type="number" step="any" placeholder="value of lat_0"/></div>
                </li>
                <li>
                    <h3>Define Extent by Geographic Coordinates (EPSG:4326)</h3>
                    <div class="coord-inputs">
                        <span><label for="latMin">Latitude Min:</label><input type="number" id="latMin" step="any" placeholder="-90 to 90"></span>
                        <span><label for="latMax">Latitude Max:</label><input type="number" id="latMax" step="any" placeholder="-90 to 90"></span>
                    </div>
                    <div class="coord-inputs">
                        <span><label for="lonMin">Longitude Min:</label><input type="number" id="lonMin" step="any" placeholder="-180 to 180"></span>
                        <span><label for="lonMax">Longitude Max:</label><input type="number" id="lonMax" step="any" placeholder="-180 to 180"></span>
                    </div>
                </li>
                <li id="li_2">
                    <label class="description" for="element_2">Projection ID (User defined ID)</label>
                    <div><input id="element_2" name="element_2" class="element text medium" type="text" maxlength="255" value="custom_id"/></div>
                </li>
                <li id="li_3">
                    <label class="description" for="element_3">Projection description (User defined text)</label>
                    <div><input id="element_3" name="element_3" class="element text large" type="text" maxlength="255" value="My custom area description"/></div>
                </li>
                <li id="li_4">
                    <label class="description" for="element_4">Desired pixel size (image resolution) in meters</label>
                    <div><input id="element_4" name="element_4" class="element text small" type="text" maxlength="255" value="1000"/></div>
                </li>
            </ul>
            <button type="button" id="applyLatLonExtentButton" style="margin-top:10px;">Apply Lat/Lon Extent</button>
        </form>
    </div>

    <div class="form_container">
        <div class="form_description">
                <h2>Result Preview</h2>
                <button type="button" id="saveButton">Save Area Definition...</button>
                <button type="button" id="cancelExtentButton">Cancel Current Extent</button>
        </div>
        <p id="result"></p>
        <p id="statusMessage"></p>
        <p class="info-note">Note: 'Save' triggers a file download. The browser handles the save location and overwrite warnings for existing files. The 'Rename' option within the save dialog changes the region name in the definition *before* generating the file to be saved.</p>
    </div>

    <div id="confirmSaveModal" class="modal-overlay">
      <div class="modal-content">
        <p id="confirmSaveMessage"></p>
        <div id="confirmSaveActions">
            <button type="button" id="confirmSaveOkButton">Save</button>
            <button type="button" id="confirmSaveRenameButton">Rename & Save</button>
            <button type="button" id="confirmSaveCancelButton">Cancel</button>
        </div>
        <div id="renameFileSection" class="modal-hidden">
            <label for="newRegionNameForFileInput">Enter new region name (for filename and YAML key):</label>
            <input type="text" id="newRegionNameForFileInput">
            <div>
                <button type="button" id="saveWithNewNameButton">Save with This Name</button>
                <button type="button" id="cancelRenameFileButton">Cancel Rename</button>
            </div>
        </div>
      </div>
    </div>

    <div id="renameRegionModal" class="modal-overlay">
        <div class="modal-content">
            <p id="renameRegionMessage"></p>
            <label for="newRegionNameForDefinitionInput">Region Name for New Area:</label>
            <input type="text" id="newRegionNameForDefinitionInput">
            <div>
                <button type="button" id="confirmNewRegionNameButton">Define Area with this Name</button>
                <button type="button" id="cancelNewRegionDefinitionButton">Cancel New Definition</button>
            </div>
        </div>
    </div>

    <div style="clear: both; padding: 10px 0;">Use <strong>Ctrl+Drag</strong> on the map to draw an extent, or input Lat/Lon coordinates above.</div>
    <div id="map" class="map" style="width: 70%; height: 500px; margin: 0 auto;"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.11.0/proj4.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/ol@9.1.0/dist/ol.js"></script>

    <script>
    $(document).ready(function() { // Ensure DOM is ready

        // --- Proj4 and OpenLayers Setup ---
        proj4.defs('EPSG:4326', '+proj=longlat +datum=WGS84 +no_defs');
        // MODIFIED: Removed static 'npol' definition here. It will be created dynamically.
        proj4.defs('eqc',  '+proj=eqc +ellps=WGS84 +units=m +no_defs');
        ol.proj.proj4.register(proj4);

        var extentEqc = [-20037508.34, -10018754.17, 20037508.34, 10018754.17];
        var extentStere = [-12000000, -12000000, 12000000, 12000000]; // Generic extent for stereographic
        var extentTmerc = [-5000000, -10000000, 5000000, 10000000];

        var prjToUseCode = ''; // MODIFIED: Will be set by applyProjectionChange, e.g., "custom_stere_..." or "custom_tmerc_..."
        var mapProjection; // Will be set by applyProjectionChange

        var map = new ol.Map({
            layers: [ new ol.layer.Tile({ source: new ol.source.OSM() }) ],
            target: 'map',
            // MODIFIED: Start with a generic EPSG:4326 view, applyProjectionChange will set the correct one.
            view: new ol.View({ projection: ol.proj.get('EPSG:4326'), center: [0, 0], zoom: 1 })
        });

        var extentInteraction = new ol.interaction.Extent({ condition: ol.events.condition.platformModifierKeyOnly });
        map.addInteraction(extentInteraction);
        extentInteraction.setActive(false);

        var lastYamlOutput = "";
        var isNewAreaActionPending = false;
        var actionAfterRenamePrompt = null;

        // --- Event Listeners ---
        $(window).on('keydown', function(event) {
            if (event.key === 'Shift' || event.keyCode === 17) { extentInteraction.setActive(true); }
        });
        $(window).on('keyup', function(event) {
            if (event.key === 'Shift' || event.keyCode === 17) {
                extentInteraction.setActive(false);
                var drawnExtent = extentInteraction.getExtent();
                if (drawnExtent) {
                    isNewAreaActionPending = true;
                    promptForRegionNameChange(function() {
                        updateLatLonInputsFromExtent(drawnExtent);
                        updateArea();
                    });
                }
            }
        });

        $("input[type=radio][name='element_1']").change(function() {
            isNewAreaActionPending = true;
            clearDrawnExtentOnMap();
            setStatusMessage("Projection changed. Define new extent for this projection.");
            applyProjectionChange();
        });

        $('#applyLatLonExtentButton').on('click', function() {
            isNewAreaActionPending = true;
            promptForRegionNameChange(processLatLonExtentInputs);
        });

        $('#cancelExtentButton').on('click', function() {
            isNewAreaActionPending = true;
            clearDrawnExtentOnMap(); // This will now also clear YAML
            setStatusMessage("Current extent cleared. Define new extent if needed. Previous YAML (if any) is cleared.");
        });

        // MODIFIED: Combined event listeners for TMerc AND STERE parameters
        $('#tmerc_lat_0, #tmerc_lon_0, #tmerc_k_0, #tmerc_lat_ts, #stere_lat_0, #stere_lon_0, #stere_lat_ts').on('change input', function() {
            var selectedPrjValue = $("input[name='element_1']:checked").val();
            // Only act if the currently selected projection's parameters are being changed
            if ((selectedPrjValue === "3" && $(this).attr('id').startsWith('tmerc')) ||
                (selectedPrjValue === "1" && $(this).attr('id').startsWith('stere'))) {
                isNewAreaActionPending = true;
                clearDrawnExtentOnMap();
                setStatusMessage("Projection parameters changed. Map view updated. Redefine extent if necessary.");
                applyProjectionChange(); // This will now handle both tmerc and stere custom params
            }
        });


        // --- Core Logic Functions ---
        function promptForRegionNameChange(callback) {
            actionAfterRenamePrompt = callback;
            if (isNewAreaActionPending && ($('#element_2a').val().trim() !== "" || lastYamlOutput !== "")) {
                var currentRegionName = $('#element_2a').val().trim() || "my_area";
                $('#renameRegionMessage').text(`The current region name is '${currentRegionName}'. Change it for the new area definition?`);
                $('#newRegionNameForDefinitionInput').val(currentRegionName);
                $('#renameRegionModal').show();
            } else {
                if (typeof callback === 'function') { callback(); }
                isNewAreaActionPending = false; // Reset here if not prompting
            }
        }

        $('#confirmNewRegionNameButton').on('click', function() {
            var newName = $('#newRegionNameForDefinitionInput').val().trim();
            if (newName) { $('#element_2a').val(newName); }
            $('#renameRegionModal').hide();
            if (typeof actionAfterRenamePrompt === 'function') { actionAfterRenamePrompt(); }
            isNewAreaActionPending = false; actionAfterRenamePrompt = null;
        });

        $('#cancelNewRegionDefinitionButton').on('click', function() {
            $('#renameRegionModal').hide();
            isNewAreaActionPending = false; actionAfterRenamePrompt = null;
            setStatusMessage("New area definition cancelled. Previous YAML (if any) is still shown.");
        });

        function processLatLonExtentInputs() {
            var latMin = parseFloat($('#latMin').val()); var latMax = parseFloat($('#latMax').val());
            var lonMin = parseFloat($('#lonMin').val()); var lonMax = parseFloat($('#lonMax').val());

            if (isNaN(latMin) || isNaN(latMax) || isNaN(lonMin) || isNaN(lonMax)) { alert("Please enter valid numbers for all Lat/Lon coordinates."); return; }
            if (latMin >= latMax || lonMin >= lonMax) { alert("Min values must be less than Max values for Lat/Lon."); return; }
            if (latMin < -90 || latMax > 90 || lonMin < -180 || lonMax > 180) { alert("Latitude must be between -90 and 90, Longitude between -180 and 180."); return; }

            // Use the CURRENT prjToUseCode which is updated by applyProjectionChange
            var targetProjectionCodeForTransform = prjToUseCode;
            try {
                var projectedMin = proj4('EPSG:4326', targetProjectionCodeForTransform, [lonMin, latMin]);
                var projectedMax = proj4('EPSG:4326', targetProjectionCodeForTransform, [lonMax, latMax]);
                var newExtent = [projectedMin[0], projectedMin[1], projectedMax[0], projectedMax[1]];
                extentInteraction.setExtent(newExtent); // This sets the extent on the interaction
                // Visually update the map to fit this new extent
                map.getView().fit(newExtent, { size: map.getSize(), duration: 500, padding: [50,50,50,50] });
                updateArea(); // This will generate YAML
            } catch (e) {
                alert("Error during coordinate transformation: " + e.message + "\nUsing projection: " + targetProjectionCodeForTransform + "\nEnsure projection parameters are correctly set.");
                console.error("Transformation error: ", e, "Target Projection: ", targetProjectionCodeForTransform, proj4.defs[targetProjectionCodeForTransform]);
            }
        }

        function updateLatLonInputsFromExtent(mapExtent) {
            if (!mapExtent || !mapProjection) return;
            var sourceProjectionCodeForTransform = mapProjection.getCode(); // Use current map projection's code
            try {
                var geoMin = proj4(sourceProjectionCodeForTransform, 'EPSG:4326', [mapExtent[0], mapExtent[1]]);
                var geoMax = proj4(sourceProjectionCodeForTransform, 'EPSG:4326', [mapExtent[2], mapExtent[3]]);
                $('#lonMin').val(geoMin[0].toFixed(6)); $('#latMin').val(geoMin[1].toFixed(6));
                $('#lonMax').val(geoMax[0].toFixed(6)); $('#latMax').val(geoMax[1].toFixed(6));
            } catch (e) { console.error("Error transforming extent to Lat/Lon: ", e); clearLatLonInputs(); }
        }

        function clearLatLonInputs() { $('#latMin, #latMax, #lonMin, #lonMax').val(''); }

        // ADDED: Function to get Stereographic projection string from input fields
        function getStereProjString() {
            var lat_0_val = parseFloat($('#stere_lat_0').val());
            var lon_0_val = parseFloat($('#stere_lon_0').val());
            var lat_ts_val = parseFloat($('#stere_lat_ts').val());

            // Basic validation or default values
            if (isNaN(lat_0_val)) lat_0_val = 90.0; // Default for North Pole Stereographic
            if (isNaN(lon_0_val)) lon_0_val = 0.0;
            if (isNaN(lat_ts_val)) lat_ts_val = lat_0_val; // Often lat_ts is same as lat_0 or a specific standard parallel

            return `+proj=stere +lat_0=${lat_0_val} +lon_0=${lon_0_val} +lat_ts=${lat_ts_val} +x_0=0 +y_0=0 +ellps=WGS84 +units=m +no_defs`;
        }

        function getTmercProjString() {
            var lat_0_val = parseFloat($('#tmerc_lat_0').val()) || 0;
            var lon_0_val = parseFloat($('#tmerc_lon_0').val()) || 0;
            var k_0_val = parseFloat($('#tmerc_k_0').val()) || 0.9996;
            var lat_ts_input_val = $('#tmerc_lat_ts').val();
            var lat_ts_val;
            if (lat_ts_input_val === "" || isNaN(parseFloat(lat_ts_input_val))) {
                lat_ts_val = lat_0_val;
            } else {
                lat_ts_val = parseFloat(lat_ts_input_val);
            }
            return `+proj=tmerc +lat_0=${lat_0_val} +lon_0=${lon_0_val} +k_0=${k_0_val} +lat_ts=${lat_ts_val} +ellps=WGS84 +units=m +no_defs`;
        }

        function generateYamlOutput() {
            var currentExtent = extentInteraction.getExtent();
            if (!currentExtent) return ""; // No extent, no YAML
            var regionName = $('#element_2a').val().trim() || "my_area";
            var regionKey = regionName.replace(/\s+/g, '_').toLowerCase();
            var description = $('#element_3').val() || "Area description";
            var proj_id_val = $('#element_2').val() || "custom_id"; // Renamed to avoid conflict
            var pixelSize = parseFloat($('#element_4').val()) || 1000;
            var xsize = Math.round(Math.abs(currentExtent[2] - currentExtent[0]) / pixelSize);
            var ysize = Math.round(Math.abs(currentExtent[3] - currentExtent[1]) / pixelSize);
            var yamlParams = "";
            var selectedPrjValue = $("input[name='element_1']:checked").val();

            if (selectedPrjValue === "1") { // MODIFIED: Stereographic (customizable)
                var lat_0 = parseFloat($('#stere_lat_0').val());
                var lon_0 = parseFloat($('#stere_lon_0').val());
                var lat_ts = parseFloat($('#stere_lat_ts').val());
                // Ensure valid numbers, fallback to defaults if necessary (though getStereProjString handles some)
                if (isNaN(lat_0)) lat_0 = 45.0;
                if (isNaN(lon_0)) lon_0 = 10.0;
                if (isNaN(lat_ts)) lat_ts = lat_0;

                yamlParams = `\n    proj: stere\n    lat_0: ${lat_0}\n    lon_0: ${lon_0}\n    lat_ts: ${lat_ts}\n    ellps: WGS84\n    units: m`;
            } else if (selectedPrjValue === "3") { // TMerc
                var lat_0_tmerc = parseFloat($('#tmerc_lat_0').val()) || 0;
                var lon_0_tmerc = parseFloat($('#tmerc_lon_0').val()) || 0;
                var k_0_tmerc = parseFloat($('#tmerc_k_0').val()) || 0.9996;
                var lat_ts_input_tmerc = $('#tmerc_lat_ts').val();
                var lat_ts_tmerc = (lat_ts_input_tmerc === "" || isNaN(parseFloat(lat_ts_input_tmerc))) ? lat_0_tmerc : parseFloat(lat_ts_input_tmerc);
                yamlParams = `\n    proj: tmerc\n    lat_0: ${lat_0_tmerc}\n    lon_0: ${lon_0_tmerc}\n    k_0: ${k_0_tmerc}\n    lat_ts: ${lat_ts_tmerc}\n    ellps: WGS84\n    units: m`;
            } else { // EQC (value "2")
                 var prjDefText = $("input[name='element_1']:checked + label").text();
                 var params = {};
                 prjDefText.split(',').forEach(part => {
                     var kv = part.split('=');
                     if (kv.length === 2) {
                         params[kv[0].trim()] = kv[1].trim();
                     }
                 });
                 for (const key in params) {
                     yamlParams += `\n    ${key}: ${params[key]}`;
                 }
            }
            return `${regionKey}:\n  description: ${description}\n  projection_id: ${proj_id_val}\n  projection:${yamlParams}\n  shape:\n    width: ${xsize}\n    height: ${ysize}\n  area_extent:\n    lower_left_xy: [${currentExtent[0].toFixed(2)}, ${currentExtent[1].toFixed(2)}]\n    upper_right_xy: [${currentExtent[2].toFixed(2)}, ${currentExtent[3].toFixed(2)}]`;
        }

        function updateArea() {
            var currentExtent = extentInteraction.getExtent();
            if (!currentExtent) {
                setStatusMessage("Define an extent on the map (Ctrl+Drag or Lat/Lon inputs).");
                $('#result').html(""); // Clear previous YAML if extent is now invalid
                lastYamlOutput = "";
                return false;
            }
            lastYamlOutput = generateYamlOutput();
            $('#result').html(lastYamlOutput);
            setStatusMessage("Area definition updated. Save or define a new area.");
            isNewAreaActionPending = false; // Reset after successful update
            return true;
        }

        /**
         * Applies selected projection to the map, updates view, and handles TMerc/Stere centering.
         * Uses unique projection codes for dynamic TMerc/Stere definitions.
         */
        function applyProjectionChange() {
            var prjNr = $("input[name='element_1']:checked").val();
            var newViewConfig = { center: [0,0], zoom: 2 };
            var newMaxExtentForView;
            var tmercParamSelectors = '#li_tmerc_lat0, #li_tmerc_lon0, #li_tmerc_k0, #li_tmerc_lat_ts';
            var stereParamSelectors = '#li_stere_lat0, #li_stere_lon0, #li_stere_lat_ts';

            // Hide all specific param sections first
            $(tmercParamSelectors).addClass('hidden-tmerc-params');
            $(stereParamSelectors).addClass('hidden-stere-params');

            if (prjNr == "1") { // Stereographic (customizable)
                $(stereParamSelectors).removeClass('hidden-stere-params'); // Show stere params
                prjToUseCode = "custom_stere_" + new Date().getTime(); // Unique code for this instance
                var stereString = getStereProjString(); // Get definition from input fields
                proj4.defs(prjToUseCode, stereString);
                ol.proj.proj4.register(proj4);

                mapProjection = ol.proj.get(prjToUseCode);
                if (!mapProjection) { // Should be found after register
                    mapProjection = new ol.proj.Projection({ code: prjToUseCode, units: 'm', extent: extentStere });
                    ol.proj.addProjection(mapProjection); // Add if somehow not registered by ol.proj.proj4.register
                } else {
                    mapProjection.setExtent(extentStere); // Ensure extent is (re)set
                }

                var lon_0_val_stere = parseFloat($('#stere_lon_0').val()) || 0; // Default to 0 for centering if NaN
                var lat_0_val_stere = parseFloat($('#stere_lat_0').val()) || 0; // Default to 0 for centering if NaN
                 try {
                    // Transform [lon_0, lat_0] from geographic to the unique Stereographic projection for centering
                    newViewConfig.center = proj4('EPSG:4326', prjToUseCode, [lon_0_val_stere, lat_0_val_stere]);
                } catch (e) {
                    console.error("Error transforming stere center for view:", e, "Using projection code:", prjToUseCode);
                    newViewConfig.center = [0,0]; // Fallback center
                }
                newMaxExtentForView = extentStere; newViewConfig.zoom = 2; // Generic zoom for stere

            } else if (prjNr == "2") { // Equidistant Cylindrical
                prjToUseCode = "eqc"; // Use the static predefined code
                mapProjection = ol.proj.get(prjToUseCode);
                newMaxExtentForView = extentEqc; newViewConfig.zoom = 1; newViewConfig.center = [0,0]; // EQC is global

            } else if (prjNr == "3") { // Transverse Mercator
                $(tmercParamSelectors).removeClass('hidden-tmerc-params');
                prjToUseCode = "custom_tmerc_" + new Date().getTime();
                var tmercString = getTmercProjString();
                proj4.defs(prjToUseCode, tmercString);
                ol.proj.proj4.register(proj4);

                mapProjection = ol.proj.get(prjToUseCode);
                if (!mapProjection) {
                    mapProjection = new ol.proj.Projection({ code: prjToUseCode, units: 'm', extent: extentTmerc });
                    ol.proj.addProjection(mapProjection);
                } else {
                    mapProjection.setExtent(extentTmerc);
                }

                var lat_0_val_tmerc = parseFloat($('#tmerc_lat_0').val()) || 0;
                var lon_0_val_tmerc = parseFloat($('#tmerc_lon_0').val()) || 0;
                try {
                    newViewConfig.center = proj4('EPSG:4326', prjToUseCode, [lon_0_val_tmerc, lat_0_val_tmerc]);
                } catch (e) {
                    console.error("Error transforming tmerc center for view:", e, "Using projection code:", prjToUseCode);
                    newViewConfig.center = [0,0];
                }
                newMaxExtentForView = extentTmerc; newViewConfig.zoom = 6; // Typical zoom for TMerc
            }

            if (!mapProjection && prjToUseCode) {
                 mapProjection = ol.proj.get(prjToUseCode); // Final attempt to get projection
            } else if (!mapProjection) { // Absolute fallback
                 console.warn("Map projection could not be determined, defaulting to EPSG:4326");
                 mapProjection = ol.proj.get('EPSG:4326');
                 prjToUseCode = 'EPSG:4326'; // Ensure prjToUseCode reflects this fallback
                 newViewConfig.center = [0,0]; newViewConfig.zoom = 1;
            }


            var newView = new ol.View({
                projection: mapProjection, center: newViewConfig.center,
                zoom: newViewConfig.zoom, extent: newMaxExtentForView
            });
            map.setView(newView);

            // Re-initialize extent interaction for the new projection
            map.removeInteraction(extentInteraction); // Remove old one
            extentInteraction = new ol.interaction.Extent({ condition: ol.events.condition.platformModifierKeyOnly });
            map.addInteraction(extentInteraction);
            extentInteraction.setActive(false); // Keep it inactive until Shift is pressed
        }

        function clearDrawnExtentOnMap() {
            extentInteraction.setExtent(undefined);
            clearLatLonInputs();
            $('#result').html(""); // Clear YAML output
            lastYamlOutput = "";  // Clear stored YAML
        }
        function setStatusMessage(message) { $('#statusMessage').text(message); }

        function triggerDownload(regionNameForFile) {
            // Ensure YAML is up-to-date with the LATEST region name if changed during save prompt
            $('#element_2a').val(regionNameForFile); // Ensure the input field has the name being used for the file
            var yamlContentToSave = generateYamlOutput(); // Regenerate with potentially new name

            if (!yamlContentToSave) { alert("Could not generate YAML content to save. Please ensure an extent is defined."); return; }
            var filename = regionNameForFile.replace(/\s+/g, '_').toLowerCase() + ".yaml";
            var blob = new Blob([yamlContentToSave], { type: 'text/yaml;charset=utf-8' });
            var link = document.createElement("a"); var url = URL.createObjectURL(blob);
            link.setAttribute("href", url); link.setAttribute("download", filename);
            link.style.visibility = 'hidden'; document.body.appendChild(link);
            link.click(); document.body.removeChild(link); URL.revokeObjectURL(url);
            // Don't clear extent or YAML automatically on save, user might want to save with another name or continue
            setStatusMessage(`Area '${regionNameForFile}' saved as '${filename}'. You can continue editing or define a new area.`);
            lastYamlOutput = yamlContentToSave; // Keep the successfully saved YAML visible
            $('#result').html(lastYamlOutput); // Display the saved YAML
        }

        var saveModal = $('#confirmSaveModal');
        $('#saveButton').on('click', function() {
            // First, ensure area is up-to-date and valid
            if (!updateArea()) { // updateArea will generate YAML and return false if no extent
                // updateArea already shows a status message if no extent
                if(!extentInteraction.getExtent()){
                    alert("Please define an area extent on the map or via Lat/Lon inputs first.");
                } else {
                    alert("Could not generate area definition. Please check your inputs and extent.");
                }
                return;
            }
            // If updateArea was successful, lastYamlOutput will be populated
            if(!lastYamlOutput){
                alert("No area definition available to save. Please define an extent.");
                return;
            }

            var currentRegionName = $('#element_2a').val().trim() || "my_area";
            var currentFilename = currentRegionName.replace(/\s+/g, '_').toLowerCase() + ".yaml";
            $('#confirmSaveMessage').text(`Save area definition for '${currentRegionName}' as '${currentFilename}'?`);
            $('#renameFileSection').addClass('modal-hidden');
            $('#confirmSaveActions').removeClass('modal-hidden');
            saveModal.show();
        });
        $('#confirmSaveOkButton').on('click', function() {
            var regionName = $('#element_2a').val().trim() || "my_area";
            triggerDownload(regionName); saveModal.hide();
        });
        $('#confirmSaveRenameButton').on('click', function() {
            var currentRegionName = $('#element_2a').val().trim() || "my_area";
            $('#confirmSaveActions').addClass('modal-hidden');
            $('#renameFileSection').removeClass('modal-hidden');
            $('#newRegionNameForFileInput').val(currentRegionName).focus();
        });
            $('#confirmSaveCancelButton').on('click', function() { saveModal.hide(); });
        $('#saveWithNewNameButton').on('click', function() {
            var newRegionName = $('#newRegionNameForFileInput').val().trim();
            if (!newRegionName) { alert("Please enter a valid region name for the file."); return; }
            // The triggerDownload function will now handle updating element_2a and regenerating YAML
            triggerDownload(newRegionName);
            saveModal.hide();
        });
            $('#cancelRenameFileButton').on('click', function() { saveModal.hide(); });

        // Initialize with the default selected projection (Stereographic)
        applyProjectionChange();
        setStatusMessage("Select projection and define an extent (Ctrl+Drag or Lat/Lon inputs).");
        $('#result').html(""); // Start with no YAML output

    }); // End of $(document).ready
    </script>

</body>
</html>
