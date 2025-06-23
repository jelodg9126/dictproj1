<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <link rel="stylesheet" href="/dictproj1/src/input.css">
    <title>Document</title>
</head>
<body>
     <div class="app-container">
 
<?php include __DIR__ . '/../components/Sidebar.php'; ?>

    <div class="flex-1 p-6 bg-gray-50 min-h-screen" id="docu">
            <div class="max-w-7xl mx-auto">

                <div class="flex items-center justify-between">
                  <div class="items-center p-6">
                    <h1 class="text-3xl font-bold text-blue-800">Documents</h1>
                    <p class="text-gray-600 mt-2">Manage and track all incoming documents</p>
                   </div>
                    <button class="border border-gray-400 flex gap-1 p-3 cursor-pointer rounded-2xl bg-white" onClick={toggleModal}>
              <IconFileSpreadsheet stroke={1.75} class="text-gray-600" />
              Sent Form
              </button>
                </div>

              
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="flex items-center gap-4 flex-1">
                            <div class="relative flex-1 max-w-md">
                                 <IconFilter class="text-gray-400" />
                                <input
                                    type="text"
                                    placeholder="Search documents, sender, or recipient..."
                                    class="pl-3 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    value={searchTerm}
                              
                                />
                            </div>
                            <div class="flex items-center gap-2">
                                <IconFilter class="text-gray-400" />
                                <select
                                    class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    value="">
                                    <option value="All">All Status</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Under Review">Under Review</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Processed">Processed</option>
                                    <option value="Published">Published</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">
                                {filteredDocuments.length} of {documents.length} documents
                            </span>
                        </div>
                    </div>
                </div>

    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Office
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Sender Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Delivery Mode
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Courier Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date & Time
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                          
                                    <tr key={doc.id} class="hover:bg-gray-50 transition-colors">
                                        <td class ="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {doc.office}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {doc.senderName}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {doc.email}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {doc.deliveryMode}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {doc.courierName}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {new Date(doc.dateTime).toLocaleString()}
                                        </td>
                                    </tr>
                            
                            </tbody>
                        </table>
                    </div>
                    
                  
                        <div class="text-center py-12">
                            <div class="text-gray-500 text-lg">No documents found</div>
                            <div class="text-gray-400 text-sm mt-2">Try adjusting your search or filter criteria</div>
                        </div>
                
                </div>
            </div>
        </div>
                    </div>

       
</body>
</html>