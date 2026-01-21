<?php
// -------------------------------------------------------------------------
// 1. CONFIGURACIÓN Y LÓGICA PHP
// -------------------------------------------------------------------------

// Cargar librerías
require __DIR__ . '/vendor/autoload.php';

// Variables para almacenar datos y errores
$resultado = "";
$error = "";
$textoOriginal = "";
$idiomaSeleccionado = "";

// Comprobamos si el formulario ha sido enviado (Método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Cargar variables de entorno (.env)
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad(); // Usamos safeLoad para evitar error fatal si no existe el .env

    // Recoger datos del formulario
    $textoOriginal = $_POST['texto'] ?? '';
    $idiomaSeleccionado = $_POST['idioma'] ?? '';

    // Validar que hay texto y un idioma seleccionado
    if (!empty($textoOriginal) && !empty($idiomaSeleccionado)) {
        try {
            // Verificar API KEY
            if (!isset($_ENV['OPENAI_API_KEY'])) {
                throw new Exception("Falta la clave OPENAI_API_KEY en el archivo .env");
            }

            $apiKey = $_ENV['OPENAI_API_KEY'];
            $client = OpenAI::client($apiKey);

            // Construir el prompt para la IA
            // Le decimos explícitamente qué queremos que haga
            $promptSistema = "Eres un traductor útil y preciso.";
            $promptUsuario = "Traduce el siguiente texto al idioma '$idiomaSeleccionado':\n\n" . $textoOriginal;

            // Hacer la petición
            $response = $client->chat()->create([
                'model' => 'gpt-5.2',
                'messages' => [
                    ['role' => 'system', 'content' => $promptSistema],
                    ['role' => 'user', 'content' => $promptUsuario],
                ],
                'temperature' => 0.3, // Temperatura baja para traducciones más literales/precisas
            ]);

            // Guardar la respuesta
            $resultado = $response->choices[0]->message->content;

        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Por favor, escribe un texto y selecciona un idioma.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testuen itzultzailea</title>
    <style>
        body {
            background-color: #f0f0f0; /* Fondo gris claro */
            font-family: "Times New Roman", Times, serif; /* Fuente con serifa */
            padding: 20px;
            text-align: center;
        }

        h1 {
            color: #000;
            font-weight: bold;
            margin-bottom: 20px;
        }

        table {
            border-collapse: separate;
            border-spacing: 2px;
            border: 1px solid #999;
            background-color: transparent;
            margin: auto;
        }

        td {
            border: 1px solid #999; /* Borde gris para las celdas */
            padding: 10px;
            vertical-align: middle;
            background-color: #f0f0f0;
        }

        /* Estilo para la primera columna (etiquetas y radio buttons) */
        td:first-child {
            text-align: center;
            width: 80px;
            font-weight: bold;
        }

        /* Estilo para la segunda columna (inputs y textos) */
        td:last-child {
            text-align: left;
            width: 250px;
            font-weight: bold;
        }

        textarea {
            width: 95%;
            height: 60px;
            resize: vertical;
        }

        button {
            padding: 2px 10px;
            font-family: inherit;
            background-color: #e0e0e0;
            border: 1px solid #777;
            cursor: pointer;
            font-weight: bold;
        }
        /* Estilos extra para mostrar el resultado */
        .resultado-box {
            background-color: #fff;
            padding: 10px;
            border: 1px solid #ccc;
            min-height: 40px;
        }
        .error-msg {
            color: red;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

    <h1>Testuen itzultzailea</h1>

    <form method="POST" action="">
        <table>
            <tr>
                <td>Testua</td>
                <td>
                    <textarea name="texto" required><?php echo htmlspecialchars($textoOriginal); ?></textarea>
                </td>
            </tr>

            <tr>
                <td><input type="radio" name="idioma" value="euskera" <?php echo ($idiomaSeleccionado == 'euskera') ? 'checked' : ''; ?>></td>
                <td>Euskera</td>
            </tr>

            <tr>
                <td><input type="radio" name="idioma" value="castellano" <?php echo ($idiomaSeleccionado == 'castellano') ? 'checked' : ''; ?>></td>
                <td>Castellano</td>
            </tr>

            <tr>
                <td><input type="radio" name="idioma" value="ingles" <?php echo ($idiomaSeleccionado == 'ingles') ? 'checked' : ''; ?>></td>
                <td>Inglés</td>
            </tr>

            <tr>
                <td></td>
                <td>
                    <button type="submit">Itzuli</button>
                    <?php if ($error): ?><br><span class="error-msg"><?php echo $error; ?></span><?php endif; ?>
                </td>
            </tr>
            <?php if ($resultado): ?>
            <tr>
                <td>Emaitza</td>
                <td>
                    <div class="resultado-box">
                        <?php echo nl2br(htmlspecialchars($resultado)); ?>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </form>

</body>
</html>