<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        #chat {
            border: 1px solid #ccc;
            height: 300px;
            overflow-y: scroll;
            padding: 5px;
        }
    </style>
</head>
<body>
    <h3>Chat en tiempo real PHP Ratchet + MySQL</h3>
    <div id="chat"></div>
    <input type="text" id="mensaje" placeholder="Escribe mensaje" />
    <button type="button" onclick="enviar()">Enviar</button>

    <script>
        const ws = new WebSocket('ws://localhost:8080');
        const chat = document.getElementById('chat');

        ws.onopen = () => console.log("WebSocket conectado");
        ws.onerror = (e) => console.error("Error en WebSocket", e);
        ws.onclose = () => console.log("WebSocket cerrado");

        ws.onmessage = function(event) {
            const p = document.createElement('p');
            p.textContent = event.data;
            chat.appendChild(p);
            chat.scrollTop = chat.scrollHeight;
        };

        function enviar() {
            const mensaje = document.getElementById('mensaje').value;
            if(mensaje.trim() !== '') {
                ws.send(mensaje);
                document.getElementById('mensaje').value = '';
            }
        }
    </script>

</body>
</html>