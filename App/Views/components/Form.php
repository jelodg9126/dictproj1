

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form onSubmit={handleSubmit} className="bg-white flex flex-col border border-gray-300 w-full max-w-lg p-8 gap-6 items-center rounded-xl shadow-lg">
            <div className="flex justify-between items-center w-full mb-4">
                <h1 className="text-2xl font-bold">Sender Form</h1>
                <button 
                    type="button"
                    onClick={handleClose}
                    className="text-gray-500 hover:text-gray-700 text-2xl px-2"
                >
                    ✕
                </button>
            </div>

            <div className="">
                <Input label="First Name" type="text" />
                <Input label="Last Name" type="text" />
                <Input label="File Type" type="text" />
                <Dropdown label="Mode of Delivery" disOption="Select Delivery" options={["Courier", "Online"]} />
                <Input label ="Label" type="text" />
            </div>

            <button type="submit" className="btn btn-wide bg-blue-500 text-white mt-6">Submit</button>
        </form>
</body>
</html>