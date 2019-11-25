<!DOCTYPE html>
<html>
  <head>
    <title>PyTroll area definition tool</title>
    <script src="extentjs/ol.js"></script>
    <script src="extentjs/proj4-src.js"></script>
    <script src="extentjs/jquery-3.4.1.min.js"></script>
    <link rel="stylesheet" href="extentjs/ol.css" type="text/css">
    <link rel="stylesheet" href="extentjs/form.css" type="text/css">
  </head>
  <body>
    
<div class="form_container">
	
		<form>
	        <div class="form_description">
			<h2>PyTroll areas definition creator</h2>
		</div>						
		<ul>
                <li>
                <label class="description" for="element_2a">Region name</label>
                <div>
                        <input id="element_2a" name="element_2a" class="element text medium" type="text" maxlength="255" value="my_area"/>
                </div>
		</li>	
		<li id="li_1" >
		<label id="prjSelector" class="description" for="element_1">Projection type</label>
		<span>
			<input id="element_1_1" name="element_1" class="element radio" type="radio" value="1" checked />
			<label class="choice" for="element_1_1">proj=stere, lat_0=45.0, lon_0=10.0, lat_ts=45.0, ellps=WGS84</label>
			<input id="element_1_2" name="element_1" class="element radio" type="radio" value="2"/>
			<label class="choice" for="element_1_2">proj=eqc, ellps=WGS84, units=m</label>

		</span> 
		</li>		<li id="li_2" >
		<label class="description" for="element_2">Projection ID</label>
		<div>
			<input id="element_2" name="element_2" class="element text medium" type="text" maxlength="255" value="sample_area"/> 
		</div> 
		</li>		<li id="li_3" >
		<label class="description" for="element_3">Projection description</label>
		<div>
			<input id="element_3" name="element_3" class="element text large" type="text" maxlength="255" value="Sample area description goes here"/> 
		</div> 
		</li>		<li id="li_4" >
		<label class="description" for="element_4">Desired pixel size (image resolution) in meters</label>
		<div>
			<input id="element_4" name="element_4" class="element text small" type="text" maxlength="255" value="3000"/> 
		</div> 
		</li><!--		<li id="li_5" >
		<label class="description" for="element_5">Y size in pixels </label>
		<div>
			<input id="element_5" name="element_5" class="element text small" type="text" maxlength="255" value=""/> 
		</div> 
		</li>-->
			</ul>
		</form>	
	</div>

<div class="form_container">
<div class="form_description">
<h2>Result</h2>
</div>
<p id="result" style="font-family: monospace"></p>
</div>
<div style="clear: both"><br><br>
Use <strong>Shift+Drag</strong> to draw an extent.
<br><br></div>
    <div id="map" class="map" style="width: 70%; max-height: 800px; margin: 0 auto; border: 1px solid black;"></div>
    <script>
    //proj4.defs('npol', '+proj=stere +lat_0=45 +lat_ts=45 +lon_0=10 +k=1 +x_0=0 +y_0=0 +datum=WGS84 +units=m +no_defs');
    proj4.defs('npol', '+proj=stere +lat_0=45 +lat_ts=45 +lon_0=10 +x_0=0 +y_0=0 +units=m +no_defs');
    proj4.defs('eqc',  '+proj=eqc +ellps=wgs84 +units=m +no_defs');
    ol.proj.proj4.register(proj4);

    var extentEqc = [-10000000,-10000000, 10000000, 10000000]
    var extentStere = [-10000000,-10000000, 10000000, 10000000]

    var prjToUse = 'npol'
    var prj = proj4.defs[prjToUse] // stereographic nord

    var prjj = new ol.proj.Projection({
        code: prjToUse,
        extent: [-10000000,-10000000, 10000000, 10000000]
      });


      var map = new ol.Map({
        layers: [
          new ol.layer.Tile({
            source: new ol.source.OSM()
          }),
        ],
        target: 'map',
        view: new ol.View({
          crossOrigin: 'anonymous',
          projection: prjj,
          center: [0, 0],
          zoom: 2
     //     imageExtent: imageExtent
        })
      });

      var extent = new ol.interaction.Extent({
        condition: ol.events.condition.platformModifierKeyOnly
      });
      map.addInteraction(extent);
      extent.setActive(false);

      //Enable interaction by holding shift
      window.addEventListener('keydown', function(event) {
        if (event.keyCode == 16) {
          extent.setActive(true);
        }
      });
      window.addEventListener('keyup', function(event) {
        if (event.keyCode == 16) {
          extent.setActive(false);
          console.log(prj);
          /*var prjDef = "";
          $.each(prj, function( key, value ) {
               if (key == "projName") {
                   prjDef = "proj="+ value;
               } else {
                   prjDef = prjDef + ", " + key+"="+ value;
               }
          });*/
          updateArea();
          i}
      });

      function updateArea() {
          var prjDef = $("input:checked + label").text();
          var xsize = Math.round(Math.abs(extent.extent_[2] - extent.extent_[0]) / $('input[name="element_4"]').val());
          var ysize = Math.round(Math.abs(extent.extent_[3] - extent.extent_[1]) / $('input[name="element_4"]').val());
          var stringForYaml = prjDef
          stringForYaml.replace('+', '')
          var splitted = stringForYaml.split(/=|,/)
          var yamlParams = ""
          for (i = 0; i < splitted.length - 1; i = i + 2) {
              yamlParams = yamlParams +"<br>&nbsp;&nbsp;&nbsp;&nbsp;"+splitted[i].replace(' ', '') +": " +splitted[i+1].replace(' ', '')
          }
          $('#result').html("REGION: "+ $('input[name="element_2a"]').val() +
                            " {<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NAME: "+ $('input[name="element_3"]').val() +
                            "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PCS_ID: "+ $('input[name="element_2"]').val() +
                            "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PCS_DEF: "+ prjDef +
                            "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;XSIZE: "+ xsize +
                            "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;YSIZE: "+ ysize +
                            "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AREA_EXTENT: ("+extent.extent_.toString()+")"+
                            "<br>}<br><br><br>" +
                            $('input[name="element_2a"]').val().toLowerCase()+":<br>" +
                            "&nbsp;&nbsp;description: "+$('input[name="element_3"]').val() +
                            yamlParams +"<br>"+
                            "&nbsp;&nbsp;shape:<br>"+
                            "&nbsp;&nbsp;&nbsp;&nbsp;height: "+xsize+"<br>" +
                            "&nbsp;&nbsp;&nbsp;&nbsp;height: "+ysize+"<br>" +
                            "&nbsp;&nbsp;area_extent:<br>"+
                            "&nbsp;&nbsp;&nbsp;&nbsp;lower_left_xy: ["+ extent.extent_[0] +", " + extent.extent_[1]  +"]<br>" +
                            "&nbsp;&nbsp;&nbsp;&nbsp;upper_right_xy: ["+ extent.extent_[2] +", " + extent.extent_[3] +"]<br>")
        return true;
      }
   
      $("input[type=radio][name='element_1']").change(function(){
           console.log("The projction has changed.");
           var prjNr = $("input[name='element_1']:checked").val();
           if (prjNr == "1") {
               prjToUse = "npol"
               prj = proj4.defs[prjToUse]
               prjj = new ol.proj.Projection({
                   code: prjToUse,
                   extent: extentStere 
               });
               map.setView(new ol.View({
                   projection: prjj,
                   center: [0, 0],
                   zoom: 2,
                   imageExtent: extentStere
               }));
           } else if (prjNr == "2") {
               prjToUse = "eqc"
               prj = proj4.defs[prjToUse]
               prjj = new ol.proj.Projection({
                   code: prjToUse,
                   extent: extentStere
               });

               map.setView(new ol.View({
                   projection: prjj,
                   center: [0, 0],
                   zoom: 2
               }));
           }
           updateArea();
      }); 

    </script>
  </body>
</html>
