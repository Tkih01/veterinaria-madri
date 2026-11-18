<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Config\ImageUploader;
use App\Middlewares\Auth;
use App\Middlewares\Csrf;
use PDO;
use RuntimeException;

/**
 * Controlador de operaciones CRUD del Panel Clínico y Catálogo (OWASP A01 / A03)
 */
final class AdminController
{
    /**
     * Genera un folio único no secuencial formato VM-XXXXX (sin 0/O ni 1/I/L)
     */
    private static function generateUniquePetCode(PDO $db): string
    {
        $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $maxIndex = strlen($alphabet) - 1;

        do {
            $code = 'VM-';
            for ($i = 0; $i < 5; $i++) {
                $code .= $alphabet[random_int(0, $maxIndex)];
            }

            $stmt = $db->prepare("SELECT COUNT(*) FROM mascotas WHERE codigo_unico = :code");
            $stmt->execute([':code' => $code]);
            $exists = ((int) $stmt->fetchColumn()) > 0;
        } while ($exists);

        return $code;
    }

    /**
     * Procesa todas las peticiones POST del panel administrativo
     * @return array{type: string, text: string}|null
     */
    public static function handlePost(): ?array
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            return ['type' => 'danger', 'text' => 'Token de seguridad CSRF inválido. Recarga la página e intenta de nuevo.'];
        }

        $action = (string) ($_POST['form_action'] ?? '');
        $db     = Database::getConnection();

        try {
            switch ($action) {
                case 'save_pet':
                    return self::savePet($db);

                case 'add_history':
                    return self::addClinicalHistory($db);

                case 'save_product':
                    return self::saveProduct($db);

                default:
                    return ['type' => 'danger', 'text' => 'Acción administrativa no reconocida.'];
            }
        } catch (RuntimeException $e) {
            return ['type' => 'danger', 'text' => $e->getMessage()];
        } catch (\Throwable $e) {
            return ['type' => 'danger', 'text' => 'Error al guardar en la base de datos: ' . $e->getMessage()];
        }
    }

    private static function savePet(PDO $db): array
    {
        $petId         = !empty($_POST['mascota_id']) ? (int) $_POST['mascota_id'] : null;
        $nombreMascota = trim((string) ($_POST['nombre_mascota'] ?? ''));
        $especie       = (string) ($_POST['especie'] ?? 'Perro');
        $nombreDueno   = trim((string) ($_POST['nombre_dueno'] ?? ''));

        if ($nombreMascota === '' || $nombreDueno === '') {
            throw new RuntimeException('El nombre de la mascota y el nombre del dueño son obligatorios.');
        }

        $especiesValidas = ['Perro', 'Gato', 'Otro'];
        if (!in_array($especie, $especiesValidas, true)) {
            $especie = 'Perro';
        }

        // Campos opcionales (convertir cadenas vacías en NULL)
        $raza         = trim((string) ($_POST['raza'] ?? '')) ?: null;
        $sexo         = trim((string) ($_POST['sexo'] ?? '')) ?: null;
        $edadAprox    = trim((string) ($_POST['edad_aprox'] ?? '')) ?: null;
        $pesoRaw      = trim((string) ($_POST['peso_actual'] ?? ''));
        $pesoActual   = ($pesoRaw !== '' && is_numeric($pesoRaw)) ? (float) $pesoRaw : null;
        $esterilizado = trim((string) ($_POST['esterilizado'] ?? '')) ?: null;
        $alergias     = trim((string) ($_POST['alergias_notas'] ?? '')) ?: null;
        $telefono     = trim((string) ($_POST['telefono_contacto'] ?? '')) ?: null;

        if ($petId === null) {
            // Crear nueva mascota
            $codigoUnico = self::generateUniquePetCode($db);
            $fotoWebp    = null;

            if (!empty($_FILES['foto_mascota'])) {
                $fotoWebp = ImageUploader::uploadAndConvertToWebp(
                    $_FILES['foto_mascota'],
                    'mascotas',
                    $codigoUnico,
                    null
                );
            }

            $stmt = $db->prepare("
                INSERT INTO mascotas (
                    codigo_unico, nombre_mascota, especie, raza, sexo, edad_aprox,
                    peso_actual, esterilizado, alergias_notas, foto_webp,
                    nombre_dueno, telefono_contacto
                ) VALUES (
                    :codigo, :nombre_mascota, :especie, :raza, :sexo, :edad,
                    :peso, :esterilizado, :alergias, :foto,
                    :nombre_dueno, :telefono
                )
            ");
            $stmt->execute([
                ':codigo'         => $codigoUnico,
                ':nombre_mascota' => $nombreMascota,
                ':especie'        => $especie,
                ':raza'           => $raza,
                ':sexo'           => $sexo,
                ':edad'           => $edadAprox,
                ':peso'           => $pesoActual,
                ':esterilizado'   => $esterilizado,
                ':alergias'       => $alergias,
                ':foto'           => $fotoWebp,
                ':nombre_dueno'   => $nombreDueno,
                ':telefono'       => $telefono,
            ]);

            $newId = (int) $db->lastInsertId();
            header("Location: /admin?mascota_id={$newId}&msg=created");
            exit;
        }

        // Actualizar mascota existente
        $stmtCurrent = $db->prepare("SELECT codigo_unico, foto_webp FROM mascotas WHERE id = :id LIMIT 1");
        $stmtCurrent->execute([':id' => $petId]);
        $currentPet = $stmtCurrent->fetch();

        if (!$currentPet) {
            throw new RuntimeException('La mascota que intentas editar no existe.');
        }

        $fotoWebp = $currentPet['foto_webp'];

        // Si marcó la casilla para eliminar la foto actual
        if (!empty($_POST['eliminar_foto']) && $fotoWebp !== null) {
            ImageUploader::deleteFile('mascotas', $fotoWebp);
            $fotoWebp = null;
        }

        // Si subió una nueva foto, reemplaza la anterior
        if (!empty($_FILES['foto_mascota']) && $_FILES['foto_mascota']['error'] !== UPLOAD_ERR_NO_FILE) {
            $fotoWebp = ImageUploader::uploadAndConvertToWebp(
                $_FILES['foto_mascota'],
                'mascotas',
                (string) $currentPet['codigo_unico'],
                $fotoWebp
            );
        }

        $stmtUpd = $db->prepare("
            UPDATE mascotas
            SET nombre_mascota    = :nombre_mascota,
                especie           = :especie,
                raza              = :raza,
                sexo              = :sexo,
                edad_aprox        = :edad,
                peso_actual       = :peso,
                esterilizado      = :esterilizado,
                alergias_notas    = :alergias,
                foto_webp         = :foto,
                nombre_dueno      = :nombre_dueno,
                telefono_contacto = :telefono
            WHERE id = :id
        ");
        $stmtUpd->execute([
            ':nombre_mascota' => $nombreMascota,
            ':especie'        => $especie,
            ':raza'           => $raza,
            ':sexo'           => $sexo,
            ':edad'           => $edadAprox,
            ':peso'           => $pesoActual,
            ':esterilizado'   => $esterilizado,
            ':alergias'       => $alergias,
            ':foto'           => $fotoWebp,
            ':nombre_dueno'   => $nombreDueno,
            ':telefono'       => $telefono,
            ':id'             => $petId,
        ]);

        return ['type' => 'ok', 'text' => 'Ficha de la mascota actualizada correctamente.'];
    }

    private static function addClinicalHistory(PDO $db): array
    {
        $mascotaId    = (int) ($_POST['mascota_id'] ?? 0);
        $fecha        = trim((string) ($_POST['fecha_atencion'] ?? date('Y-m-d')));
        $tipo         = (string) ($_POST['tipo_atencion'] ?? 'Consulta General');
        $titulo       = trim((string) ($_POST['titulo'] ?? ''));
        $descripcion  = trim((string) ($_POST['descripcion'] ?? '')) ?: null;
        $pesoRaw      = trim((string) ($_POST['peso_consulta'] ?? ''));
        $pesoConsulta = ($pesoRaw !== '' && is_numeric($pesoRaw)) ? (float) $pesoRaw : null;
        $refuerzo     = trim((string) ($_POST['proximo_refuerzo'] ?? '')) ?: null;

        if ($mascotaId <= 0 || $titulo === '') {
            throw new RuntimeException('Debes indicar el motivo o nombre de la vacuna/consulta.');
        }

        $tiposValidos = ['Vacunación', 'Desparasitación', 'Consulta General', 'Cirugía / Otro'];
        if (!in_array($tipo, $tiposValidos, true)) {
            $tipo = 'Consulta General';
        }

        $stmt = $db->prepare("
            INSERT INTO historial_clinico (
                mascota_id, veterinario_id, fecha_atencion, tipo_atencion,
                titulo, descripcion, peso_consulta, proximo_refuerzo
            ) VALUES (
                :mascota_id, :vet_id, :fecha, :tipo,
                :titulo, :descripcion, :peso, :refuerzo
            )
        ");
        $stmt->execute([
            ':mascota_id'  => $mascotaId,
            ':vet_id'      => (int) ($_SESSION['user_id'] ?? 1),
            ':fecha'       => $fecha,
            ':tipo'        => $tipo,
            ':titulo'      => $titulo,
            ':descripcion' => $descripcion,
            ':peso'        => $pesoConsulta,
            ':refuerzo'    => $refuerzo,
        ]);

        // Si se registró peso en la consulta, actualizar también el peso actual de la mascota
        if ($pesoConsulta !== null) {
            $updPeso = $db->prepare("UPDATE mascotas SET peso_actual = :peso WHERE id = :id");
            $updPeso->execute([':peso' => $pesoConsulta, ':id' => $mascotaId]);
        }

        return ['type' => 'ok', 'text' => 'Registro clínico agregado al expediente de la mascota.'];
    }

    private static function saveProduct(PDO $db): array
    {
        $prodId         = !empty($_POST['producto_id']) ? (int) $_POST['producto_id'] : null;
        $sku            = strtoupper(trim((string) ($_POST['sku'] ?? '')));
        $nombre         = trim((string) ($_POST['nombre'] ?? ''));
        $categoria      = trim((string) ($_POST['categoria'] ?? 'General'));
        $descripcion    = trim((string) ($_POST['descripcion'] ?? '')) ?: null;
        $precio         = (float) ($_POST['precio_mxn'] ?? 0);
        $disponibilidad = (string) ($_POST['disponibilidad'] ?? 'Disponible');
        $activo         = isset($_POST['activo']) ? 1 : 0;

        if ($sku === '' || $nombre === '' || $precio <= 0) {
            throw new RuntimeException('El código SKU, el nombre del producto y un precio mayor a $0.00 son obligatorios.');
        }

        $estadosValidos = ['Disponible', 'Pocas unidades', 'Agotado'];
        if (!in_array($disponibilidad, $estadosValidos, true)) {
            $disponibilidad = 'Disponible';
        }

        if ($prodId === null) {
            $imagenWebp = null;
            if (!empty($_FILES['imagen_producto'])) {
                $imagenWebp = ImageUploader::uploadAndConvertToWebp(
                    $_FILES['imagen_producto'],
                    'productos',
                    $sku,
                    null
                );
            }

            $stmt = $db->prepare("
                INSERT INTO productos (sku, nombre, categoria, descripcion, precio_mxn, disponibilidad, imagen_webp, activo)
                VALUES (:sku, :nombre, :categoria, :descripcion, :precio, :disp, :img, :activo)
            ");
            $stmt->execute([
                ':sku'         => $sku,
                ':nombre'      => $nombre,
                ':categoria'   => $categoria,
                ':descripcion' => $descripcion,
                ':precio'      => $precio,
                ':disp'        => $disponibilidad,
                ':img'         => $imagenWebp,
                ':activo'      => $activo,
            ]);

            return ['type' => 'ok', 'text' => "Producto {$sku} agregado al catálogo vitrina."];
        }

        // Editar producto existente
        $stmtCur = $db->prepare("SELECT imagen_webp FROM productos WHERE id = :id LIMIT 1");
        $stmtCur->execute([':id' => $prodId]);
        $curProd = $stmtCur->fetch();

        if (!$curProd) {
            throw new RuntimeException('El producto seleccionado no existe.');
        }

        $imagenWebp = $curProd['imagen_webp'];
        if (!empty($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imagenWebp = ImageUploader::uploadAndConvertToWebp(
                $_FILES['imagen_producto'],
                'productos',
                $sku,
                $imagenWebp
            );
        }

        $stmtUpd = $db->prepare("
            UPDATE productos
            SET sku            = :sku,
                nombre         = :nombre,
                categoria      = :categoria,
                descripcion    = :descripcion,
                precio_mxn     = :precio,
                disponibilidad = :disp,
                imagen_webp    = :img,
                activo         = :activo
            WHERE id = :id
        ");
        $stmtUpd->execute([
            ':sku'         => $sku,
            ':nombre'      => $nombre,
            ':categoria'   => $categoria,
            ':descripcion' => $descripcion,
            ':precio'      => $precio,
            ':disp'        => $disponibilidad,
            ':img'         => $imagenWebp,
            ':activo'      => $activo,
            ':id'          => $prodId,
        ]);

        return ['type' => 'ok', 'text' => "Producto {$sku} actualizado correctamente."];
    }
}