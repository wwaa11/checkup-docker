<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receive Numbers - All Users</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .connection-status {
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .connected {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .disconnected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .connecting {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .numbers-container {
            min-height: 300px;
            max-height: 400px;
            overflow-y: auto;
            border: 2px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background-color: #fafafa;
        }
        .number-item {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            animation: slideIn 0.3s ease-out;
        }
        .number-item:last-child {
            margin-bottom: 0;
        }
        .number-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .number-meta {
            font-size: 12px;
            color: #666;
            display: flex;
            justify-content: space-between;
        }
        .empty-state {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 50px 20px;
        }
        .links {
            text-align: center;
            margin-top: 30px;
        }
        .links a {
            color: #007bff;
            text-decoration: none;
            margin: 0 10px;
        }
        .links a:hover {
            text-decoration: underline;
        }
        .clear-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .clear-btn:hover {
            background-color: #c82333;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Receive Numbers - All Users</h1>
        
        <div id="connectionStatus" class="connection-status connecting">
            Connecting to WebSocket...
        </div>
        
        <button id="clearBtn" class="clear-btn" onclick="clearNumbers()">Clear All Numbers</button>
        
        <div id="numbersContainer" class="numbers-container">
            <div class="empty-state">
                Waiting for numbers from user1...
            </div>
        </div>
        
        <div class="links">
            <a href="{{ route('send') }}" target="_blank">Open Send Page</a> |
            <a href="/">Home</a>
        </div>
    </div>

    @vite(['resources/js/app.js'])
    
    <script>
        let receivedNumbers = [];
        let echoChannel = null;
        
        // Wait for Echo to be available
        function initializeEcho() {
            if (typeof window.Echo !== 'undefined') {
                try {
                    // Initialize Echo for WebSocket connection
                    echoChannel = window.Echo.channel('numbers')
                        .listen('.number.sent', (e) => {
                            console.log('Number received:', e);
                            addNumber(e.number, e.sender, e.timestamp);
                            updateConnectionStatus('connected', 'Connected to WebSocket');
                        });
                    
                    // Connection status handling - check if connector and socket exist
                    if (window.Echo.connector && window.Echo.connector.socket) {
                        window.Echo.connector.socket.on('connect', () => {
                            updateConnectionStatus('connected', 'Connected to WebSocket');
                        });
                        
                        window.Echo.connector.socket.on('disconnect', () => {
                            updateConnectionStatus('disconnected', 'Disconnected from WebSocket');
                        });
                        
                        window.Echo.connector.socket.on('reconnect', () => {
                            updateConnectionStatus('connected', 'Reconnected to WebSocket');
                        });
                        
                        window.Echo.connector.socket.on('error', (error) => {
                            console.error('WebSocket error:', error);
                            updateConnectionStatus('disconnected', 'WebSocket connection error');
                        });
                    }
                    
                    updateConnectionStatus('connecting', 'Connecting to WebSocket...');
                    
                    // Set a timeout to check connection status
                    setTimeout(() => {
                        if (echoChannel) {
                            updateConnectionStatus('connected', 'WebSocket ready');
                        }
                    }, 1000);
                    
                } catch (error) {
                    console.error('Error initializing Echo:', error);
                    updateConnectionStatus('disconnected', 'Failed to initialize WebSocket');
                }
            } else {
                // Retry after a short delay if Echo is not available yet
                setTimeout(initializeEcho, 100);
            }
        }
        
        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            updateConnectionStatus('connecting', 'Initializing WebSocket connection...');
            initializeEcho();
        });
        
        function updateConnectionStatus(status, message) {
            const statusElement = document.getElementById('connectionStatus');
            statusElement.className = `connection-status ${status}`;
            statusElement.textContent = message;
        }
        
        function addNumber(number, sender, timestamp) {
            const numbersContainer = document.getElementById('numbersContainer');
            
            // Remove empty state if it exists
            const emptyState = numbersContainer.querySelector('.empty-state');
            if (emptyState) {
                emptyState.remove();
            }
            
            // Create new number item
            const numberItem = document.createElement('div');
            numberItem.className = 'number-item';
            
            const formattedTime = new Date(timestamp).toLocaleTimeString();
            
            numberItem.innerHTML = `
                <div class="number-value">${number}</div>
                <div class="number-meta">
                    <span>From: ${sender}</span>
                    <span>Time: ${formattedTime}</span>
                </div>
            `;
            
            // Add to the beginning of the container
            numbersContainer.insertBefore(numberItem, numbersContainer.firstChild);
            
            // Store in array
            receivedNumbers.unshift({
                number: number,
                sender: sender,
                timestamp: timestamp
            });
            
            // Limit to last 50 numbers
            if (receivedNumbers.length > 50) {
                receivedNumbers = receivedNumbers.slice(0, 50);
                const items = numbersContainer.querySelectorAll('.number-item');
                if (items.length > 50) {
                    items[items.length - 1].remove();
                }
            }
        }
        
        function clearNumbers() {
            const numbersContainer = document.getElementById('numbersContainer');
            numbersContainer.innerHTML = '<div class="empty-state">Waiting for numbers from user1...</div>';
            receivedNumbers = [];
        }
        
        // Show initial connection attempt
        setTimeout(() => {
            if (document.getElementById('connectionStatus').classList.contains('connecting')) {
                updateConnectionStatus('connected', 'Connected to WebSocket');
            }
        }, 2000);
    </script>
</body>
</html>