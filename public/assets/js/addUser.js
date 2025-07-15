// Add User Page JavaScript

document.getElementById('addUserForm').onsubmit = async function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('ajax', 'true');
    const response = await fetch('', {
        method: 'POST',
        body: formData
    });
    const text = await response.text();
    // Try to parse new table body from response
    const parser = new DOMParser();
    const doc = parser.parseFromString(text, 'text/html');
    const newTbody = doc.getElementById('usersTableBody');
    if (newTbody) {
        document.getElementById('usersTableBody').innerHTML = newTbody.innerHTML;
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'User has been added successfully.',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK',
            timer: 2000
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to add user. Please check your input.',
            confirmButtonColor: '#d33',
            confirmButtonText: 'OK'
        });
    }
    // Close modal and reset form
    document.querySelectorAll('#formModal .close').forEach(btn => btn.click());
    form.reset();
}; 