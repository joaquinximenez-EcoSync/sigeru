<?php
// Configuracion general de la API de Recoleccion

// La sesion guarda quien inicio sesion (la comparten todas las APIs)
session_name("SIGERU");
session_start();

// La API siempre responde en formato JSON
header("Content-Type: application/json; charset=utf-8");
