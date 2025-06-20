<?php
// Simple test page to verify Tailwind CSS is working
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/assets/css/output.css" rel="stylesheet">
    <title>Tailwind CSS Test - PHP</title>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-blue-600 mb-8">Tailwind CSS is Working in PHP!</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Card 1</h2>
                <p class="text-gray-600">This card demonstrates that Tailwind CSS is properly connected and working in PHP.</p>
            </div>
            
            <div class="bg-blue-500 rounded-lg shadow-md p-6 text-white">
                <h2 class="text-xl font-semibold mb-4">Card 2</h2>
                <p>This card shows different styling with Tailwind CSS classes.</p>
            </div>
            
            <div class="bg-green-500 rounded-lg shadow-md p-6 text-white">
                <h2 class="text-xl font-semibold mb-4">Card 3</h2>
                <p>Another example of Tailwind CSS utility classes in action.</p>
            </div>
        </div>
        
        <div class="mt-8 text-center">
            <a href="/App/Views/components/Sidebar.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                View Sidebar Component
            </a>
        </div>
    </div>
</body>
</html>
