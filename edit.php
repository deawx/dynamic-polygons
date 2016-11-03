<?php

/*******************
 * Author: Edward R.
 * Date: 2016/10/19
 * Activity: Mapeo de vecindarios con poligonos y marcadores en Google Maps.
*******************/

// incluye las funciones a implementar.
include_once('functions.php');
// valida la definición de las variables.
if (isset($_POST) && isset($_POST['coords_center']) && isset($_POST['coords_polygon']) && isset($_POST['neighborhood_id']) ) {
	// recibe el id del vecindario.
	$neighborhood_id = $_POST['neighborhood_id'];
	// recibe los valores de los campos ocultos.
	$polygon_data = $_POST;
	// realiza la actualización de los valores.
	updateNeighborhoodPolygonData($polygon_data);
	// redirecciona a la página indicada.
	header('location: index.php?id='.$neighborhood_id);
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>GMaps Edit</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDDhUR46Jx-zizDHTVRcpxK31MKJiq9kvI"></script>
    <script type="text/javascript">
    // DECLARACION DE VARIABLES
    var mapElement, neighborhoodId, neighborhoodTitle, neighborhoodPosition, mapTypeControlStyle, mapTypeId, mapOptions, map, mapCoordinates, polygonOptions, polygon, polygonData, coordinatesData, coordsPolygon, coordLatitude, coordLongitude, polyPath, polyLength, polyPathStart, polyPathEnd, inputCoordinatesCenter, inputCoordinatesPolygon, inputNeighborhoodId, btnSave;
    // MAPA
    function map() {
    	try {
	    	// SELECCIONA EL ELEMENTO HTML DEL MAPA.
			mapElement = document.getElementById('map');			
			// SELECCIONA EL ELEMENTO HTML DE LAS COORDENADAS DEL CENTRO.
			inputCoordinatesCenter = document.getElementsByName('coords_center')[0];
			// SELECCIONA EL ELEMENTO HTML DE LAS COORDENADAS DEL POLÍGONO.
			inputCoordinatesPolygon = document.getElementsByName('coords_polygon')[0];
			// SELECCIONA EL ELEMENTO HTML DEL ID DEL VECINDARIO.
			inputNeighborhoodId = document.getElementsByName('neighborhood_id')[0];
			// SELECCIONA EL ELEMENTO HTML DEL BOTÓN GUARDAR.
			btnSave = document.getElementById('btn_save');
			// ALMACENA EL DETALLE Y LAS COORDENDAS DEL POLÍGONO EN FORMATO JSON.
			polygonData = '<?= json_encode($_POST); ?>';
			// TRANSFORMA EL CONTENIDO EN OBJETOS.
			polygonData = JSON.parse(polygonData);
			// ASIGNA EL ID DEL VECINDARIO.
			neighborhoodId = polygonData.list_neighborhoods;
			// ASIGNA LA LATITUD.
			coordLatitude = polygonData.coordinate_latitude;
			// ASIGNA LA LONGITUD.
			coordLongitude = polygonData.coordinate_longitude;
			// ASIGNA LAS COORDENADAS DEL LUGAR.
			neighborhoodPosition = new google.maps.LatLng(coordLatitude, coordLongitude);
			// ASIGNA EL NOMBRE DEL VECINDARIO.
			neighborhoodTitle = polygonData.neighborhood_title;
			// INICIA COMO ARREGLO.
			coordsPolygon = new Array();
			// INICIA COMO ARREGLO.
			mapCoordinates = new Array();
			// DIVIDE LA CADENA POR COMAS EN FORMA DE ARREGLO.
			coordinatesData = polygonData.coordinates_polygon.split(', ');
			// RECORRE LA LONGITUD DEL ARREGLO.
			for (var i = 0 ; i < coordinatesData.length; i++) {
				// DIVIDE POR SEGUNDA VEZ Y AHORA POR ESPACIOS LOS VALORES DEL ARREGLO.
				coordsPolygon[i] = coordinatesData[i].split(' ');
				// ASIGNA LA LATITUD DE ACUERDO CON EL ORDENAMIENTO.
				coordLatitude = coordsPolygon[i][0];
				// ASIGNA LA LONGITUD DE ACUERDO CON EL ORDENAMIENTO.
				coordLongitude = coordsPolygon[i][1];
				// CREA LA INSTANCIA DEL OBJETO CON SUS PROPIEDADES.
				mapCoordinates[i] = new google.maps.LatLng(coordLatitude, coordLongitude);
			}
			// PROPIEDAD DEL TIPO (ESTILO) DE MENU PARA CONTROL DEL MAPA.
			mapTypeControlStyle = { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU };
			// DEFINE EL ESTILO GEOGRAFICO PREDETERMINADO DEL MAPA.
			mapTypeId = google.maps.MapTypeId.ROADMAP;
			// PROPIEDADES DEL MAPA.
			mapOptions = {
				center: neighborhoodPosition, // LUGAR
				disableDoubleClickZoom: false, // ZOOM CON DOBLE CLIC.
				draggable: true,  // DESPLAZAMIENTO SOBRE EL MAPA CON EL MOUSE.
				mapTypeControl: true, // ACTIVACION DEL MENU DE CONTROL DE TIPO DE MAPA.
				mapTypeControlOptions: mapTypeControlStyle, // ESTILO DEL MENU DE CONTROL.
				mapTypeId: mapTypeId , // ESTILO GEOGRAFICO DEL MAPA.
				scrollwheel: true,  // SCROLL
				streetViewControl: false, // VISOR DE CALLES 3D.
				panControl: true, // PANEL DE CONTROL DE DESPLAZAMIENTO.
				zoom: 12, // ZOOM
				zoomControl: true // CONTROL DE ZOOM.
			};
			// CREA EL MAPA Y SUS PROPIEDADES.
			map = new google.maps.Map(mapElement, mapOptions);
			// PROPIEDADES DEL MARCADOR.
			markerOptions = {
				draggable: true, // ARRASTRABLE
				position: neighborhoodPosition, // CENTRO
				title: neighborhoodTitle, // TÍTULO
				map: map // MAPA
			};
			// CREA EL MARCADOR Y SUS PROPIEDADES.
			marker = new google.maps.Marker(markerOptions);
			// PROPIEDADES DEL POLIGONO.
			polygonOptions = {
				draggable: true, // ARRASTRABLE
				editable: true, // EDITABLE
				paths: mapCoordinates, // COORDENADAS DEL POLÍGONO.
				strokeColor: 'blue', // COLOR DEL CONTORNO.
				fillColor: 'blue', // COLOR DE RELLENO.
			};
			// CREA UN POLIGONO.
			polygon = new google.maps.Polygon(polygonOptions);
			// SUPERPOSICIONA EL POLIGONO SOBRE EL MAPA.
			polygon.setMap(map);
			// EVENTO CLIC UTILIZADO PARA RECORRER LAS NUEVAS COORDENADAS DEL MARCADOR Y POLÍGONO.
			btnSave.onclick = function() {
				// ALMACENA LA LONGITUD Y LATITUD OBTENIDA DEL MARCADOR EN EL CAMPO HTML.
				inputCoordinatesCenter.value = marker.getPosition().lat() + ' ' + marker.getPosition().lng();
				// OBTIENE EL CONJUNTO DE COORDENADAS DEL POLÍGONO.
				polyPath = polygon.getPath();
				// OBTIENE LA LONGITUD DEL CONJUNTO DE OBJETOS.
				polyLength = polyPath.length;
				// INICIA EN UNO EL CONTADOR.
				count = 1;
				// REASIGNADA COMO CADENA VACÍA.
				coordinatesData = '';
				// OBTIENE LA RUTA DE CADA COORDENADA DEL POLÍGONO. 
				polyPath.forEach(function(coord) {
					// CONCATENA LA LATITUD Y LA LONGITUD DEL POLÍGONO.
					coordinatesData += coord.lat() + ' ' + coord.lng();
					// AUMENTA EN UNO EL CONTADOR ACTUAL.
					count++;
					// VALIDA SER MENOR IGUAL A LA LONGITUD DE OBJETOS.
					if(count <= polyLength) {
						// CONCATENA COMAS COMO SEPARADORES.
						coordinatesData += ', ';
					}
				});
				// OBTIENE LA LATITUD Y LONGITUD DEL CONJUNTO DE COORDENADAS EN LA POSICIÓN INICIAL DEL POLÍGONO.
				polyPathStart = polyPath.getAt(0).lat() + ' ' + polyPath.getAt(0).lng();
				// OBTIENE LA LATITUD Y LONGITUD DEL CONJUNTO DE COORDENADAS EN LA POSICIÓN FINAL DEL POLÍGONO.
				polyPathEnd = polyPath.getAt(polyLength - 1).lat() + ' ' + polyPath.getAt(polyLength - 1).lng();
				// VALIDA LA DESIGUALDAD ENTRE CADENAS.
				if(polyPathEnd != polyPathStart) {
					// CONCATENA LA POSICIÓN INICIAL AL FINAL DEL CONJUNTO PARA FORMAR EL POLÍGONO CORRECTAMENTE.
					coordinatesData += ', ' + polyPathStart;
				}
				// ALMACENA TODAS LAS COORDENADAS DEL POLÍGONO EN EL CAMPO HTML.
				inputCoordinatesPolygon.value = coordinatesData;
				// ALMACENA EL ID DEL VECINDARIO EN EL CAMPO HTML.
				inputNeighborhoodId.value = neighborhoodId;
				// ENVÍA AL SERVIDOR LOS DATOS DEL FORMULARIO.
				document.getElementById('frm_edit').submit();
			}
		} catch(ex) {
			// IMPRIME EL MENSAJE DE ERROR.
			console.log('Error: ' + ex.message);
		}
    }
    // AGREGA UN EVENTO DE CARGA 'LOAD' EN LA COLA DEL DOM.
	google.maps.event.addDomListener(window, 'load', map);
    </script>
</head>

<body>
	<h3>Module to update.</h3>
	<form action="edit.php" enctype="multipart/form-data" method="post" id="frm_edit">
		<input type="hidden" name="coords_center">
		<input type="hidden" name="coords_polygon">
		<input type="hidden" name="neighborhood_id">
		<input type="button" id="btn_save" value="Save data">
		<a href="index.php">Go to the listing.</a>
	</form>
	<div id="map"></div>
</body>
</html>