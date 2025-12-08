<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FreelanceHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-lg max-w-md w-full mx-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold">
                <span class="text-gray-800">Freelance</span><span class="text-blue-600">Hub</span>
            </h1>
            <p class="text-gray-500 mt-2">Accedi al tuo account</p>
        </div>
        
        <form id="loginForm" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                       placeholder="la-tua@email.com">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                       placeholder="••••••••">
            </div>
            
            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-600">Ricordami</span>
                </label>
                <a href="#" class="text-sm text-blue-600 hover:text-blue-700">Password dimenticata?</a>
            </div>
            
            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Accedi
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-600">
                Non hai un account? 
                <a href="register" class="text-blue-600 hover:text-blue-700 font-medium">Registrati</a>
            </p>
        </div>
        
        <div id="errorMsg" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm"></div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            const errorDiv = document.getElementById('errorMsg');
            
            try {
                const response = await fetch('api/v1/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = './';
                } else {
                    errorDiv.textContent = result.message || 'Credenziali non valide';
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
