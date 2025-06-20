<?php
session_start();

if (isset($_SESSION['id_cliente']) && isset($_SESSION['llave_secreta'])) {
    header("Location: /dashboard");
} else {
    header("Location: /login");
}
exit;
?>