<?php
// Configuracion general de la API de Gestion

// En esta entrega los datos se guardan en la sesion (todavia no hay base de datos)
session_name("SIGERU_GESTION");
session_start();

// La API siempre responde en formato JSON
header("Content-Type: application/json; charset=utf-8");
