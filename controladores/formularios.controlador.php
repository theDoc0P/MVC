<?php
 /**
 * registro
 */
class ControladorFormularios{
    static public function ctrRegistro(){
        if (isset($_POST["registroNombre"])) {
            if (
                preg_match("/^[a-zA-Z ]+$/", $_POST["registroNombre"]) &&
                preg_match('/^[_a-z0-9- ]+(\.[_a-z0-9- ]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})+$/', $_POST["registroEmail"]) &&
                preg_match('/^[0-9a-zA-Z]+$/', $_POST["registroPassword"])
            ){
                $tabla = "registros";
                $token = md5($_POST["registroNombre"] . "+" . $_POST["registroEmail"],);
                $encriptarPassword = crypt($_POST["registroPassword"], '$2a$07$wwwinnovaweb4cawscommx$');
                $datos = array(
                    "token" => $token,
                    "nombre" => $_POST["registroNombre"],
                    "email" => $_POST["registroEmail"],
                    "password" => $encriptarPassword);
                $respuesta = ModeloFormularios::mdlRegistro($tabla, $datos);
                return $respuesta;
            } else {
                $respuesta = "error";
                return $respuesta;
            }
        }
    }
    /**
     * seleccionar registros de la tabla
     */
    static public function ctrSeleccionarRegistros($item, $valor)
    {
        $tabla = "registros";
        $respuesta = ModeloFormularios::mdlSeleccionarRegistros
        ($tabla, $item, $valor);
        return $respuesta;
    }
    /**
     * ingreso
     */
    public function ctrIngreso(){
        if (isset($_POST["ingresoEmail"])) {
            $tabla = "registros";
            $item = "email";
            $valor = $_POST["ingresoEmail"];
            $respuesta = ModeloFormularios::mdlSeleccionarRegistros($tabla, $item, $valor);
            $encriptarPassword = crypt($_POST["ingresoPassword"], '$2a$07$2a$07$wwwinnovaweb4cawscommx$');
            if (is_array($respuesta)) {
                if ($respuesta["email"] == $_POST["ingresoEmail"] && $respuesta["password"] == $encriptarPassword) {
                    ModeloFormularios::mdlActualizarIntentosFallidos($tabla, 0, $respuesta["token"]);
                    $_SESSION["validarIngreso"] = "ok";
                    echo '<script>
                        if(window.history.replaceState){
                        window.history.replaceState(null, null, window.location.href)
                            }
                            window.location = "index.php?pagina=inicio";
                        </script>';
                } else {
                    if ($respuesta["intentos_fallidos"] < 3){
                        $tabla = "registros";
                        $intentos_fallidos = $respuesta["intentos_fallidos"] + 1;
                        $actualizarIntentosFallidos = ModeloFormularios::mdlActualizarIntentosFallidos(
                            $tabla,
                            $intentos_fallidos,
                            $respuesta["token"]
                        );
                    } else {
                        echo '<div class="alert alert-warning">ReCAPTCHA debes validar que no eres un robot</div>';
                    }
                    echo '<script>
                        if(window.history.replaceState){
                            window.history.replaceState(null, null, window.location.href)
                            }
                        </script>';
                    echo '<div class="alert alert-danger">¡error al ingresar al sistema!, el email o el password no coinciden</div>';
                }
            } else {
                echo '<script>
                        if(window.history.replaceState){
                            window.history.replaceState(null, null, window.location.href)
                            }
                        </script>';
                echo '<div class="alert alert-danger">¡error al ingresar al sistema!, el email o el password no coinciden</div>';
            }
        }
    }
    /**
     * actualizar registros
     */
    static public function ctrActualizarRegistro(){
        if (isset($_POST["actualizarNombre"])){
            if (
                preg_match("/^[a-zA-Z]+$/", $_POST["actualizarNombre"]) &&
                preg_match('/^[_a-z0-9- ]+(\.[_a-z0-9- ]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})+$/', $_POST["actualizarEmail"])
            ) {
                $usuario = ModeloFormularios::mdlSeleccionarRegistros("registros", "token", $_POST["tokenUsuario"]);
                $compararToken = md5($usuario["nombre"] . "+" . $usuario["email"]);
                if ($compararToken == $_POST['tokenUsuario']) {
                    if ($_POST["actualizarPassword"] != "") {
                        if (preg_match('/^[0-9a-zA-Z]+$/', $_POST["actualizarPassword"])) {
                            $password = crypt($_POST["actualizarPassword"], '$2a$07$2a$07$wwwinnovaweb4cawscommx$');
                        }
                    } else {
                        $password = $_POST["passwordActual"];
                    }
                    if ($_POST["nombreActual"] != $_POST["actualizarNombre"] || $_POST["emailActual"] != $_POST["actualizarEmail"]){
                        $nuevoToken = md5($_POST["actualizarNombre"] . "+" . $_POST["actualizarEmail"]);
                    } else {
                        $nuevoToken = null;
                    }
                    $tabla = "registros";
                    $datos = array(
                        "token" => $_POST["tokenUsuario"],
                        "nuevoToken" => $nuevoToken,
                        "nombre" => $_POST["actualizarNombre"],
                        "email" => $_POST["actualizarEmail"],
                        "password" => $password
                    );
                    $respuesta = ModeloFormularios::mdlActualizarRegistros($tabla, $datos);
                    return $respuesta;
                } else {
                    $respuesta = "error";
                    return $respuesta;
                }
            } else {
                $respuesta = "error";
                return $respuesta;
            };
        }
    }
    /**
     * eliminar registro
     */
    public function ctrEliminarRegistro(){
        if (isset($_POST["eliminarRegistro"])){
            $usuario = ModeloFormularios::mdlSeleccionarRegistros("registros", "token", $_POST["eliminarRegistro"]);
            $compararToken = md5($usuario["nombre"] . "+" . $usuario["email"]);
            if ($compararToken == $_POST["eliminarRegistro"]) {
                $tabla = "registros";
                $valor = $_POST["eliminarRegistro"];
                $respuesta = ModeloFormularios::mdlEliminarRegistro($tabla, $valor);
                if ($respuesta == "ok") {
                    echo '<script>
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
                window.location = "index.php?pagina=inicio";
                </script>';
                }
            }
        }
    }
}
