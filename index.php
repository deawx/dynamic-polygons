<?php

/*******************
 * Author: Edward R.
 * Date: 2016/10/19
 * Activity: Mapeo de vecindarios con poligonos y marcadores en Google Maps.  
*******************/

// incluye las funciones principales.
include_once('functions.php');
// asigna por defecto cero.
$neighborhood_id = 0;
// valida si recibe algún valor.
if (isset($_GET['id'])) {
	// captura el valor.
	$neighborhood_id = $_GET['id'];
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>GMaps Index</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDDhUR46Jx-zizDHTVRcpxK31MKJiq9kvI"></script>
    <script type="text/javascript">
    // DECLARACIÓN DE VARIABLES.
    var mapElement, neighborhoodTitle, neighborhoodPosition, mapTypeControlStyle, mapTypeId, mapOptions, map, mapCoordinates, polygonOptions, polygon, coordLatitude, coordLongitude, listNeighborhoods, optionNumList, inputNeighborhoodTitle, inputCoordinateLatitude, inputCoordinateLongitude, inputCoordinatesPolygon, btnUpdate, count, coordinatesData;
    // MAPA SIMPLE.
    function simpleMap() {
		// PROPIEDADES DEL MAPA.
		mapOptions = {
			center: new google.maps.LatLng(20.646828, -87.079008), // LUGAR
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
    }
    // MAPA PERSONALIZADO.
    function customMap() {
    	// SELECCIONA EL ELEMENTO HTML DEL MAPA.
		mapElement = document.getElementById('map');
		// SELECCIONA EL ELEMENTO HTML DEL LISTADO DE VECINDARIOS.
		listNeighborhoods = document.getElementById('list_neighborhoods');
		// SELECCIONA EL ELEMENTO HTML DEL NOMBRE DEL VECINDARIO.
		inputNeighborhoodTitle = document.getElementsByName('neighborhood_title')[0];
		// SELECCIONA EL ELEMENTO HTML CON LA COORDENADA DE LA LATITUD.
		inputCoordinateLatitude = document.getElementsByName('coordinate_latitude')[0];
		// SELECCIONA EL ELEMENTO HTML CON LA COORDENADA DE LA LONGITUD.
		inputCoordinateLongitude = document.getElementsByName('coordinate_longitude')[0];
		// SELECCIONA EL ELEMENTO HTML DE LAS COORDENADAS DEL POLÍGONO DEL VECINDARIO.
		inputCoordinatesPolygon = document.getElementsByName('coordinates_polygon')[0];
		// SELECCIONA EL ELEMENTO HTML DEL BOTON ACTUALIZAR VECINDARIO ACTUAL.
		btnUpdate = document.getElementById('btn_update');
		// ALMACENA EL DETALLE Y LAS COORDENDAS DEL POLÍGONO EN FORMATO JSON.
		listDetailsPolygonsNeighborhoods = '<?= getListDetailsPolygonsNeighborhoods(); ?>';
		// TRANSFORMA EL CONTENIDO EN OBJETOS.
		listDetailsPolygonsNeighborhoods = JSON.parse(listDetailsPolygonsNeighborhoods);
		// VALIDA QUE EL VALOR NÚMERICO SEA MAYOR A CERO.
		if (listNeighborhoods.value > 0) { 
		 	// ALMACENA EL VALOR DE LA OPCION SELECCIONADA.
			optionNumList = listNeighborhoods.options[listNeighborhoods.selectedIndex].id;
			// ASIGNA EL TITULO CON EL NOMBRE DEL LUGAR.
			inputNeighborhoodTitle.value = neighborhoodTitle = listNeighborhoods.options[listNeighborhoods.selectedIndex].text;
			// ASIGNA LA LATITUD.
			inputCoordinateLatitude.value = coordLatitude = listDetailsPolygonsNeighborhoods[optionNumList].latitude;
			// ASIGNA LA LONGITUD
			inputCoordinateLongitude.value = coordLongitude = listDetailsPolygonsNeighborhoods[optionNumList].longitude;
			// ASIGNA LAS COORDENADAS DEL LUGAR.
			neighborhoodPosition = new google.maps.LatLng(coordLatitude, coordLongitude);
			// INICIA UN ARREGLO.
			mapCoordinates = new Array();
			// INICIA EN UNO EL CONTADOR.
			count = 1;
			// INICIA COMO CADENA VACÍA.
			coordinatesData = '';
			// RECORRE LAS COORDENADAS PARA LA CREACIÓN DEL POLIGONO DEL LUGAR.
			for(var i = 0; i < listDetailsPolygonsNeighborhoods[optionNumList].coordinates.length; i++) {
				// ASIGNA LA LATITUD DE ACUERDO CON EL ORDENAMIENTO.
				coordLatitude = listDetailsPolygonsNeighborhoods[optionNumList].coordinates[i][0];
				// ASIGNA LA LONGITUD DE ACUERDO CON EL ORDENAMIENTO.
				coordLongitude = listDetailsPolygonsNeighborhoods[optionNumList].coordinates[i][1];
				// CREA LA INSTANCIA DEL OBJETO CON SUS PROPIEDADES.
				mapCoordinates[i] = new google.maps.LatLng(coordLatitude, coordLongitude);
				// CONCATENA LA LATITUD Y LA LONGITUD DEL POLÍGONO.
				coordinatesData += coordLatitude + ' ' + coordLongitude;
				// AUMENTA EN UNO AL CONTADOR ACTUAL.
				count++;
				// VALIDA SER MENOR IGUAL A LA CANTIDAD DE OBJETOS.
				if(count <= listDetailsPolygonsNeighborhoods[optionNumList].coordinates.length) {
					// CONCATENA COMAS COMO SEPARADORES.
					coordinatesData += ', ';
				}
			}
			// ALMACENA LA CADENA DE COORDENADAS PERTENECIENTES AL POLÍGONO ACTUAL.
			inputCoordinatesPolygon.value =  coordinatesData;
			// PROPIEDAD DEL TIPO (ESTILO) DE MENU DE CONTROL DEL MAPA.
			mapTypeControlStyle = { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU };
			// DEFINE EL ESTILO GEOGRAFICO PREDETERMINADO DEL MAPA.
			mapTypeId = google.maps.MapTypeId.ROADMAP;
			// PROPIEDADES DEL MAPA.
			mapOptions = {
				center: neighborhoodPosition, // CENTRO DEL LUGAR.
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
				draggable: false,
				position: neighborhoodPosition,
				title: neighborhoodTitle,
				map: map
			};
			// CREA EL MARCADOR Y SUS PROPIEDADES.
			marker = new google.maps.Marker(markerOptions);
			// PROPIEDADES DEL POLIGONO.
			polygonOptions = {
				draggable: false,
				editable: false,
				paths: mapCoordinates,
				strokeColor: 'red',
				fillColor: 'red',
			};
			// CREA UN POLIGONO.
			polygon = new google.maps.Polygon(polygonOptions);
			// SUPERPOSICIONA EL POLIGONO SOBRE EL MAPA.
			polygon.setMap(map);
			// HABILITA EL BOTÓN ACTUALIZAR VECINDARIO ACTUAL.
			btnUpdate.disabled =  false;
		// VALIDA QUE EL VALOR NÚMERICO SEA IGUAL A CERO.
		} else if (listNeighborhoods.value == 0) {
			// MUESTRA EL MAPA SIMPLE.
			simpleMap();
			// PONE NULOS LOS VALORES DEFINIDOS.
			inputNeighborhoodTitle.value = inputCoordinateLatitude.value = inputCoordinateLongitude.value = inputCoordinatesPolygon.value = null;
			// DESHABILITA EL BOTÓN ACTUALIZAR VECINDARIO ACTUAL.
			btnUpdate.disabled =  true;
		}
    }
    // MAPA PERSONALIZADO CON EVENTOS.
    function customMapEvents() {
    	// TRATA EL CONTENIDO.
		try {
			// CARGA EL MAPA CON EL VECINDARIO SELECCIONADO.
			window.document.onload = customMap();
			// ACTUALIZA EL POLÍGONO DENTRO DEL MAPA CADA QUE CAMBIA DE OPCIÓN.
			document.getElementById('list_neighborhoods').addEventListener('change', customMap);
		} catch(ex) {
			// IMPRIME EL MENSAJE DE ERROR.
			console.log('Error: ' + ex.message);
		}
    }
    // AGREGA UN EVENTO DE CARGA 'LOAD' EN LA COLA DEL DOM.
	google.maps.event.addDomListener(window, 'load', customMapEvents);
    </script>
</head>

<body>
	<h3>Module to select one.</h3>
	<form action="edit.php" enctype="multipart/form-data" method="post">
		<?= getListNeighborhoods('enabled', $neighborhood_id); ?>
		<input type="hidden" name="neighborhood_title">
		<input type="hidden" name="coordinate_latitude">
		<input type="hidden" name="coordinate_longitude">
		<input type="hidden" name="coordinates_polygon">
		<input type="submit" id="btn_update" value="Go to update data" disabled="true">
		<a href="add.php">Go to add a new polygon.</a>
	</form>
	<div id="map"></div>
</body>
</html>