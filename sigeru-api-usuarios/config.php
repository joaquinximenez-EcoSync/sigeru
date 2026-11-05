<?php
// Configuracion general de la API de Usuarios

// En esta entrega los datos se guardan en la sesion (todavia no hay base de datos)
session_name("SIGERU_USUARIOS");
session_start();

// La API siempre responde en formato JSON
header("Content-Type: application/json; charset=utf-8");
