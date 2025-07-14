<?php
namespace Chat;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $db;

    public function __construct() {
        $this->clients = new \SplObjectStorage;

        // Cambia los datos de conexión a tu servidor MySQL
        $this->db = new \mysqli('localhost', 'root', 'root', 'chatdb');
        if ($this->db->connect_error) {
            die("Error conexión BD: " . $this->db->connect_error);
        }
        // Opcional: para manejar caracteres UTF-8
        $this->db->set_charset("utf8mb4");
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        // Recuperar últimos 20 mensajes para enviar al cliente nuevo
        $result = $this->db->query("SELECT mensaje FROM mensajes ORDER BY id DESC LIMIT 20");
        $mensajes = [];
        while ($row = $result->fetch_assoc()) {
            $mensajes[] = $row['mensaje'];
        }
        $result->free();

        // Enviar mensajes en orden correcto (del más antiguo al más nuevo)
        foreach (array_reverse($mensajes) as $mensaje) {
            $conn->send($mensaje);
        }

        //$conn->send("Bienvenido al chat!");
        echo "Nueva conexión: {$conn->resourceId}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Guardar mensaje en la BD
        $stmt = $this->db->prepare("INSERT INTO mensajes (mensaje) VALUES (?)");
        $stmt->bind_param('s', $msg);
        $stmt->execute();
        $stmt->close();

        // Reenviar mensaje a todos los clientes (incluido el emisor)
        foreach ($this->clients as $client) {
            $client->send($msg);
        }

        echo "Mensaje recibido: $msg\n";
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Conexión cerrada: {$conn->resourceId}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}