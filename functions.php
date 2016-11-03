<?php

/*******************
 * Author: Edward R.
 * Date: 2016/10/18
 * Activity: Listado, registro y actualización de datos de detalle y coordenadas del polígono del vecindario. 
*******************/

// incluye la conexión a la base de datos.
include_once('db-connection.php');

// Obtiene la lista desplegable de vecindarios.
function getListNeighborhoods($type_listing_neighborhoods = null, $selected_neighborhood_id = 0) {
	// cadena de conexión.
	$conn = db_connection();
	// selección de la consulta.
	switch ($type_listing_neighborhoods) {
		// para mostrar los vecindarios sin coordendas y polígono a agregar.
		case 'disabled':
			// enlista todos los vecindarios que no tienen polígonos relacionados.
			$sql = 'SELECT nh.id, name, polys.neighborhood_id FROM neighborhoods nh
					LEFT JOIN neighborhoods_polygons polys ON nh.id = polys.neighborhood_id
					WHERE polys.neighborhood_id IS NULL';
			break;
		// para mostrar los vecindarios con coordendas y polígono a actualizar.
		case 'enabled':
			// enlista todos los vecindarios que si tienen polígonos relacionados.
			$sql = 'SELECT nh.id, name, polys.neighborhood_id FROM neighborhoods nh
					LEFT JOIN neighborhoods_polygons polys ON nh.id = polys.neighborhood_id
					WHERE polys.neighborhood_id IS NOT NULL
					ORDER BY polys.neighborhood_id ASC';
			break;
	}
	// ejecuta la consulta y devuelve el resultado obtenido.
	$result = mysqli_query($conn, $sql);
	// valida si el resultado es verdadero.
	if ($result) {
		// apertura de la lista
		$list_neighborhoods = '<select id="list_neighborhoods" name="list_neighborhoods">';
		// concatena el primer valor por defecto.
		$list_neighborhoods .= '<option value="0">Select Neighborhood</option>';
		// contador
		$count = 0;
		// realiza un recorrido de todos los registros y los va guardando.
		while ($row = mysqli_fetch_assoc($result)) {
			// extrae y materializa los valores con su nombre en forma de variables.
			extract($row);
			// encodifica a utf8.
			$name = utf8_encode($name);
			// valida la equivalencia de valores entre el elemento seleccionado con el elemento enlistado.
			if ($selected_neighborhood_id == $id) {
				// concatena el elemento seleccionado a la lista.
				$list_neighborhoods .= '<option value="'.$id.'" id="'.$count.'" selected>'.$name.'</option>';
			} else {
				// concatena el elemento a la lista.
				$list_neighborhoods .= '<option value="'.$id.'" id="'.$count.'">'.$name.'</option>';
			}
			// suma uno
			$count++;
		}
		// cierre de concatenacon
		$list_neighborhoods .= '</select>';
		// libera espacio en la memoria.
		mysqli_free_result($result);
		// cierra la conexión.
		mysqli_close($conn);
		// devuelve la lista de vecindarios.
		return $list_neighborhoods;
	} else {
		// si en la validacion el resultado es falso se devuelve este mensaje de error.
		echo 'Error: ' . $sql . '<br>' . mysqli_error($conn);
	}
}

// Obtiene el detalle y las coordenadas del polígono del vecindario.
function getListDetailsPolygonsNeighborhoods() {
	// cadena de conexión.
	$conn = db_connection();
	// trae todos los registros.
	$sql = "SELECT X(center) latitude, Y(center) longitude, 
			REPLACE(REPLACE(ASTEXT(EXTERIORRING(coordinates)), 'LINESTRING(', ''), ')', '') coordinates,
			neighborhood_id
		    FROM neighborhoods_polygons
		    ORDER BY neighborhood_id ASC";
	// ejecuta la consulta y devuelve el resultado obtenido.
	$result = mysqli_query($conn, $sql);
	// valida si el resultado es verdadero
	if ($result) {
		// declarada y posteriormente usada para almacenar cada propiedad del polígono del vecindario.
		$neighborhood_polygon_data = array();
		// declarada y posteriomente usada para almacenar el conjunto de datos del polígono de cada vecindario.
		$list_details_polygons_neighborhoods = array();
		// contador en 0.
		$num = 0;
		// realiza un recorrido de todos los registros.
		while ($row = mysqli_fetch_assoc($result)) {
			// extrae y materializa los valores con su nombre en forma de variables.
			extract($row);
			// alamcena la latitud.
			$neighborhood_polygon_data['latitude'] = $latitude;
			// almacena la longitud.
			$neighborhood_polygon_data['longitude'] = $longitude;
			// almacena los coordenadas particionadas por ',' comas.
			$coordinates = explode(',', $coordinates);
			//  realiza un recorrido de todas las cordendadas.
			foreach ($coordinates as $key => $value) {
				// almacena las subcordenadas particionadas por ' ' espacios en blanco.
				$coordinates[$key] = explode(' ', $value);
			}
			// almacena las coordenadas.
			$neighborhood_polygon_data['coordinates'] = $coordinates;
			// almacena el identificador del vecindario.
			$neighborhood_polygon_data['neighborhood_id'] = $neighborhood_id;
			// almacena todo el conjunto de datos por cada vecindario.
			$list_details_polygons_neighborhoods[$num] = $neighborhood_polygon_data;
			// aumenta un número al contador.
			$num++;
		}
		// libera la memoria del servidor.
		mysqli_free_result($result);
		// cierra la conexión a la base de datos.
		mysqli_close($conn);
		// devuelve un json con el listado de vecindarios.
		return json_encode($list_details_polygons_neighborhoods);
	} else {
		// si en la validacion el resultado es falso se devuelve este mensaje de error.
		echo 'Error: ' . $sql . '<br>' . mysqli_error($conn);
	}
}

// Inserta el detalle y las coordenadas del polígono del vecindario.
function setNeighborhoodPolygonData($polygon_data) {
	// cadena de conexión.
	$conn = db_connection();
	// extrae los datos en variables con su propio nombre de la base de datos.
	extract($polygon_data);
	// obtiene el id del vecindario.
	$neighborhood_id = $list_neighborhoods;
	// inserta los valores indicados.
	$sql = "INSERT INTO neighborhoods_polygons (center, coordinates, neighborhood_id) VALUES
			(GEOMFROMTEXT('POINT(".$coordinates_center.")'), 
			GEOMFROMTEXT('POLYGON((".$coordinates_polygon."))'), 
			".$neighborhood_id.")";
	// ejecuta la consulta y devuelve el resultado obtenido.
	$result = mysqli_query($conn, $sql);
	// valida si el resultado es verdadero.
	if ($result) {
 		// cierra la conexión.
		mysqli_close($conn);
	} else {
		// si en la validacion el resultado es falso se devuelve este mensaje de error.
		echo 'Error: ' . $sql . '<br>' . mysqli_error($conn);
	}
}

// Actualiza el detalle y las coordenadas del polígono del vecindario.
function updateNeighborhoodPolygonData($polygon_data) {
	// cadena de conexión.
	$conn = db_connection();
	// extrae los datos en variables con su propio nombre de la base de datos.
	extract($polygon_data);
	// actualiza los valores indicados.
	$sql = "UPDATE neighborhoods_polygons SET  
			center = GEOMFROMTEXT( 'POINT(".$coords_center.")', 0 ) ,
			coordinates = GEOMFROMTEXT( 'POLYGON((".$coords_polygon."))', 0 ) 
			WHERE neighborhood_id = ".$neighborhood_id."";
	// ejecuta la consulta y devuelve el resultado obtenido.
	$result = mysqli_query($conn, $sql);
	// valida si el resultado es verdadero.
	if ($result) {
 		// cierra la conexión.
		mysqli_close($conn);
	} else {
		// si en la validacion el resultado es falso se devuelve este mensaje de error.
		echo 'Error: ' . $sql . '<br>' . mysqli_error($conn);
	}	
}