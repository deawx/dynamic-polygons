<?php

/*******************
 * Author: Edward R.
 * Date: 2016/10/17
 * Activity: Mapeo de vecindarios con poligonos y marcadores en Google Maps.  
*******************/

// incluye las funciones principales.
include_once('functions.php');
// asigna por defecto cero.
$neighborhood_id = 0;
// valida la definición de contenido y que el id del vecindario seleccionado sea mayor a cero.
if (isset($_POST) && isset($_POST['list_neighborhoods']) > 0) {
    // identificador del vecindario.
    $neighborhood_id = $_POST['list_neighborhoods'];
	// obtiene los datos del formulario.
	$polygon_data = $_POST;
	// almacena los datos del polígono en la base de datos.
	setNeighborhoodPolygonData($polygon_data);
	// redirecciona a la principal.
	header('location: index.php?id='.$neighborhood_id);
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>GMaps Add</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDDhUR46Jx-zizDHTVRcpxK31MKJiq9kvI"></script>
    <script type="text/javascript">
    // DECLARACION DE VARIABLES
    var mapElement, neighborhoodPosition, mapTypeControlStyle, mapTypeId, mapOptions, map, listNeighborhoods, inputCoordinatesCenter, inputCoordinatesPolygon, btnSave, coordinatesPoint, coordinatesPolygon, coordinatesData, count, addressNeighborhood, geocoder, flagCoordCenter, flagCoodPolygon;
    // MAPA
    function map() {
    	try {
	    	// SELECCIONA EL ELEMENTO HTML DEL MAPA.
			mapElement = document.getElementById('map');
			// SELECCIONA EL ELEMENTO HTML DE LA LISTA DE VECINDARIOS.
			listNeighborhoods = document.getElementById('list_neighborhoods');			
			// SELECCIONA EL ELEMENTO HTML DEL CAMPO COORDENADAS DEL CENTRO.
			inputCoordinatesCenter = document.getElementsByName('coordinates_center')[0];
			// SELECCIONA EL ELEMENTO HTML DEL CAMPO COORDENADAS DE POLIGONO.
			inputCoordinatesPolygon = document.getElementsByName('coordinates_polygon')[0];
			// SELECCIONA EL ELEMENTO HTML DEL BOTÓN GUARDAR.
			btnSave = document.getElementById('btn_save');
			// DETECTA LA UBICACIÓN DEL LUCAR POR MEDIO DEL NOMBRE AL CAMBIAR LA OPCIÓN SELECCIONADA.
			listNeighborhoods.addEventListener('change', function(e) {
				// VALIDA SI EL VALOR ES MAYOR A CERO.
				if (listNeighborhoods.value > 0) {
					// HABILITA EL BOTÓN GUARDAR.
					btnSave.disabled = false;
					// PROPIEDAD DEL TIPO (ESTILO) DE MENU DE CONTROL DEL MAPA.
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
						streetViewControl: true, // VISOR DE CALLES 3D.
						panControl: true, // PANEL DE CONTROL DE DESPLAZAMIENTO.
						zoom: 12, // ZOOM
						zoomControl: true // CONTROL DE ZOOM.
					};
					// CREA EL MAPA Y SUS PROPIEDADES.
					map = new google.maps.Map(mapElement, mapOptions);
					// COLOCA LOS CONTROLES PARA TRAZAR PUNTOS Y POLÍGONOS.
					map.data.setControls(['Point', 'Polygon']);
					// COLOCA LAS PROPIEDADES DE EDICIÓN Y ARRASTRE DE OBJETOS.
					map.data.setStyle({ editable: true, draggable: true });
					// ALMACENA EL NOMBRE DEL VECINDARIO.
					addressNeighborhood = listNeighborhoods.options[listNeighborhoods.selectedIndex].text;
					// CREA LA INSTANCIA.
					geocoder = new google.maps.Geocoder();
					// COLOCA LAS PROPIEDADES DEL OBJETO.
					geocoder.geocode({ 'address': addressNeighborhood }, function(addressNeighborhood, status) {
						//	VALIDA EL ESTADO DEL RESULTADO.
						if (status == 'OK') {
							// POSICIONA EL LUGAR.
							map.setCenter(addressNeighborhood[0].geometry.location);
							// AJUSTA EL LIMITE DE LA VENTANA.
							map.fitBounds(addressNeighborhood[0].geometry.viewport);
						} else {
							// MANDA ESTE MENSAJE SI NO UBICA ALGUNA COINCIDENCIA CON EL NOMBRE DEL LUGAR.
							alert("Geocoding was unsuccessful because back: " + status);
						}
					});
					// SOBRE EL CLIC DEL BOTÓN SE CAPTURAN LOS COORDENADAS DEL MARCADOR, POLÍGONO E ID DEL VECINDARIO PARA ALAMACENARLOS.
					btnSave.onclick = function() {
						// INICIALIZA EN NULO.
						coordinatesData = null;
                        // INICIALIZA EN FALSO.
                        flagCoord = false;
						// OBTIENE LAS COORDENADAS DEL POLÍGONO Y MARCADOR DEL VECINDARIO.
						map.data.toGeoJson(function(geoJson) {
							// VALIDA SI ES UN OBJETO.
							if(typeof geoJson.features[0] === 'object') {
								// RECIBE LA CONFIRMACIÓN.
								confirm = confirm('Are you totally sure you want to save these coordinates?');
								// EVALUA SI FUE ACEPTADA.
								if(confirm == true) {
									// CONVIERTE EL OBJETO EN CADENA.
									coordinatesData = JSON.stringify(geoJson);
									// PARSEA LA CADENA EN FORMATO JSON.
									coordinatesData = JSON.parse(coordinatesData);
                                    // VALIDA SI EL CAMPO CENTRO NO ES NULO.
                                    if(typeof coordinatesData.features[0] !== 'undefined' && typeof coordinatesData.features[1] !== 'undefined') {
                                        // CAMBIA EL ESTADO DE LA BANDERA.
                                        flagCoord = true;
                                        // VALIDA SI LA GEOMETRÍA ES UN POLÍGONO.
                                        if(coordinatesData.features[0].geometry.type == 'Polygon') {
                                            // ALMACENA LAS COORDENADAS DEL POLÍGONO.
                                            coordinatesPolygon = coordinatesData.features[0].geometry.coordinates[0];
                                        } else  {
                                            // ALMACENA LAS COORDENADAS DEL PUNTO.
                                            coordinatesPoint = coordinatesData.features[0].geometry.coordinates;
                                        }
                                        // VALIDA SI LA GEOMETRÍA ES UN PUNTO.
                                        if(coordinatesData.features[1].geometry.type == 'Point') {
                                            // ALMACENA LAS COORDENADAS DEL PUNTO, SI ESTÁN EN ORDEN INVERSO.
                                            coordinatesPoint = coordinatesData.features[1].geometry.coordinates;
                                        } else {
                                            // ALMACENA LAS COORDENADAS DEL POLÍGONO, SI ESTÁN EN ORDEN INVERSO.
                                            coordinatesPolygon = coordinatesData.features[1].geometry.coordinates[0];
                                        }
                                        // INICIA EL CONTADOR EN UNO.
                                        count = 1;
                                        // REASIGNA EL VALOR A VACÍO.
                                        coordinatesData = '';
                                        // REALIZA UN RECORRIDO DE TODOS LOS REGISTROS.
                                        for(i in coordinatesPolygon) {
                                            // CONCATENA LAS COORDENADAS EN ORDEN INVERSO.
                                            coordinatesData += coordinatesPolygon[i][1] + ' ' + coordinatesPolygon[i][0];
                                            // AUMENTA UNO.
                                            count++;
                                            // VALIDA SI EL CONTADOR ES MENOR O IGUAL A LA LONGITUD DE DATOS EN EL OBJETO.
                                            if(count <= coordinatesPolygon.length) {
                                                // CONCATENA UNA COMA COMO SEPARADOR.
                                                coordinatesData += ', ';
                                            }
                                        }
                                        // ALMACENA LAS COORDENADAS DEL CENTRO.
                                        inputCoordinatesCenter.value = coordinatesPoint[1] + ' ' + coordinatesPoint[0];
                                        // ALMACENA LAS COORDENADAS DEL POLÍGONO.
                                        inputCoordinatesPolygon.value = coordinatesData;
                                    } else {
                                        // MANDA ESTE MENSAJE.
                                        window.alert('You need specifique to polygon and marker.');
                                        // RECARGA LA PÁGINA.
                                        location.reload();
                                    }
                                    // VALIDA QUE AMBOS TENGAN CONTENIDO.
                                    if(flagCoord) {
                                        // REALIZA EL ENVÍO DE LA INFORMACIÓN.
                                        document.getElementById('frm_insert').submit();
                                    }
								} else {
									// RECARGA LA PÁGINA.
									location.reload();
								}
							} else {
								//MANDA ESTE MENSAJE.
								window.alert("You haven't created any polygon or marker.");
							}
						});
					}
				} else if (listNeighborhoods.value == 0) {
					// DESHABILITA EL BORÓN GUARDAR.
					btnSave.disabled = true;
					// PROPIEDADES DEL MAPA.
					mapOptions = {
						center: google.maps.LatLng(20.646828, -87.079008), // LUGAR
						disableDoubleClickZoom: true, // ZOOM CON DOBLE CLIC.
						draggable: true,  // DESPLAZAMIENTO SOBRE EL MAPA CON EL MOUSE.
						mapTypeControl: true, // ACTIVACION DEL MENU DE CONTROL DE TIPO DE MAPA.
						mapTypeControlOptions: mapTypeControlStyle, // ESTILO DEL MENU DE CONTROL.
						mapTypeId: mapTypeId , // ESTILO GEOGRAFICO DEL MAPA.
						scrollwheel: true,  // SCROLL
						streetViewControl: false, // VISOR DE CALLES 3D.
						panControl: true, // PANEL DE CONTROL DE DESPLAZAMIENTO.
						zoom: 12, // ZOOM
						zoomControl: false // CONTROL DE ZOOM.
					};
					// CREA EL MAPA Y SUS PROPIEDADES.
					map = new google.maps.Map(mapElement, mapOptions);
				}
			});
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
	<h3>Module to add.</h3>
	<form action="add.php" enctype="multipart/form-data" method="post" id="frm_insert">
		<?= getListNeighborhoods('disabled', $neighborhood_id); ?>
		<input type="hidden" name="coordinates_center">
		<input type="hidden" name="coordinates_polygon">
		<input type="button" id="btn_save" value="Save polygon and marker" disabled="true">
		<a href="index.php">Go to the listing.</a>
	</form>
	<div id="map"></div>
</body>
</html>