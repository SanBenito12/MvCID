<?php
define("SUPABASE_URL", "https://gvpkeksbujfdszckpuki.supabase.co"); 
define("SUPABASE_KEY", "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imd2cGtla3NidWpmZHN6Y2twdWtpIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDk1MTA0NTQsImV4cCI6MjA2NTA4NjQ1NH0.hBX14NQ1aoPCX2dZWINUTGjFrgNK_6eti0LYS4CJL5c"); 
define('SUPABASE_SERVICE_ROLE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imd2cGtla3NidWpmZHN6Y2twdWtpIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc0OTUxMDQ1NCwiZXhwIjoyMDY1MDg2NDU0fQ.rNXqhDiKveKgUdFnStIVer7QkpGNsSPwM_f9FheQKhQ'); 

function supabaseRequest($endpoint, $method = "GET", $data = null) {
    $curl = curl_init();

    $headers = [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY,
        "Content-Type: application/json",
        "Prefer: return=representation"
    ];

    $url = SUPABASE_URL . "/rest/v1/" . $endpoint;

    // Log para debug
    error_log("Supabase Request - URL: $url");
    error_log("Supabase Request - Method: $method");
    if ($data) {
        error_log("Supabase Request - Data: " . json_encode($data));
    }

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_FOLLOWLOCATION => true
    ];

    if ($data !== null) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }

    curl_setopt_array($curl, $options);

    $response = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    
    // Log de respuesta
    error_log("Supabase Response - Status: $httpcode");
    error_log("Supabase Response - Body: " . substr($response, 0, 500));
    
    if ($error) {
        error_log("Supabase cURL Error: $error");
    }
    
    curl_close($curl);

    // Manejo de errores de cURL
    if ($error) {
        throw new Exception("Error de conexión: $error");
    }

    // Decodificar respuesta
    $decodedResponse = json_decode($response, true);
    
    // Si no se puede decodificar JSON, devolver respuesta cruda
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error: " . json_last_error_msg());
        $decodedResponse = null;
    }

    return [
        "status" => $httpcode,
        "body" => $decodedResponse,
        "raw" => $response
    ];
}
?>