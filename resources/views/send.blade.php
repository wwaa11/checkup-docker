<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Send Numbers - User1</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="number"]:focus {
            border-color: #007bff;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .status {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            display: none;
        }
        .status.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Send Numbers - User1</h1>
        
        <form id="numberForm">
            <div class="form-group">
                <label for="number">Enter a number to broadcast:</label>
                <input type="number" id="number" name="number" required step="any" placeholder="Enter any number...">
            </div>
            
            <button type="submit" id="sendBtn">Send Number</button>
        </form>
        
        <div id="status" class="status"></div>
        
        <div class="links">
            <a href="{{ route('receive') }}" target="_blank">Open Receive Page</a> |
            <a href="/">Home</a>
        </div>
    </div>

    <script>
        // Set up CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        document.getElementById('numberForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const numberInput = document.getElementById('number');
            const sendBtn = document.getElementById('sendBtn');
            const status = document.getElementById('status');
            
            const number = numberInput.value;
            
            if (!number) {
                showStatus('Please enter a number', 'error');
                return;
            }
            
            // Disable button and show loading state
            sendBtn.disabled = true;
            sendBtn.textContent = 'Sending...';
            
            try {
                const response = await fetch('/send-number', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ number: parseFloat(number) })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showStatus(`Number ${data.number} sent successfully!`, 'success');
                    numberInput.value = '';
                } else {
                    showStatus('Failed to send number', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showStatus('Error sending number', 'error');
            } finally {
                // Re-enable button
                sendBtn.disabled = false;
                sendBtn.textContent = 'Send Number';
            }
        });
        
        function showStatus(message, type) {
            const status = document.getElementById('status');
            status.textContent = message;
            status.className = `status ${type}`;
            status.style.display = 'block';
            
            // Hide status after 3 seconds
            setTimeout(() => {
                status.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>