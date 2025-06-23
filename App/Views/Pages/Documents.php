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
          <button class="border border-gray-400 flex gap-1 p-3 cursor-pointer rounded-2xl bg-white" id="form-btn">
              Sent Form
          </button>
                </div>

              
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="flex items-center gap-4 flex-1">
                            <div class="relative flex-1 max-w-md">
                       
                                <input
                                    type="text"
                                    placeholder="Search documents, sender, or recipient..."
                                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              
                                />
                            </div>
                            <div class="flex items-center gap-2">
                            
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
                              1 of 1 documents
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
                                                doc.office
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            doc.senderName
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            doc.email
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            doc.deliveryMode
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            doc.courierName
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            new Datedoc.dateTime.toLocaleString
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

    <!-- Modal -->
    <div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <form action="" class="bg-white flex flex-col border border-gray-300 w-full max-w-lg p-8 gap-6 items-center rounded-xl shadow-lg">
            <div class="flex justify-between items-center w-full mb-4">
                <h1 class="text-2xl font-bold">Sender Form</h1>
                <button 
                    type="button"
                    id="close-btn"
                    class="text-gray-500 hover:text-gray-700 text-2xl px-2"
                >
                    ✕
                </button>
            </div>

            <div class="w-full space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input type="text" class="border w-full p-2 rounded-lg" placeholder="Enter first name" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" class="border w-full p-2 rounded-lg" placeholder="Enter last name" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Type</label>
                    <input type="text" class="border w-full p-2 rounded-lg" placeholder="Enter file type" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Mode</label>
                    <select name="delivery-mode" class="border w-full p-2 rounded-lg">
                        <option value="" disabled>--Select--</option>
                        <option value="Courier">Courier</option>
                        <option value="In-Person">In-Person</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white mt-6 py-2 rounded-lg">Submit</button>
        </form>
    </div>

    <script src="/dictproj1/modal.js"></script>
    
</body>
</html>