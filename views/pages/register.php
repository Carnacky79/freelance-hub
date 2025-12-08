<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrati - FreelanceHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center py-12">
    <div class="bg-white p-8 rounded-xl shadow-lg max-w-md w-full mx-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold">
                <span class="text-gray-800">Freelance</span><span class="text-blue-600">Hub</span>
            </h1>
            <p class="text-gray-500 mt-2">Crea il tuo account gratuito</p>
        </div>
        
        <form id="registerForm" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome completo</label>
                <input type="text" name="name" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                       placeholder="Mario Rossi">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                       placeholder="la-tua@email.com">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                       placeholder="Minimo 8 caratteri">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Conferma Password</label>
                <input type="password" name="password_confirm" required minlength="8"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                       placeholder="Ripeti la password">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tariffa oraria (€) - opzionale</label>
                <input type="number" name="hourly_rate" step="0.01" min="0"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                       placeholder="50.00">
            </div>
            
            <div class="flex items-start">
                <input type="checkbox" name="terms" required class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-600">
                    Accetto i <a href="#" class="text-blue-600 hover:text-blue-700">Termini di servizio</a> 
                    e la <a href="#" class="text-blue-600 hover:text-blue-700">Privacy Policy</a>
                </span>
            </div>
            
            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Crea Account
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-600">
                Hai già un account? 
                <a href="login" class="text-blue-600 hover:text-blue-700 font-medium">Accedi</a>
            </p>
        </div>
        
        <div id="errorMsg" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm"></div>
        <div id="successMsg" class="hidden mt-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-600 text-sm"></div>
    </div>
    
    <script>
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            const errorDiv = document.getElementById('errorMsg');
            const successDiv = document.getElementById('successMsg');
            
            // Nascondi messaggi precedenti
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');
            
            // Verifica password
            if (data.password !== data.password_confirm) {
                errorDiv.textContent = 'Le password non coincidono';
                errorDiv.classList.remove('hidden');
                return;
            }
            
            try {
                const response = await fetch('api/v1/auth/register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    successDiv.textContent = 'Account creato! Reindirizzamento...';
                    successDiv.classList.remove('hidden');
                    setTimeout(() => {
                        window.location.href = './';
                    }, 1500);
                } else {
                    errorDiv.textContent = result.message || 'Errore durante la registrazione';
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = 'Errore di connessione';
                errorDiv.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
