<?php

	require_once 'vendor/autoload.php';

	$app = new \Slim\Slim();

	$db = new mysqli("localhost", "root", "", "curso");

	//configuracion de cabeceras
	header('Access-Control-Allow-Origin: *');
	header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
	header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
	header("Allow: GET, POST, OPTIONS, PUT, DELETE");
	$method = $_SERVER['REQUEST_METHOD'];
	if($method == "OPTIONS") {
	    die();
	}

	$app->get("/pruebas", function() use($app, $db){
		echo "Hola Mundo ";
		var_dump($db);
	});

	$app->get("/pruebas2", function() use($app){
		echo "Hola Mundo";
	});

	//listar de productos
	$app->get("/productos", function() use($app, $db){

		$sql = 'SELECT * FROM productos ORDER BY id DESC;';
		$query = $db->query($sql);

		//var_dump($query->fetch_assoc());
		//var_dump($query->fetch_all());
		$productos = array();

		while ($producto = $query->fetch_assoc()) {
			$productos[] = $producto;
		}
		
		$result = array(
				'status' => 'success',
				'code' => 200,
				'data' => $productos
		);

		echo json_encode($result);
		

	});

	//obtener producto
	$app->get("/producto/:id", function($id) use($app, $db){

		$sql = 'SELECT * FROM productos WHERE id ='.$id;
		$query = $db->query($sql);


		$result = array(
			'status' => 'error',
			'code' => 404,
			'message' => 'Producto no encontrado'
		);
		
		if($query->num_rows == 1){
			$producto = $query->fetch_assoc();

			$result = array(
				'status' => 'success',
				'code' => 200,
				'data' => $producto
			);
		} 
		
		echo json_encode($result);

	});

	//eliminar producto
	$app->get("/delete/:id", function($id) use($app, $db){

		$sql = 'DELETE FROM productos WHERE id ='.$id;
		$query = $db->query($sql);


		$result = array(
			'status' => 'error',
			'code' => 404,
			'message' => 'Producto no eliminado'
		);
		
		if($query) {

			$result = array(
				'status' => 'success',
				'code' => 200,
				'message' => 'Producto Eliminado Correctamente'
			);
		} 
		
		echo json_encode($result);

	});

	//modificar producto
	$app->post("/update/:id", function($id) use($app, $db){

		$json = $app->request->post('json');
		$data = json_decode($json, true);

		$sql = "UPDATE productos SET "
				."nombre = '{$data['nombre']}', "
				."descripcion = '{$data['descripcion']}', "
				."precio = '{$data['precio']}' "
				." WHERE id = {$id}";
		var_dump($sql);
		$query = $db->query($sql);

		if($query) {

			$result = array(
				'status' => 'success',
				'code' => 200,
				'message' => 'Producto se Actualizado Correctamente'
			);

		} else {
			$result = array(
				'status' => 'error',
				'code' => 404,
				'message' => 'Producto no se ha podido actualizado'
			);
		}
		echo json_encode($result);

	});


	//subir imagen a producto
	$app->post("/upload", function() use($app, $db){

		$result = array(
				'status' => 'error',
				'code' => 404,
				'message' => 'Imagen no se ha podido subir'
		);
		
		if(isset($_FILES['uploads'])) {

			$piramideUploader = new PiramideUploader();
			$upload = $piramideUploader->upload('image', 'uploads', 'uploads/', array('image/jpeg', 'image/png', 'image/gif'));

			$file = $piramideUploader->getInfoFile();
			$file_name = $file['complete_name'];

			var_dump($file_name);

			if(isset($upload) && $upload['uploaded'] == false){
				$result = array(
					'status' => 'error',
					'code' => 404,
					'message' => 'Imagen NO Recibida'
				);
			} else {
				$result = array(
					'status' => 'success',
					'code' => 200,
					'message' => 'Imagen Recibida'
				);
			}
		}

		echo json_encode($result);

	});

	//guardar productos
	$app->post('/productos', function() use($app, $db){
		$json = $app->request->post('json');
		$data = json_decode($json, true);

		//var_dump($json);
		//var_dump($data);

		if(!isset($data['nombre'])){
			$data['nombre'] = null;
		}
		if(!isset($data['descripcion'])){
			$data['descripcion'] = null;
		}
		if(!isset($data['precio'])){
			$data['precio'] = null;
		}
		if(!isset($data['imagen'])){
			$data['imagen'] = null;
		}

		$query = "INSERT INTO productos VALUES (NULL,"
			."'{$data['nombre']}', "
			."'{$data['descripcion']}', "
			."'{$data['precio']}', "
			."'{$data['imagen']}'"
			.")";

		//var_dump($query);
		
		$insert = $db->query($query);
		
		$result = array(
				'status' => 'error',
				'code' => 404,
				'message' => 'Producto NO agregado'

		);

		if($insert){
			$result = array(
				'status' => 'success',
				'code' => 200,
				'message' => 'Producto Agregado correctamente'

			);
		}

		echo json_encode($result);
	});


	$app->run();

?>